<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Biblioteka (tenant) bierze się z nagłówka X-Tenant-ID
     * albo z parametru ?tenant_id=. Gdy brak obu, zostaje biblioteka nr 1.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $raw = $request->header('X-Tenant-ID');

        if ($raw === null || $raw === '') {
            $raw = $request->query('tenant_id', 1);
        }

        if ($raw === null || $raw === '') {
            $raw = 1;
        }

        $tenantId = (int) $raw;

        if ($tenantId < 1) {
            $tenantId = 1;
        }

        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
