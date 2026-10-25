<?php

namespace App\Portals\Customer\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\IdentityAndAccess\Services\GoogleAuthService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth Provider.
     */
    public function redirectToGoogle(GoogleAuthService $service): SymfonyRedirectResponse
    {
        return $service->redirectToGoogle();
    }

    /**
     * Handle Google OAuth Callback.
     */
    public function handleGoogleCallback(GoogleAuthService $service): RedirectResponse
    {
        try {
            $user = $service->handleCallback();

            return redirect()->route('customer.orders.index')
                ->with('status', 'Berhasil masuk dengan akun Google: '.$user->name);
        } catch (Exception $e) {
            return redirect()->route('customer.security.reset-password')
                ->withErrors(['google' => 'Gagal melakukan otorisasi dengan akun Google. Silakan coba lagi.']);
        }
    }
}
