<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMitra
{
    /**
     * Hanya akun mitra (dan superadmin) yang boleh mengakses Dashboard Mitra.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, ['mitra', 'superadmin'], true)) {
            return redirect()->route('register.mitra')
                ->with('status', 'Daftarkan toko Anda terlebih dahulu untuk mengakses Dashboard Mitra.');
        }

        return $next($request);
    }
}
