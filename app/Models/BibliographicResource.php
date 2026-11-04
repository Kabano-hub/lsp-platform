<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BibliographicResource extends Model
{
    protected $fillable = [
        'tenant_id',
        'isbn',
        'title',
        'author',
        'metadata_format',
        'marc21_json',
        'price',
        'available_copies',
        'search_hits',
    ];

    protected function casts(): array
    {
        return [
            'marc21_json' => 'array',
            'price' => 'decimal:2',
            'available_copies' => 'integer',
            'search_hits' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantLibrary::class, 'tenant_id');
    }

    public function pdaRequests(): HasMany
    {
        return $this->hasMany(PdaRequest::class, 'resource_id');
    }

    public function forecasts(): HasMany
    {
        return $this->hasMany(DemandForecast::class, 'resource_id');
    }

    public function latestForecast(): HasOne
    {
        return $this->hasOne(DemandForecast::class, 'resource_id')->latestOfMany();
    }
}
