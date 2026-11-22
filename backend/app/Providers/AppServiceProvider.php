<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ProductProvider;
use App\Services\Products\FakeStoreProductProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The single place that chooses the product source. Add a match arm to swap providers.
        $this->app->singleton(ProductProvider::class, function (Application $app): ProductProvider {
            $provider = (string) config('catalogue.provider');

            return match ($provider) {
                'fake_store' => new FakeStoreProductProvider(
                    http: $app->make(HttpFactory::class),
                    cache: $app->make('cache.store'),
                    baseUrl: (string) config('services.fake_store.base_url'),
                    timeoutSeconds: (int) config('services.fake_store.timeout'),
                    cacheTtlSeconds: (int) config('catalogue.cache_ttl'),
                ),
                default => throw new InvalidArgumentException("Unsupported PRODUCTS_PROVIDER [{$provider}]."),
            };
        });
    }

    public function boot(): void
    {
        RateLimiter::for('enquiries', fn (Request $request): Limit => Limit::perMinute((int) config('catalogue.enquiries_per_minute'))
            ->by($request->ip() ?? 'unknown'));
    }
}
