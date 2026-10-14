<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class RekeningSettlementTenant extends Model {
    protected $table = 'rekening_settlement_tenants';
    protected $primaryKey = 'id_rekening';
    protected $guarded = [];
}