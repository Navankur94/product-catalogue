<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\ProductProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\Support\FakeStoreFixtures;
use Tests\Support\InMemoryProductProvider;
use Tests\TestCase;

/** End-to-end through Laravel's HTTP kernel, CatalogueService and the Fake Store adapter (upstream faked). */
final class CatalogueApiTest extends TestCase
{
    private const PRODUCT_SHAPE = ['id', 'title', 'description', 'category', 'price', 'image', 'rating' => ['rate', 'count']];

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Sleep::fake();
    }

    private function upstreamReturnsFixtures(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response(FakeStoreFixtures::products())]);
    }

    public function test_health(): void
    {
        $this->getJson('/api/health')->assertOk()->assertExactJson(['status' => 'ok']);
    }

    public function test_featured_returns_five_highest_rated_products(): void
    {
        $this->upstreamReturnsFixtures();

        $response = $this->getJson('/api/products/featured')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['data' => ['*' => self::PRODUCT_SHAPE]]);

        // 4.8 (id 5), 4.8 (id 7), 4.7, 4.6, 4.1 — tie broken by lower id.
        $this->assertSame([5, 7, 3, 4, 2], $response->json('data.*.id'));
    }

    public function test_list_without_query_returns_all_products(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/products')->assertOk()->assertJsonCount(7, 'data');
    }

    public function test_search_matches_title_description_and_category_case_insensitively(): void
    {
        $this->upstreamReturnsFixtures();

        $this->assertSame([1, 5], $this->getJson('/api/products?q=Phone')->assertOk()->json('data.*.id'));
        $this->assertSame([4], $this->getJson('/api/products?q=dragon')->json('data.*.id'));
        $this->assertSame([2, 3, 7], $this->getJson('/api/products?q=CLOTHING')->json('data.*.id'));
    }

    public function test_search_without_matches_returns_empty_list(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/products?q=zzz-no-match')->assertOk()->assertExactJson(['data' => []]);
    }

    public function test_search_rejects_invalid_query(): void
    {
        $this->getJson('/api/products?q='.str_repeat('a', 121))->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->getJson('/api/products?q[]=x')->assertUnprocessable()->assertJsonValidationErrors('q');
    }

    public function test_categories_are_unique_and_sorted(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/categories')->assertOk()
            ->assertExactJson(['data' => ['electronics', 'jewelery', "men's clothing", "women's clothing"]]);
    }

    public function test_category_returns_ranked_featured_and_others(): void
    {
        $this->app->instance(ProductProvider::class, new InMemoryProductProvider(array_map(
            fn (int $id) => InMemoryProductProvider::product($id, $id === 8 ? 'jewelery' : 'electronics', $id / 2),
            range(1, 8),
        )));

        $response = $this->getJson('/api/categories/electronics/products')
            ->assertOk()
            ->assertJsonPath('data.category', 'electronics')
            ->assertJsonStructure(['data' => ['category', 'featured' => ['*' => self::PRODUCT_SHAPE], 'others' => ['*' => self::PRODUCT_SHAPE]]]);

        $this->assertSame([7, 6, 5, 4, 3], $response->json('data.featured.*.id'));
        $this->assertSame([2, 1], $response->json('data.others.*.id'));
    }

    public function test_category_with_encoded_name_is_resolved(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/categories/'.rawurlencode("men's clothing").'/products')
            ->assertOk()
            ->assertJsonPath('data.category', "men's clothing")
            ->assertJsonCount(2, 'data.featured')
            ->assertJsonCount(0, 'data.others');
    }

    public function test_unknown_category_returns_404(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/categories/garden/products')->assertNotFound()->assertExactJson(['message' => 'Category not found.']);
    }

    public function test_product_details(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/products/3')->assertOk()
            ->assertJsonPath('data.id', 3)
            ->assertJsonPath('data.title', 'Gamma jacket')
            ->assertJsonStructure(['data' => self::PRODUCT_SHAPE]);
    }

    public function test_unknown_or_invalid_product_id_returns_404(): void
    {
        $this->upstreamReturnsFixtures();

        $this->getJson('/api/products/999')->assertNotFound()->assertExactJson(['message' => 'Product not found.']);
        $this->getJson('/api/products/0')->assertNotFound();
        $this->getJson('/api/products/99999999999999999999999')->assertNotFound();
        $this->getJson('/api/products/abc')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    }

    public function test_upstream_failure_returns_503_on_every_catalogue_endpoint(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response('down', 500)]);

        foreach (['/api/products', '/api/products/featured', '/api/products/1', '/api/categories', '/api/categories/electronics/products'] as $uri) {
            $this->getJson($uri)->assertStatus(503)
                ->assertExactJson(['message' => 'The product catalogue is temporarily unavailable.']);
        }
    }

    public function test_api_errors_are_json_without_accept_header(): void
    {
        $this->get('/api/does-not-exist')->assertNotFound()->assertExactJson(['message' => 'Not found.']);
    }
}
