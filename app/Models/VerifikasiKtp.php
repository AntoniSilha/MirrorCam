<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VerifikasiKtp extends Model {
    protected $table = 'verifikasi_ktp';
    protected $primaryKey = 'id_ktp';
    protected $guarded = [];
}