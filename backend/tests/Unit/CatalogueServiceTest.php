<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Data\Product;
use App\Services\CatalogueService;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryProductProvider as Provider;

final class CatalogueServiceTest extends TestCase
{
    /** @param list<Product> $products */
    private function service(array $products): CatalogueService
    {
        return new CatalogueService(new Provider($products));
    }

    /** @param list<Product> $products @return list<int> */
    private function ids(array $products): array
    {
        return array_map(fn (Product $p): int => $p->id, $products);
    }

    public function test_featured_returns_five_highest_rated_with_id_tie_break(): void
    {
        $service = $this->service([
            Provider::product(1, rate: 3.0),
            Provider::product(2, rate: 4.9),
            Provider::product(3, rate: 4.5),
            Provider::product(4, rate: 4.9),
            Provider::product(5, rate: null),
            Provider::product(6, rate: 4.0),
            Provider::product(7, rate: 4.5),
        ]);

        $this->assertSame([2, 4, 3, 7, 6], $this->ids($service->featured()));
    }

    public function test_featured_returns_all_when_fewer_than_five(): void
    {
        $this->assertSame([2, 1], $this->ids($this->service([Provider::product(1, rate: 1.0), Provider::product(2, rate: 2.0)])->featured()));
    }

    public function test_category_splits_top_five_and_others_without_duplicates(): void
    {
        $products = [Provider::product(99, 'jewelery', 5.0)];
        foreach (range(1, 7) as $id) {
            $products[] = Provider::product($id, 'Electronics', $id / 2);
        }

        $listing = $this->service($products)->category('electronics');

        $this->assertNotNull($listing);
        $this->assertSame('Electronics', $listing['category']);
        $this->assertSame([7, 6, 5, 4, 3], $this->ids($listing['featured']));
        $this->assertSame([2, 1], $this->ids($listing['others']));
    }

    public function test_unknown_category_returns_null(): void
    {
        $this->assertNull($this->service([Provider::product(1)])->category('garden'));
    }

    public function test_search_is_case_insensitive_across_title_description_and_category(): void
    {
        $service = $this->service([
            Provider::product(1, 'electronics', title: 'Red Phone'),
            Provider::product(2, 'jewelery', description: 'Gold ring for a phone lover'),
            Provider::product(3, 'Phones & accessories'),
            Provider::product(4, 'jewelery', title: 'Bracelet'),
        ]);

        $this->assertSame([1, 2, 3], $this->ids($service->search('  PHONE ')));
        $this->assertSame([], $service->search('nothing-matches'));
        $this->assertCount(4, $service->search(''));
        $this->assertCount(4, $service->search(null));
    }

    public function test_categories_are_unique_and_sorted(): void
    {
        $service = $this->service([
            Provider::product(1, 'jewelery'),
            Provider::product(2, 'electronics'),
            Provider::product(3, 'jewelery'),
        ]);

        $this->assertSame(['electronics', 'jewelery'], $service->categories());
    }
}
