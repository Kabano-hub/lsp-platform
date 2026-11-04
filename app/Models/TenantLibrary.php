<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TenantLibrary extends Model
{
    protected $fillable = [
        'code',
        'name',
        'budget',
        'auto_pda_threshold',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'auto_pda_threshold' => 'integer',
        ];
    }

    public function resources(): HasMany
    {
        return $this->hasMany(BibliographicResource::class, 'tenant_id');
    }

    public function pdaRequests(): HasMany
    {
        return $this->hasMany(PdaRequest::class, 'tenant_id');
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(DemandForecast::class, 'tenant_id');
    }
}
