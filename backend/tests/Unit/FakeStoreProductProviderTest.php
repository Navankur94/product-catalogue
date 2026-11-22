<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Contracts\ProductProvider;
use App\Exceptions\ProductProviderUnavailable;
use App\Services\Products\FakeStoreProductProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\Support\FakeStoreFixtures;
use Tests\TestCase;

final class FakeStoreProductProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Sleep::fake();
    }

    private function provider(): ProductProvider
    {
        return $this->app->make(ProductProvider::class);
    }

    public function test_container_binds_fake_store_adapter_by_default(): void
    {
        $this->assertInstanceOf(FakeStoreProductProvider::class, $this->provider());
    }

    public function test_maps_upstream_payload_to_canonical_products(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response(FakeStoreFixtures::products())]);

        $product = $this->provider()->find(4);

        $this->assertNotNull($product);
        $this->assertSame(
            ['id' => 4, 'title' => 'Delta ring', 'description' => 'Silver dragon ring', 'category' => 'jewelery', 'price' => 695.0, 'image' => 'https://fakestore.test/img/4.png', 'rating' => ['rate' => 4.6, 'count' => 400]],
            $product->jsonSerialize(),
        );
        $this->assertNull($this->provider()->find(999));
    }

    public function test_skips_malformed_items_and_tolerates_missing_rating(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response([
            ['id' => 1, 'title' => 'Valid', 'price' => 5, 'category' => 'misc'],
            ['id' => 'two', 'title' => 'Bad id', 'price' => 5],
            ['id' => 3, 'title' => '', 'price' => 5],
            ['id' => 4, 'title' => 'No price'],
            'not-an-object',
        ])]);

        $products = $this->provider()->all();

        $this->assertCount(1, $products);
        $this->assertNull($products[0]->jsonSerialize()['rating']);
    }

    public function test_caches_successful_responses(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response(FakeStoreFixtures::products())]);

        $this->provider()->all();
        $this->provider()->all();
        $this->provider()->find(1);

        Http::assertSentCount(1);
    }

    public function test_server_error_throws_unavailable_after_retrying_and_is_not_cached(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::sequence()
            ->push('error', 500)->push('error', 500)
            ->push(FakeStoreFixtures::products())]);

        try {
            $this->provider()->all();
            $this->fail('Expected ProductProviderUnavailable.');
        } catch (ProductProviderUnavailable) {
            Http::assertSentCount(2);
        }

        // Failure was not cached: next call reaches upstream and succeeds.
        $this->assertCount(7, $this->provider()->all());
    }

    public function test_non_list_payload_throws_unavailable(): void
    {
        Http::fake([FakeStoreFixtures::URL => Http::response(['error' => 'unexpected'])]);

        $this->expectException(ProductProviderUnavailable::class);
        $this->provider()->all();
    }

    public function test_connection_failure_throws_unavailable(): void
    {
        Http::fake([FakeStoreFixtures::URL => fn () => throw new ConnectionException('timeout')]);

        $this->expectException(ProductProviderUnavailable::class);
        $this->provider()->all();
    }
}
