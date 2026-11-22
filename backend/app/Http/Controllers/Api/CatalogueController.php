<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\CatalogueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * Thin HTTP adapter over CatalogueService. Upstream failures surface as
 * ProductProviderUnavailable, which renders itself as a 503.
 */
final class CatalogueController extends Controller
{
    public function __construct(private readonly CatalogueService $catalogue) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:120']]);

        return response()->json(['data' => $this->catalogue->search($validated['q'] ?? null)]);
    }

    public function featured(): JsonResponse
    {
        return response()->json(['data' => $this->catalogue->featured()]);
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => $this->catalogue->categories()]);
    }

    public function category(string $category): JsonResponse
    {
        $listing = mb_strlen($category) <= 80 ? $this->catalogue->category($category) : null;

        return $listing === null
            ? response()->json(['message' => 'Category not found.'], 404)
            : response()->json(['data' => $listing]);
    }

    public function show(string $id): JsonResponse
    {
        // Route guarantees digits; filter_var rejects 0 and integer overflow.
        $productId = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $product = $productId === false ? null : $this->catalogue->find($productId);

        return $product === null
            ? response()->json(['message' => 'Product not found.'], 404)
            : response()->json(['data' => $product]);
    }
}
