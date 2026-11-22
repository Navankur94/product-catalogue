<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * Thrown by any ProductProvider when upstream product data cannot be obtained.
 * Rendered as a stable 503 JSON response; the original cause is still reported.
 */
final class ProductProviderUnavailable extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => 'The product catalogue is temporarily unavailable.'], 503);
    }
}
