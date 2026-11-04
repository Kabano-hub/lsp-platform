<?php

namespace App\Http\Controllers;

use App\Contracts\DemandForecastServiceInterface;
use App\Models\BibliographicResource;
use App\Models\PdaRequest;
use App\Models\TenantLibrary;
use App\ViewModels\CatalogDashboardViewModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebDashboardController extends Controller
{
    public function __construct(
        private CatalogDashboardViewModel $dashboard,
        private DemandForecastServiceInterface $forecasts,
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = (int) $request->attributes->get('tenant_id', 1);
        $data = $this->dashboard->build($tenantId, $request->query('q'));

        return view('dashboard', $data);
    }

    public function storePda(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'integer'],
            'user_email' => ['required', 'email'],
            'course_code' => ['required', 'string', 'max:32'],
        ]);

        $tenantId = (int) $request->attributes->get('tenant_id', 1);
        $tenant = TenantLibrary::find($tenantId) ?? TenantLibrary::query()->orderBy('id')->firstOrFail();

        $resource = BibliographicResource::where('tenant_id', $tenant->id)
            ->where('id', $validated['resource_id'])
            ->first();

        if (! $resource) {
            return redirect()
                ->route('dashboard', ['tenant_id' => $tenant->id])
                ->with('error', 'Nie znaleziono zasobu w wybranej bibliotece.');
        }

        $pda = PdaRequest::create([
            'tenant_id' => $tenant->id,
            'resource_id' => $resource->id,
            'user_email' => $validated['user_email'],
            'course_code' => $validated['course_code'],
            'status' => 'NEW',
        ]);

        $forecast = $this->forecasts->evaluateAndForecast($resource, $tenant);

        if ($forecast->recommendation === 'AUTO_BUY') {
            $pda->status = 'AUTO_BUY';
            $pda->save();
        }

        $message = 'Zapisano zgłoszenie PDA dla „'.$resource->title.'”. '
            .'APS = '.$forecast->aps_score
            .', ROI ≈ '.$forecast->estimated_roi_months.' mies.'
            .', rekomendacja: '.$forecast->recommendation.'.';

        return redirect()
            ->route('dashboard', ['tenant_id' => $tenant->id])
            ->with('status', $message);
    }
}
