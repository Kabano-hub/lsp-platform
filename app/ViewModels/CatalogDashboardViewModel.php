<?php

namespace App\ViewModels;

use App\Contracts\ResourceRepositoryInterface;
use App\Converters\Marc21ToDublinCoreConverter;
use App\Models\TenantLibrary;

class CatalogDashboardViewModel
{
    public function __construct(
        private ResourceRepositoryInterface $resources,
        private Marc21ToDublinCoreConverter $converter,
    ) {
    }

    /**
     * Dane pod widok Blade i pod odpowiedź JSON API.
     */
    public function build(int $tenantId, ?string $search = null): array
    {
        $tenant = TenantLibrary::find($tenantId) ?? TenantLibrary::query()->orderBy('id')->first();
        $tenants = TenantLibrary::orderBy('name')->get();

        $items = $tenant
            ? $this->resources->getAllByTenant($tenant->id)
            : collect();

        $needle = mb_strtolower(trim((string) $search));

        if ($needle !== '') {
            $items = $items->filter(function ($resource) use ($needle) {
                $haystack = mb_strtolower($resource->title.' '.$resource->author.' '.$resource->isbn);

                return str_contains($haystack, $needle);
            })->values();
        }

        $rows = $items->map(function ($resource) {
            $marc = $resource->marc21_json ?? [];
            $isValid = $this->converter->validateMarc21($marc);

            return [
                'resource' => $resource,
                'marc21' => $marc,
                'marc21_valid' => $isValid,
                'dublin_core' => $isValid ? $this->converter->toDublinCore($marc) : null,
                'forecast' => $resource->latestForecast,
                'pda_requests_count' => $resource->pdaRequests->count(),
            ];
        })->values();

        return [
            'tenant' => $tenant,
            'tenants' => $tenants,
            'rows' => $rows,
            'search' => $search ?? '',
        ];
    }
}
