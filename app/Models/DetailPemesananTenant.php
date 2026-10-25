<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DetailPemesananTenant extends Model {
    protected $table = 'detail_pemesanan_tenants';
    protected $primaryKey = 'id_pemesanan';
    protected $guarded = [];
}