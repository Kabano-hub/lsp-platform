<?php

namespace App\Services;

use App\Contracts\DemandForecastServiceInterface;
use App\Models\BibliographicResource;
use App\Models\DemandForecast;
use App\Models\PdaRequest;
use App\Models\TenantLibrary;

class DemandForecastService implements DemandForecastServiceInterface
{
    public function evaluateAndForecast(BibliographicResource $resource, TenantLibrary $tenant): DemandForecast
    {
        $pdaRequestsCount = PdaRequest::where('resource_id', $resource->id)->count();
        $searchHits = (int) $resource->search_hits;

        // APS: im więcej zgłoszeń PDA i wyszukań, tym wyższy popyt (maks. 100).
        $aps = (int) min(100, ($pdaRequestsCount * 25) + ($searchHits * 2) + 15);

        $price = (float) $resource->price;
        $estimatedRoiMonths = round($price / max(1, $aps / 10), 1);

        if ($aps >= $tenant->auto_pda_threshold && $price <= 250) {
            $recommendation = 'AUTO_BUY';
            $resource->available_copies = $resource->available_copies + 1;
            $resource->save();
        } else {
            $recommendation = 'LIBRARIAN_REVIEW';
        }

        return DemandForecast::create([
            'tenant_id' => $tenant->id,
            'resource_id' => $resource->id,
            'aps_score' => $aps,
            'estimated_roi_months' => $estimatedRoiMonths,
            'recommendation' => $recommendation,
        ]);
    }
}
