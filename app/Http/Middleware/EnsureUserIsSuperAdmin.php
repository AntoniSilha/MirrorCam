<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSuperAdmin
{
    /**
     * Hanya akun superadmin (Pengelola Platform MirrorCam) yang boleh mengakses Dashboard SuperAdmin.
     * Sesuai SRS UC-19 & NFR-SEC-04.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (strtolower($user->role ?? '') !== 'superadmin') {
            abort(403, 'Akses terbatas hanya untuk SuperAdmin (Pengelola Platform MirrorCam).');
        }

        return $next($request);
    }
}
