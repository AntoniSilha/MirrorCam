<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DetailOperasionalTenant extends Model {
    protected $table = 'detail_operasional_tenants';
    protected $primaryKey = 'id_operasional';
    protected $guarded = [];
}