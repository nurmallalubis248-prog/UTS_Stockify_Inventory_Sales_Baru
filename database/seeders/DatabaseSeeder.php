<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(TransactionService $service): void
    {
        // Akun demo (bukan kredensial asli).
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Stockify', 'password' => 'password', 'role' => User::ROLE_ADMIN]
        );
        $staff = User::updateOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Gudang', 'password' => 'password', 'role' => User::ROLE_STAFF]
        );

        User::updateOrCreate(
            ['email' => 'user@example.com'],
            ['name' => 'User Demo', 'password' => 'password', 'role' => User::ROLE_USER]
        );

        $categories = collect([
            'Bahan Pokok' => 'Beras, gula, minyak, dan kebutuhan dapur utama',
            'Minuman' => 'Air mineral, teh, dan kopi',
            'Bumbu & Mi Instan' => 'Bumbu dapur dan mi instan',
            'Kebersihan Rumah Tangga' => 'Sabun, deterjen, dan tisu',
        ])->map(fn ($description, $name) => Category::firstOrCreate(['name' => $name], ['description' => $description]));

        $suppliers = collect([
            ['name' => 'PT Sumber Pangan Nusantara', 'phone' => '0812-3456-0001', 'email' => 'sales@sumberpangan.example', 'address' => 'Jl. Industri No. 12, Medan'],
            ['name' => 'CV Tirta Segar Abadi', 'phone' => '0812-3456-0002', 'email' => 'order@tirtasegar.example', 'address' => 'Jl. Sudirman No. 45, Lhokseumawe'],
            ['name' => 'UD Berkah Makmur', 'phone' => '0812-3456-0003', 'email' => 'berkah@makmur.example', 'address' => 'Jl. Pasar Baru No. 8, Banda Aceh'],
            ['name' => 'PT Bersih Sejahtera', 'phone' => '0812-3456-0004', 'email' => 'cs@bersihsejahtera.example', 'address' => 'Jl. Gatot Subroto No. 21, Medan'],
        ])->map(fn ($data) => Supplier::firstOrCreate(['name' => $data['name']], $data));

        // [sku, nama, kategori, harga beli, harga jual, stok awal, stok minimum]
        $products = [
            ['BRS-001', 'Beras Premium 5 kg', 'Bahan Pokok', 62000, 69000, 40, 10],
            ['GLA-001', 'Gula Pasir 1 kg', 'Bahan Pokok', 14500, 16500, 60, 15],
            ['MYK-001', 'Minyak Goreng 2 L', 'Bahan Pokok', 33000, 37000, 35, 10],
            ['TPG-001', 'Tepung Terigu 1 kg', 'Bahan Pokok', 10500, 12500, 8, 10],
            ['MNM-001', 'Air Mineral 600 ml (dus)', 'Minuman', 38000, 44000, 50, 12],
            ['TEH-001', 'Teh Celup isi 25', 'Minuman', 6500, 8000, 70, 20],
            ['KPI-001', 'Kopi Bubuk 200 g', 'Minuman', 18000, 22000, 4, 8],
            ['MIE-001', 'Mi Instan Goreng', 'Bumbu & Mi Instan', 2800, 3500, 200, 60],
            ['KCP-001', 'Kecap Manis 275 ml', 'Bumbu & Mi Instan', 11500, 14000, 45, 12],
            ['GRM-001', 'Garam Halus 250 g', 'Bumbu & Mi Instan', 2500, 3500, 55, 15],
            ['SBN-001', 'Sabun Cuci Piring 800 ml', 'Kebersihan Rumah Tangga', 14000, 17500, 30, 10],
            ['DTR-001', 'Deterjen Bubuk 1 kg', 'Kebersihan Rumah Tangga', 21000, 25500, 25, 8],
            ['TSU-001', 'Tisu Gulung isi 4', 'Kebersihan Rumah Tangga', 12000, 15000, 3, 10],
        ];

        foreach ($products as [$sku, $name, $category, $buy, $sell, $stock, $min]) {
            Product::firstOrCreate(['sku' => $sku], [
                'name' => $name,
                'category_id' => $categories[$category]->id,
                'purchase_price' => $buy,
                'selling_price' => $sell,
                'stock' => $stock,
                'minimum_stock' => $min,
            ]);
        }

        // Contoh transaksi lewat service yang sama dengan aplikasi,
        // agar dashboard langsung terisi dan stok konsisten.
        if (Purchase::exists()) {
            return;
        }

        $id = fn (string $sku) => Product::where('sku', $sku)->value('id');

        $service->createPurchase($staff, [
            'supplier_id' => $suppliers[0]->id,
            'notes' => 'Restock bahan pokok mingguan',
            'items' => [
                ['product_id' => $id('BRS-001'), 'quantity' => 20, 'unit_cost' => 62000],
                ['product_id' => $id('GLA-001'), 'quantity' => 30, 'unit_cost' => 14500],
            ],
        ]);

        $service->createPurchase($admin, [
            'supplier_id' => $suppliers[1]->id,
            'items' => [
                ['product_id' => $id('MNM-001'), 'quantity' => 24, 'unit_cost' => 38000],
            ],
        ]);

        $service->createSale($staff, [
            'customer_name' => 'Toko Maju Jaya',
            'items' => [
                ['product_id' => $id('BRS-001'), 'quantity' => 10],
                ['product_id' => $id('GLA-001'), 'quantity' => 12],
                ['product_id' => $id('MIE-001'), 'quantity' => 40],
            ],
        ]);

        $service->createSale($staff, [
            'customer_name' => 'Warung Bu Siti',
            'items' => [
                ['product_id' => $id('MYK-001'), 'quantity' => 6],
                ['product_id' => $id('TEH-001'), 'quantity' => 10],
            ],
        ]);

        $service->createSale($admin, [
            'items' => [
                ['product_id' => $id('MNM-001'), 'quantity' => 8],
                ['product_id' => $id('SBN-001'), 'quantity' => 5],
            ],
        ]);
    }
}
