<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekeningSettlementTenant extends Model
{
    protected $table = 'rekening_settlement_tenants';

    protected $primaryKey = 'id_rekening';

    protected $guarded = [];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }
}
