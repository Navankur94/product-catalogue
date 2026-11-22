<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\EnquiryController;
Route::get('/health', fn () => response()->json(['status' => 'ok']));
Route::get('/products', [CatalogueController::class, 'index']);
Route::get('/products/featured', [CatalogueController::class, 'featured']);
Route::get('/categories', [CatalogueController::class, 'categories']);
Route::get('/categories/{category}/products', [CatalogueController::class, 'byCategory']);
Route::get('/products/{id}', [CatalogueController::class, 'show'])->whereNumber('id');
Route::post('/enquiries', [EnquiryController::class, 'store']);
