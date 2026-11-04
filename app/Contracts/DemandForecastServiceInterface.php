<?php

namespace App\Contracts;

use App\Models\BibliographicResource;
use App\Models\DemandForecast;
use App\Models\TenantLibrary;

interface DemandForecastServiceInterface
{
    public function evaluateAndForecast(BibliographicResource $resource, TenantLibrary $tenant): DemandForecast;
}
