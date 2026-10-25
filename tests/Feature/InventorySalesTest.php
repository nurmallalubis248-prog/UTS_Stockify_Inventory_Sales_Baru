<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventorySalesTest extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role = 'staff'): User
    {
        $this->counter++;

        return User::create([
            'name' => ucfirst($role).' '.$this->counter,
            'email' => "{$role}{$this->counter}@test.local",
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function makeProduct(int $stock = 10, array $overrides = []): Product
    {
        $this->counter++;
        $category = Category::firstOrCreate(['name' => 'Umum']);

        return Product::create(array_merge([
            'category_id' => $category->id,
            'sku' => 'SKU-'.$this->counter,
            'name' => 'Produk '.$this->counter,
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => $stock,
            'minimum_stock' => 2,
        ], $overrides));
    }

    private function makeSupplier(): Supplier
    {
        return Supplier::create(['name' => 'Pemasok Uji']);
    }

    // ---------- Auth ----------

    public function test_guest_cannot_open_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_wrong_password_is_rejected(): void
    {
        $user = $this->makeUser();

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'salah-total'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_valid_login_reaches_dashboard(): void
    {
        $user = $this->makeUser();

        $this->post(route('login.attempt'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    // ---------- Product ----------

    public function test_duplicate_sku_is_rejected(): void
    {
        $admin = $this->makeUser('admin');
        $existing = $this->makeProduct();

        $this->actingAs($admin)->post(route('products.store'), [
            'sku' => $existing->sku,
            'name' => 'Duplikat',
            'category_id' => $existing->category_id,
            'purchase_price' => 1000,
            'selling_price' => 1500,
            'stock' => 1,
            'minimum_stock' => 1,
        ])->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count());
    }

    public function test_negative_price_is_rejected(): void
    {
        $admin = $this->makeUser('admin');
        $category = Category::create(['name' => 'Umum']);

        $this->actingAs($admin)->post(route('products.store'), [
            'sku' => 'NEG-1',
            'name' => 'Harga negatif',
            'category_id' => $category->id,
            'purchase_price' => -1,
            'selling_price' => -5,
            'stock' => 0,
            'minimum_stock' => 0,
        ])->assertSessionHasErrors(['purchase_price', 'selling_price']);
    }

    public function test_product_used_in_transaction_cannot_be_deleted(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(10);

        $this->actingAs($admin)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $this->actingAs($admin)->delete(route('products.destroy', $product))
            ->assertSessionHasErrors('product');

        $this->assertNotNull(Product::find($product->id));
    }

    // ---------- Purchase & Sale ----------

    public function test_purchase_increases_stock(): void
    {
        $staff = $this->makeUser();
        $product = $this->makeProduct(10);
        $supplier = $this->makeSupplier();

        $this->actingAs($staff)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'items' => [['product_id' => $product->id, 'quantity' => 5, 'unit_cost' => 900]],
        ])->assertRedirect();

        $this->assertSame(15, $product->fresh()->stock);

        $purchase = Purchase::first();
        $this->assertEquals(4500, $purchase->total);
        $this->assertSame($staff->id, $purchase->user_id);
    }

    public function test_sale_decreases_stock_and_server_calculates_total(): void
    {
        $staff = $this->makeUser();
        $product = $this->makeProduct(10); // harga jual 1500

        $this->actingAs($staff)->post(route('sales.store'), [
            'total' => 1, // input palsu dari browser harus diabaikan
            'items' => [['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1]],
        ])->assertRedirect();

        $this->assertSame(7, $product->fresh()->stock);

        $sale = Sale::first();
        $this->assertEquals(4500, $sale->total);
        $this->assertEquals(1500, $sale->items->first()->unit_price);
    }

    public function test_sale_with_insufficient_stock_is_rejected_without_changes(): void
    {
        $staff = $this->makeUser();
        $product = $this->makeProduct(4);

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 6]],
        ])->assertSessionHasErrors('items');

        $this->assertSame(4, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_failed_item_rolls_back_the_whole_sale(): void
    {
        $staff = $this->makeUser();
        $enough = $this->makeProduct(10);
        $short = $this->makeProduct(1);

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $enough->id, 'quantity' => 2],
                ['product_id' => $short->id, 'quantity' => 5],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(10, $enough->fresh()->stock);
        $this->assertSame(1, $short->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_duplicate_product_rows_are_merged_before_stock_check(): void
    {
        $staff = $this->makeUser();
        $product = $this->makeProduct(5);

        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ])->assertSessionHasErrors('items');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_cancelling_a_sale_restores_stock_once(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(10);

        $this->actingAs($admin)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 4]],
        ]);
        $sale = Sale::first();
        $this->assertSame(6, $product->fresh()->stock);

        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertRedirect();
        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame('cancelled', $sale->fresh()->status);

        // Pembatalan kedua ditolak dan tidak menambah stok lagi.
        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertSessionHasErrors('status');
        $this->assertSame(10, $product->fresh()->stock);
    }

    // ---------- Authorization ----------

    public function test_staff_cannot_access_admin_functions(): void
    {
        $staff = $this->makeUser();
        $product = $this->makeProduct(10);

        $this->actingAs($staff);

        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('suppliers.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('products.create'))->assertForbidden();
        $this->delete(route('products.destroy', $product))->assertForbidden();

        $this->get(route('products.index'))->assertOk();
        $this->get(route('sales.create'))->assertOk();
    }

    public function test_staff_cannot_cancel_transactions_or_view_others(): void
    {
        $owner = $this->makeUser();
        $other = $this->makeUser();
        $product = $this->makeProduct(10);

        $this->actingAs($owner)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);
        $sale = Sale::first();

        $this->actingAs($owner)->post(route('sales.cancel', $sale))->assertForbidden();
        $this->actingAs($other)->get(route('sales.show', $sale))->assertForbidden();
        $this->actingAs($owner)->get(route('sales.show', $sale))->assertOk();
    }

    public function test_user_role_can_only_view_products_and_dashboard(): void
    {
        $user = $this->makeUser('user');
        $product = $this->makeProduct(10);

        $this->actingAs($user);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('products.index'))->assertOk();
        $this->get(route('products.show', $product))->assertOk();

        $this->get(route('sales.index'))->assertForbidden();
        $this->get(route('sales.create'))->assertForbidden();
        $this->get(route('purchases.index'))->assertForbidden();
        $this->get(route('purchases.create'))->assertForbidden();
        $this->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertForbidden();
        $this->assertSame(10, $product->fresh()->stock);

        $this->get(route('users.index'))->assertForbidden();
        $this->get(route('categories.index'))->assertForbidden();
        $this->get(route('products.create'))->assertForbidden();
    }

    // ---------- Search & pagination ----------

    public function test_product_search_filter_and_pagination(): void
    {
        $admin = $this->makeUser('admin');

        foreach (range(1, 12) as $n) {
            $this->makeProduct(10, ['name' => "Beras Kualitas {$n}"]);
        }
        $this->makeProduct(10, ['name' => 'Sabun Mandi']);

        $this->actingAs($admin);

        $page1 = $this->get(route('products.index'));
        $this->assertCount(10, $page1->viewData('products'));

        $search = $this->get(route('products.index', ['q' => 'Sabun']));
        $search->assertSee('Sabun Mandi');
        $this->assertCount(1, $search->viewData('products'));

        $filtered = $this->get(route('products.index', ['q' => 'Beras', 'page' => 2]));
        $this->assertCount(2, $filtered->viewData('products'));
    }

    public function test_sales_date_and_status_filters(): void
    {
        $admin = $this->makeUser('admin');
        $product = $this->makeProduct(10);

        $this->actingAs($admin)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $today = now()->toDateString();

        $hit = $this->get(route('sales.index', ['from' => $today, 'to' => $today, 'status' => 'completed']));
        $this->assertCount(1, $hit->viewData('sales'));

        $miss = $this->get(route('sales.index', ['status' => 'cancelled']));
        $this->assertCount(0, $miss->viewData('sales'));
    }

    // ---------- Dashboard ----------

    public function test_dashboard_numbers_come_from_database(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(1, ['minimum_stock' => 5]); // stok rendah
        $this->makeProduct(50);

        $stats = $this->actingAs($admin)->get(route('dashboard'))->viewData('stats');

        $this->assertSame(2, $stats['products']);
        $this->assertSame(1, $stats['low_stock']);
    }
}
