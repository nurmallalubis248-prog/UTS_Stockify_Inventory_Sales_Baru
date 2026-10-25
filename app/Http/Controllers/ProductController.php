<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $products = Product::with('category')
            ->search($request->q)
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function lowStock()
    {
        $products = Product::lowStock()
            ->with('category')
            ->orderBy('stock')
            ->paginate(10);

        return view('products.low-stock', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load('category');

        return view('products.show', [
            'product' => $product,
            'sold' => $product->saleItems()->whereHas('sale', fn ($q) => $q->completed())->sum('quantity'),
            'bought' => $product->purchaseItems()->whereHas('purchase', fn ($q) => $q->where('status', 'confirmed'))->sum('quantity'),
        ]);
    }

    public function create()
    {
        return view('products.create', ['categories' => Category::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules() + [
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ], [], $this->attributes());

        Product::create($data);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        // Stok tidak diubah dari form ini; stok hanya berubah lewat transaksi.
        $product->update($request->validate($this->rules($product), [], $this->attributes()));

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->isUsedInTransactions()) {
            return back()->withErrors(['product' => 'Produk sudah dipakai dalam transaksi, tidak dapat dihapus.']);
        }

        $product->delete(); // soft delete

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    private function rules(?Product $product = null): array
    {
        return [
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product)],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    private function attributes(): array
    {
        return [
            'sku' => 'SKU',
            'name' => 'nama produk',
            'category_id' => 'kategori',
            'purchase_price' => 'harga beli',
            'selling_price' => 'harga jual',
            'minimum_stock' => 'stok minimum',
            'stock' => 'stok awal',
        ];
    }
}
