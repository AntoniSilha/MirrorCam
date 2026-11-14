<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
