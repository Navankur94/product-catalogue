<?php

declare(strict_types=1);

namespace App\Data;

use JsonSerializable;

/**
 * Canonical product shape exposed by the API.
 *
 * Every ProductProvider maps its upstream payload into this class, so the
 * HTTP contract does not depend on any particular provider's field names.
 */
final readonly class Product implements JsonSerializable
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $category,
        public float $price,
        public string $image,
        public ?float $ratingRate = null,
        public ?int $ratingCount = null,
    ) {}

    /**
     * @return array{id: int, title: string, description: string, category: string, price: float, image: string, rating: array{rate: float, count: int}|null}
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'price' => $this->price,
            'image' => $this->image,
            'rating' => $this->ratingRate === null
                ? null
                : ['rate' => $this->ratingRate, 'count' => $this->ratingCount ?? 0],
        ];
    }
}
