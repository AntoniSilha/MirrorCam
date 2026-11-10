<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /**
     * Akun mitra tidak boleh mengakses Dashboard Customer.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->role === 'mitra') {
            return redirect()->route('mitra.dashboard')
                ->with('status', 'Sebagai mitra, Anda diarahkan ke Dashboard Mitra.');
        }

        return $next($request);
    }
}
