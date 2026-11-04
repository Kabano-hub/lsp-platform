<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdaRequest extends Model
{
    protected $fillable = [
        'tenant_id',
        'resource_id',
        'user_email',
        'course_code',
        'status',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantLibrary::class, 'tenant_id');
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(BibliographicResource::class, 'resource_id');
    }
}
