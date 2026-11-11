<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_akun';

    protected $guarded = [];

    protected $hidden = ['remember_token'];

    public function initials(): string
    {
        $name = $this->nama_lengkap_ktp ?? $this->name ?? 'User';
        $words = array_values(array_filter(explode(' ', trim($name))));

        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1).substr($words[1], 0, 1));
        }

        return strtoupper(substr($name, 0, 2));
    }

    public function accountGoogle()
    {
        return $this->belongsTo(AccountGoogle::class, 'id_user_google', 'id_user_google');
    }

    public function pengaduan()
    {
        return $this->hasMany(PengaduanPelanggan::class, 'id_akun', 'id_akun');
    }

    public function getNameAttribute(): string
    {
        return $this->nama_lengkap_ktp ?? 'User';
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['email'] ?? $this->accountGoogle?->email ?? null;
    }

    public function getInitialsAttribute(): string
    {
        return $this->initials();
    }
}
