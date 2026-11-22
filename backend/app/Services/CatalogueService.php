<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductProvider;
use App\Data\Product;

/**
 * Catalogue business rules: search, featured selection and category composition.
 * Provider-agnostic: works with any ProductProvider implementation.
 */
final class CatalogueService
{
    public const FEATURED_LIMIT = 5;

    public function __construct(private readonly ProductProvider $provider) {}

    /**
     * Case-insensitive substring search across title, description and category.
     * A null or blank term returns every product.
     *
     * @return list<Product>
     */
    public function search(?string $term = null): array
    {
        $products = $this->provider->all();
        $needle = mb_strtolower(trim($term ?? ''));

        if ($needle === '') {
            return $products;
        }

        return array_values(array_filter(
            $products,
            fn (Product $p): bool => str_contains(
                mb_strtolower($p->title."\n".$p->description."\n".$p->category),
                $needle,
            ),
        ));
    }

    /**
     * The five highest-rated products (ties broken by lowest id).
     *
     * @return list<Product>
     */
    public function featured(): array
    {
        return array_slice($this->rankByRating($this->provider->all()), 0, self::FEATURED_LIMIT);
    }

    /**
     * Top-rated products of a category followed by the remaining ones, without duplicates.
     * Returns null when no product belongs to the category (case-insensitive match).
     *
     * @return array{category: string, featured: list<Product>, others: list<Product>}|null
     */
    public function category(string $category): ?array
    {
        $wanted = mb_strtolower(trim($category));
        $inCategory = array_values(array_filter(
            $this->provider->all(),
            fn (Product $p): bool => mb_strtolower($p->category) === $wanted,
        ));

        if ($inCategory === []) {
            return null;
        }

        $ranked = $this->rankByRating($inCategory);

        return [
            'category' => $inCategory[0]->category,
            'featured' => array_slice($ranked, 0, self::FEATURED_LIMIT),
            'others' => array_slice($ranked, self::FEATURED_LIMIT),
        ];
    }

    /**
     * @return list<string>
     */
    public function categories(): array
    {
        $categories = array_unique(array_map(fn (Product $p): string => $p->category, $this->provider->all()));
        sort($categories, SORT_STRING | SORT_FLAG_CASE);

        return array_values($categories);
    }

    public function find(int $id): ?Product
    {
        return $this->provider->find($id);
    }

    /**
     * @param  list<Product>  $products
     * @return list<Product>
     */
    private function rankByRating(array $products): array
    {
        usort($products, fn (Product $a, Product $b): int => [$b->ratingRate ?? -1.0, $a->id] <=> [$a->ratingRate ?? -1.0, $b->id]);

        return $products;
    }
}
