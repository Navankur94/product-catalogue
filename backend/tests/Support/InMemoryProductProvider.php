<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Contracts\ProductProvider;
use App\Data\Product;

/** Second ProductProvider implementation: proves CatalogueService is provider-agnostic. */
final class InMemoryProductProvider implements ProductProvider
{
    /** @param list<Product> $products */
    public function __construct(private readonly array $products) {}

    public function all(): array
    {
        return $this->products;
    }

    public function find(int $id): ?Product
    {
        foreach ($this->products as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }

    public static function product(int $id, string $category = 'electronics', ?float $rate = 4.0, string $title = '', string $description = ''): Product
    {
        return new Product(
            id: $id,
            title: $title !== '' ? $title : "Product {$id}",
            description: $description !== '' ? $description : "Description {$id}",
            category: $category,
            price: 10.0 + $id,
            image: "https://images.test/{$id}.png",
            ratingRate: $rate,
            ratingCount: $rate === null ? null : 10,
        );
    }
}
