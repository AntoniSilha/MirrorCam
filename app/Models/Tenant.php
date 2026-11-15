<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $table = 'tenants';

    protected $primaryKey = 'id_tenant';

    protected $guarded = ['id_tenant'];
    public function produks()
    {
        return $this->hasMany(Produk::class, 'id_tenant', 'id_tenant');
    }
}
