<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCustomer
{
    /** User dan mitra boleh memakai area dashboard pelanggan. */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (in_array(strtolower($user->role ?? ''), ['user', 'mitra'], true)) {
            return $next($request);
        }

        if (strtolower($user->role ?? '') === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        abort(403, 'Akses dashboard pelanggan tidak diizinkan untuk role ini.');
    }
}
