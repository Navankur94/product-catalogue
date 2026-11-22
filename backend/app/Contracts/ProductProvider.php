<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Data\Product;
use App\Exceptions\ProductProviderUnavailable;

/**
 * Source of product data. Implementations own transport, caching and mapping
 * into the canonical Product shape; business rules live in CatalogueService.
 */
interface ProductProvider
{
    /**
     * @return list<Product>
     *
     * @throws ProductProviderUnavailable
     */
    public function all(): array;

    /**
     * @throws ProductProviderUnavailable
     */
    public function find(int $id): ?Product;
}
