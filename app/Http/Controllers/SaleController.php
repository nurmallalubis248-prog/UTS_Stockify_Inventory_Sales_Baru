<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $sales = Sale::with('user')
            ->visibleTo($request->user())
            ->filter($filters)
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        return view('sales.create', [
            'products' => Product::where('stock', '>', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(SaleRequest $request, TransactionService $service): RedirectResponse
    {
        $sale = $service->createSale($request->user(), $request->validated());

        return redirect()->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->sale_number} berhasil disimpan dan stok berkurang.");
    }

    public function show(Sale $sale)
    {
        Gate::authorize('view-transaction', $sale);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale, TransactionService $service): RedirectResponse
    {
        $service->cancelSale($sale);

        return redirect()->route('sales.show', $sale)
            ->with('success', 'Penjualan dibatalkan dan stok sudah dikembalikan.');
    }
}
