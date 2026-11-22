<?php

declare(strict_types=1);

namespace App\Services\Products;

use App\Contracts\ProductProvider;
use App\Data\Product;
use App\Exceptions\ProductProviderUnavailable;
use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as Http;

/**
 * Fake Store API adapter. The only class that knows Fake Store's URL and payload.
 */
final class FakeStoreProductProvider implements ProductProvider
{
    private const CACHE_KEY = 'catalogue.fake_store.products.v2';

    public function __construct(
        private readonly Http $http,
        private readonly Cache $cache,
        private readonly string $baseUrl,
        private readonly int $timeoutSeconds,
        private readonly int $cacheTtlSeconds,
    ) {}

    public function all(): array
    {
        // Only successful responses reach the cache: a thrown exception skips the write.
        $raw = $this->cache->remember(self::CACHE_KEY, $this->cacheTtlSeconds, fn (): array => $this->fetch());

        return array_values(array_filter(array_map($this->map(...), $raw)));
    }

    public function find(int $id): ?Product
    {
        foreach ($this->all() as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @return list<mixed>
     */
    private function fetch(): array
    {
        try {
            $response = $this->http
                ->acceptJson()
                ->timeout($this->timeoutSeconds)
                ->retry(2, 200, throw: false)
                ->get(rtrim($this->baseUrl, '/').'/products');
        } catch (ConnectionException $e) {
            throw new ProductProviderUnavailable('Fake Store API connection failed.', previous: $e);
        }

        $payload = $response->json();

        if (! $response->successful() || ! is_array($payload) || ! array_is_list($payload)) {
            throw new ProductProviderUnavailable("Fake Store API returned an unusable response (HTTP {$response->status()}).");
        }

        return $payload;
    }

    /** Maps one upstream item; returns null for items missing required fields. */
    private function map(mixed $item): ?Product
    {
        if (! is_array($item)
            || ! is_int($item['id'] ?? null) || $item['id'] < 1
            || ! is_string($item['title'] ?? null) || trim($item['title']) === ''
            || ! is_numeric($item['price'] ?? null)
        ) {
            return null;
        }

        $rating = is_array($item['rating'] ?? null) ? $item['rating'] : [];

        return new Product(
            id: $item['id'],
            title: trim($item['title']),
            description: is_string($item['description'] ?? null) ? $item['description'] : '',
            category: is_string($item['category'] ?? null) ? $item['category'] : 'uncategorised',
            price: round((float) $item['price'], 2),
            image: is_string($item['image'] ?? null) ? $item['image'] : '',
            ratingRate: is_numeric($rating['rate'] ?? null) ? (float) $rating['rate'] : null,
            ratingCount: is_numeric($rating['count'] ?? null) ? (int) $rating['count'] : null,
        );
    }
}
