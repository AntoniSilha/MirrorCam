<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class GaleriFotoProduk extends Model {
    protected $table = 'galeri_foto_produks';
    protected $primaryKey = 'id_foto';
    protected $guarded = [];
}