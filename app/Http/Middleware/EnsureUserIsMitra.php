<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMitra
{
    /** Hanya akun mitra yang boleh mengakses dashboard mitra. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $role = strtolower($user->role ?? '');

        if ($role === 'mitra') {
            return $next($request);
        }

        if ($role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        return redirect()->route('register.mitra')
            ->with('status', 'Daftarkan toko Anda terlebih dahulu untuk mengakses Dashboard Mitra.');
    }
}
