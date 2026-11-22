<?php
namespace App\Contracts;
interface ProductProvider
{
    /** @return array<int, array<string, mixed>> */
    public function all(): array;
    /** @return array<string, mixed>|null */
    public function find(int $id): ?array;
}
