<?php
namespace App\Http\Controllers\Api;
use App\Services\CatalogueService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Throwable;
class CatalogueController extends Controller
{
    public function __construct(private CatalogueService $catalogue) {}
    public function index(Request $request)
    {
        $validated = $request->validate(['q' => ['sometimes','string','max:120'], 'category' => ['sometimes','string','max:80']]);
        try { return response()->json(['data' => $this->catalogue->all($validated['q'] ?? null, $validated['category'] ?? null)]); }
        catch (Throwable $e) { report($e); return response()->json(['message' => 'Catalogue is temporarily unavailable.'], 503); }
    }
    public function featured(Request $request)
    {
        $validated = $request->validate(['category' => ['sometimes','string','max:80']]);
        try { return response()->json(['data' => $this->catalogue->featured($validated['category'] ?? null)]); }
        catch (Throwable $e) { report($e); return response()->json(['message' => 'Catalogue is temporarily unavailable.'], 503); }
    }
    public function categories()
    {
        try { return response()->json(['data' => $this->catalogue->categories()]); }
        catch (Throwable $e) { report($e); return response()->json(['message' => 'Catalogue is temporarily unavailable.'], 503); }
    }
    public function byCategory(string $category)
    {
        try { return response()->json(['featured' => $this->catalogue->featured($category), 'products' => $this->catalogue->all(null, $category)]); }
        catch (Throwable $e) { report($e); return response()->json(['message' => 'Catalogue is temporarily unavailable.'], 503); }
    }
    public function show(int $id)
    {
        try { $product = $this->catalogue->find($id); return $product ? response()->json(['data' => $product]) : response()->json(['message' => 'Product not found.'], 404); }
        catch (Throwable $e) { report($e); return response()->json(['message' => 'Catalogue is temporarily unavailable.'], 503); }
    }
}
