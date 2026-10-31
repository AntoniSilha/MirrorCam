<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mitra extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama_toko',
        'alamat_lengkap',
        'kota_operasional',
        'whatsapp_toko',
        'jam_buka',
        'jam_tutup',
        'nama_pengelola',
        'nik',
        'jabatan_pengelola',
        'foto_ktp_path',
        'bank_name',
        'nomor_rekening',
        'nama_pemilik_rekening',
        'status',
        'catatan_penolakan',
    ];

    /**
     * Relasi ke User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
