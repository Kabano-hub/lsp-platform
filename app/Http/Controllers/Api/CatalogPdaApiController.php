<?php

namespace App\Http\Controllers\Api;

use App\Contracts\DemandForecastServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\BibliographicResource;
use App\Models\PdaRequest;
use App\Models\TenantLibrary;
use App\ViewModels\CatalogDashboardViewModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogPdaApiController extends Controller
{
    public function __construct(
        private CatalogDashboardViewModel $dashboard,
        private DemandForecastServiceInterface $forecasts,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $tenantId = (int) $request->attributes->get('tenant_id', 1);
        $data = $this->dashboard->build($tenantId, $request->query('q'));

        $resources = $data['rows']->map(function (array $row) {
            $book = $row['resource'];
            $forecast = $row['forecast'];

            return [
                'id' => $book->id,
                'isbn' => $book->isbn,
                'title' => $book->title,
                'author' => $book->author,
                'price' => $book->price,
                'available_copies' => $book->available_copies,
                'search_hits' => $book->search_hits,
                'pda_requests_count' => $row['pda_requests_count'],
                'marc21' => $row['marc21'],
                'dublin_core' => $row['dublin_core'],
                'forecast' => $forecast ? [
                    'aps_score' => $forecast->aps_score,
                    'estimated_roi_months' => $forecast->estimated_roi_months,
                    'recommendation' => $forecast->recommendation,
                ] : null,
            ];
        })->values();

        return response()->json([
            'tenant' => $data['tenant'] ? [
                'id' => $data['tenant']->id,
                'code' => $data['tenant']->code,
                'name' => $data['tenant']->name,
                'auto_pda_threshold' => $data['tenant']->auto_pda_threshold,
            ] : null,
            'resources' => $resources,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resource_id' => ['required', 'integer'],
            'user_email' => ['required', 'email'],
            'course_code' => ['required', 'string', 'max:32'],
        ]);

        $tenant = $this->currentTenant($request);
        $resource = BibliographicResource::where('tenant_id', $tenant->id)
            ->where('id', $validated['resource_id'])
            ->first();

        if (! $resource) {
            return response()->json([
                'message' => 'Nie znaleziono zasobu w wybranej bibliotece.',
            ], 404);
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

        $resource->refresh();

        return response()->json([
            'message' => 'Zgłoszenie PDA zapisane.',
            'pda_request' => $pda,
            'forecast' => $forecast,
            'available_copies' => $resource->available_copies,
        ], 201);
    }

    private function currentTenant(Request $request): TenantLibrary
    {
        $tenantId = (int) $request->attributes->get('tenant_id', 1);
        $tenant = TenantLibrary::find($tenantId);

        if (! $tenant) {
            $tenant = TenantLibrary::query()->orderBy('id')->firstOrFail();
        }

        return $tenant;
    }
}
