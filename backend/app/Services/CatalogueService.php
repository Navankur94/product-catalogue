<?php
namespace App\Services;
use App\Contracts\ProductProvider;
use Illuminate\Support\Str;
class CatalogueService
{
    public function __construct(private ProductProvider $provider) {}
    private function rating(array $product): float { return (float) data_get($product, 'rating.rate', 0); }
    public function all(?string $search = null, ?string $category = null): array
    {
        $products = collect($this->provider->all());
        if ($category !== null) $products = $products->filter(fn ($p) => Str::lower($p['category'] ?? '') === Str::lower($category));
        if ($search !== null && trim($search) !== '') {
            $term = Str::lower(trim($search));
            $products = $products->filter(fn ($p) => Str::contains(Str::lower(($p['title'] ?? '').' '.($p['description'] ?? '').' '.($p['category'] ?? '')), $term));
        }
        return $products->values()->all();
    }
    public function featured(?string $category = null): array
    {
        return collect($this->all(null, $category))->sort(function ($a, $b) {
            $rating = $this->rating($b) <=> $this->rating($a);
            return $rating !== 0 ? $rating : (($a['id'] ?? 0) <=> ($b['id'] ?? 0));
        })->take(5)->values()->all();
    }
    public function categories(): array
    {
        return collect($this->provider->all())->pluck('category')->filter()->unique()->sort()->values()->all();
    }
    public function find(int $id): ?array { return $this->provider->find($id); }
}
