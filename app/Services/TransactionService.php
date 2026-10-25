<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh logika bisnis transaksi ada di sini:
 * - total dan harga selalu dihitung server dari database
 * - header, item, dan perubahan stok disimpan dalam satu DB::transaction
 * - baris produk dikunci (lockForUpdate) agar tidak terjadi overselling
 */
class TransactionService
{
    public function createSale(User $user, array $data): Sale
    {
        return DB::transaction(function () use ($user, $data) {
            $lines = $this->mergeQuantities($data['items']);
            $products = $this->lockProducts(array_keys($lines));

            $errors = [];
            foreach ($lines as $productId => $quantity) {
                $product = $products->get($productId);

                if (! $product) {
                    $errors[] = 'Produk tidak ditemukan atau sudah dihapus.';
                } elseif ($quantity < 1 || $product->stock < $quantity) {
                    $errors[] = "Stok {$product->name} tidak cukup (tersedia {$product->stock}, diminta {$quantity}).";
                }
            }

            if ($errors) {
                throw ValidationException::withMessages(['items' => $errors]);
            }

            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->generateNumber('SL', Sale::class, 'sale_number'),
                'customer_name' => $data['customer_name'] ?? null,
                'total' => 0,
                'status' => Sale::STATUS_COMPLETED,
                'sold_at' => now(),
            ]);

            $total = 0;
            foreach ($lines as $productId => $quantity) {
                $product = $products->get($productId);
                $subtotal = round((float) $product->selling_price * $quantity, 2);

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $quantity);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    public function createPurchase(User $user, array $data): Purchase
    {
        return DB::transaction(function () use ($user, $data) {
            $items = $data['items'];
            $products = $this->lockProducts(array_unique(array_map(
                fn ($item) => (int) $item['product_id'],
                $items
            )));

            $purchase = Purchase::create([
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'purchase_number' => $this->generateNumber('PO', Purchase::class, 'purchase_number'),
                'total' => 0,
                'status' => Purchase::STATUS_CONFIRMED,
                'purchased_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            $total = 0;
            foreach ($items as $item) {
                $product = $products->get((int) $item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Produk tidak ditemukan atau sudah dihapus.',
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = round($unitCost * $quantity, 2);

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $quantity);
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);

            return $purchase;
        });
    }

    /** Membatalkan penjualan: stok dikembalikan secara konsisten. */
    public function cancelSale(Sale $sale): Sale
    {
        return DB::transaction(function () use ($sale) {
            $sale = Sale::whereKey($sale->id)->lockForUpdate()->firstOrFail();

            if ($sale->status !== Sale::STATUS_COMPLETED) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya penjualan berstatus selesai yang dapat dibatalkan.',
                ]);
            }

            $items = $sale->items()->get();
            $products = $this->lockProducts($items->pluck('product_id')->all(), true);

            foreach ($items as $item) {
                $products->get($item->product_id)?->increment('stock', $item->quantity);
            }

            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $sale;
        });
    }

    /** Membatalkan pembelian: ditolak jika stok saat ini tidak cukup untuk ditarik kembali. */
    public function cancelPurchase(Purchase $purchase): Purchase
    {
        return DB::transaction(function () use ($purchase) {
            $purchase = Purchase::whereKey($purchase->id)->lockForUpdate()->firstOrFail();

            if ($purchase->status !== Purchase::STATUS_CONFIRMED) {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pembelian berstatus terkonfirmasi yang dapat dibatalkan.',
                ]);
            }

            $items = $purchase->items()->get();
            $products = $this->lockProducts($items->pluck('product_id')->all(), true);

            $errors = [];
            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                if ($product && $product->stock < $item->quantity) {
                    $errors[] = "Pembelian tidak dapat dibatalkan: stok {$product->name} ({$product->stock}) sudah lebih kecil dari {$item->quantity}.";
                }
            }

            if ($errors) {
                throw ValidationException::withMessages(['status' => $errors]);
            }

            foreach ($items as $item) {
                $products->get($item->product_id)?->decrement('stock', $item->quantity);
            }

            $purchase->update([
                'status' => Purchase::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            return $purchase;
        });
    }

    /** Menggabungkan baris yang memakai produk sama: [product_id => total quantity]. */
    private function mergeQuantities(array $items): array
    {
        $lines = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $lines[$id] = ($lines[$id] ?? 0) + (int) $item['quantity'];
        }
        ksort($lines);

        return $lines;
    }

    private function lockProducts(array $ids, bool $withTrashed = false): Collection
    {
        $query = Product::query();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    private function generateNumber(string $prefix, string $model, string $column): string
    {
        do {
            $number = $prefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while ($model::where($column, $number)->exists());

        return $number;
    }
}
