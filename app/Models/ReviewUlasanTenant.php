<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewUlasanTenant extends Model
{
    protected $table = 'review_ulasan_tenants';

    protected $primaryKey = 'id_review';

    protected $guarded = [];

    /**
     * Relationship: Associated Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'id_tenant', 'id_tenant');
    }

    /**
     * Relationship: Renter user account
     */
    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_akun_penyewa', 'id_akun');
    }
}
