<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailOperasionalTenant extends Model
{
    protected $table = 'detail_operasional_tenants';

    protected $primaryKey = 'id_operasional';

    protected $guarded = [];

    /**
     * Relationship: Associated Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }
}
