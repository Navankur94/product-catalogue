<?php

use App\Http\Controllers\Api\CatalogueController;
use App\Http\Controllers\Api\EnquiryController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/products', [CatalogueController::class, 'index']);
Route::get('/products/featured', [CatalogueController::class, 'featured']);
Route::get('/products/{id}', [CatalogueController::class, 'show'])->whereNumber('id');

Route::get('/categories', [CatalogueController::class, 'categories']);
Route::get('/categories/{category}/products', [CatalogueController::class, 'category']);

Route::post('/enquiries', [EnquiryController::class, 'store'])->middleware('throttle:enquiries');
