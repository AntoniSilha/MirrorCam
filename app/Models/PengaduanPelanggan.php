<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PengaduanPelanggan extends Model {
    protected $table = 'pengaduan_pelanggans';
    protected $primaryKey = 'id_pengaduan';
    protected $guarded = [];
}