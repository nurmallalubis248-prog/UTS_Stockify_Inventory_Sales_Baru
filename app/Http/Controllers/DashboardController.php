<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stats = [
            'products' => Product::count(),
            'low_stock' => Product::lowStock()->count(),
            'sales_count' => Sale::visibleTo($user)->count(),
            'purchases_count' => Purchase::visibleTo($user)->count(),
        ];

        if ($user->isAdmin()) {
            $stats['sales_today'] = Sale::completed()->whereDate('sold_at', today())->sum('total');
            $stats['sales_month'] = Sale::completed()->where('sold_at', '>=', now()->startOfMonth())->sum('total');
        }

        $canTransact = $user->canTransact();

        return view('dashboard', [
            'stats' => $stats,
            'lowStock' => Product::lowStock()->with('category')->orderBy('stock')->limit(5)->get(),
            'recentSales' => $canTransact ? Sale::visibleTo($user)->with('user')->latest('sold_at')->limit(5)->get() : collect(),
            'recentPurchases' => $canTransact ? Purchase::visibleTo($user)->with('supplier')->latest('purchased_at')->limit(5)->get() : collect(),
        ]);
    }
}
