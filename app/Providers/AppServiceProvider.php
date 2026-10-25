<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.matcha');

        // Otorisasi di server (bukan sekadar menyembunyikan tombol).
        Gate::define('manage-master', fn (User $user) => $user->isAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-transactions', fn (User $user) => $user->canTransact());
        Gate::define('cancel-transaction', fn (User $user) => $user->isAdmin());
        Gate::define('view-transaction', fn (User $user, $transaction) => $user->isAdmin()
            || $transaction->user_id === $user->id);

        Blade::directive('rupiah', fn ($expression) => "<?php echo 'Rp ' . number_format((float) ({$expression}), 0, ',', '.'); ?>");
    }
}
