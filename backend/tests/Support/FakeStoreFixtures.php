<?php

declare(strict_types=1);

namespace Tests\Support;

/** Payloads shaped like Fake Store API's GET /products response. */
final class FakeStoreFixtures
{
    public const URL = 'https://fakestore.test/products';

    /** @return list<array<string, mixed>> */
    public static function products(): array
    {
        return [
            ['id' => 1, 'title' => 'Alpha phone', 'price' => 109.95, 'description' => 'Smart phone with OLED screen', 'category' => 'electronics', 'image' => 'https://fakestore.test/img/1.png', 'rating' => ['rate' => 3.9, 'count' => 120]],
            ['id' => 2, 'title' => 'Beta running shoe', 'price' => 22.3, 'description' => 'Lightweight trainer', 'category' => "men's clothing", 'image' => 'https://fakestore.test/img/2.png', 'rating' => ['rate' => 4.1, 'count' => 259]],
            ['id' => 3, 'title' => 'Gamma jacket', 'price' => 55.99, 'description' => 'Waterproof shell', 'category' => "men's clothing", 'image' => 'https://fakestore.test/img/3.png', 'rating' => ['rate' => 4.7, 'count' => 500]],
            ['id' => 4, 'title' => 'Delta ring', 'price' => 695, 'description' => 'Silver dragon ring', 'category' => 'jewelery', 'image' => 'https://fakestore.test/img/4.png', 'rating' => ['rate' => 4.6, 'count' => 400]],
            ['id' => 5, 'title' => 'Epsilon SSD', 'price' => 109, 'description' => 'Fast PHONE-compatible storage', 'category' => 'electronics', 'image' => 'https://fakestore.test/img/5.png', 'rating' => ['rate' => 4.8, 'count' => 319]],
            ['id' => 6, 'title' => 'Zeta monitor', 'price' => 599, 'description' => '4K display', 'category' => 'electronics', 'image' => 'https://fakestore.test/img/6.png', 'rating' => ['rate' => 2.9, 'count' => 250]],
            ['id' => 7, 'title' => 'Eta dress', 'price' => 39.99, 'description' => 'Summer dress', 'category' => "women's clothing", 'image' => 'https://fakestore.test/img/7.png', 'rating' => ['rate' => 4.8, 'count' => 145]],
        ];
    }
}
