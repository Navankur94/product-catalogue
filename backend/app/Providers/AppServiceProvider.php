<?php
namespace App\Providers;
use App\Contracts\ProductProvider;
use App\Services\Products\FakeStoreProductProvider;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductProvider::class, function ($app) {
            return match (config('app.products_provider', env('PRODUCTS_PROVIDER', 'fake_store'))) {
                'fake_store' => $app->make(FakeStoreProductProvider::class),
                default => throw new \InvalidArgumentException('Unsupported product provider configured.'),
            };
        });
    }
    public function boot(): void {}
}
