<?php

namespace App\Modules\IdentityAndAccess\UseCases;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordUseCase
{
    /**
     * Execute password change or set local password.
     *
     *
     * @throws ValidationException
     */
    public function execute(User $user, ?string $currentPassword, string $newPassword): bool
    {
        // Cek apakah akun murni Google (google_id != null & password == null)
        $isPureGoogleAccount = ! empty($user->google_id) && empty($user->password);

        // Jika punya password lama, lakukan verifikasi Hash::check
        if (! $isPureGoogleAccount && ! empty($user->password)) {
            if (empty($currentPassword) || ! Hash::check($currentPassword, $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => ['Kata sandi saat ini tidak sesuai.'],
                ]);
            }
        }

        // Enkripsi password baru dengan Hash::make
        $user->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        return true;
    }
}
