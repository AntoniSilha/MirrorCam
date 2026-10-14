<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\IdentityAndAccess\UseCases\ChangePasswordUseCase;
use App\Portals\Customer\Requests\SetLocalPasswordRequest;
use App\Portals\Customer\Resources\UserProfileResource;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerSecurityController extends Controller
{
    /**
     * Show the reset/set password form.
     */
    public function edit(Request $request): View
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $user = User::first() ?? new User([
                'name' => 'Antoni Silha',
                'email' => 'antoni@example.com',
            ]);
        }

        $isGoogleAccount = ! empty($user->google_id);
        $hasPassword = ! empty($user->password);
        $userProfile = (new UserProfileResource($user))->resolve();

        return view('customer.security.reset-password', [
            'user' => $userProfile,
            'isGoogleAccount' => $isGoogleAccount,
            'hasPassword' => $hasPassword,
        ]);
    }

    /**
     * Update customer password.
     */
    public function update(SetLocalPasswordRequest $request, ChangePasswordUseCase $useCase): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            $user = User::first();
        }

        if (! $user) {
            return back()->withErrors(['general' => 'User tidak ditemukan.']);
        }

        $useCase->execute(
            $user,
            $request->input('current_password'),
            $request->input('password')
        );

        return back()->with('status', 'Kata sandi akun Anda berhasil diperbarui.');
    }
}
