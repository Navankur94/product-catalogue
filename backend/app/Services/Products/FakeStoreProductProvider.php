<?php
namespace App\Services\Products;
use App\Contracts\ProductProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
class FakeStoreProductProvider implements ProductProvider
{
    private function request(string $path): array
    {
        $base = rtrim((string) config('services.fake_store.base_url'), '/');
        $response = Http::acceptJson()->timeout((int) config('services.fake_store.timeout', 8))->get($base.$path);
        if (!$response->successful()) {
            throw new RuntimeException('The product catalogue provider is temporarily unavailable.');
        }
        return $response->json() ?? [];
    }
    public function all(): array
    {
        return Cache::remember('catalogue.products.v1', now()->addMinutes(5), fn () => $this->request('/products'));
    }
    public function find(int $id): ?array
    {
        $product = collect($this->all())->firstWhere('id', $id);
        return $product ?: null;
    }
}
