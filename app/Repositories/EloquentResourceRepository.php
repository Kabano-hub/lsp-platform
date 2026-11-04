<?php

namespace App\Repositories;

use App\Contracts\ResourceRepositoryInterface;
use App\Models\BibliographicResource;
use Illuminate\Support\Collection;

class EloquentResourceRepository implements ResourceRepositoryInterface
{
    public function getAllByTenant(int $tenantId): Collection
    {
        return BibliographicResource::with(['pdaRequests', 'latestForecast'])
            ->where('tenant_id', $tenantId)
            ->orderBy('title')
            ->get();
    }

    public function findById(int $id): ?BibliographicResource
    {
        return BibliographicResource::with(['pdaRequests', 'latestForecast'])->find($id);
    }

    public function createResource(array $data): BibliographicResource
    {
        return BibliographicResource::create($data);
    }
}
