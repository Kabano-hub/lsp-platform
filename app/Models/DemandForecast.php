<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemandForecast extends Model
{
    protected $fillable = [
        'tenant_id',
        'resource_id',
        'aps_score',
        'estimated_roi_months',
        'recommendation',
    ];

    protected function casts(): array
    {
        return [
            'aps_score' => 'integer',
            'estimated_roi_months' => 'decimal:1',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantLibrary::class, 'tenant_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(BibliographicResource::class, 'resource_id');
    }
}
