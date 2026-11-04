<?php

namespace App\Contracts;

use App\Models\BibliographicResource;
use Illuminate\Support\Collection;

interface ResourceRepositoryInterface
{
    public function getAllByTenant(int $tenantId): Collection;

    public function findById(int $id): ?BibliographicResource;

    public function createResource(array $data): BibliographicResource;
}
