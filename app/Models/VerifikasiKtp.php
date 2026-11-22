<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerifikasiKtp extends Model
{
    protected $table = 'verifikasi_ktp';

    protected $primaryKey = 'id_ktp';

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_akun', 'id_akun');
    }
}
