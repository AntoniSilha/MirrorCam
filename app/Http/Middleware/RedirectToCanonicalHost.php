<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToCanonicalHost
{
    /**
     * Samakan host lokal (localhost/127.0.0.1) agar cookie sesi tidak
     * terpisah dan pengguna tidak terlihat logout saat membuka tab baru.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->isLocal() || ! $request->isMethod('GET')) {
            return $next($request);
        }

        $canonicalUrl = rtrim((string) config('app.url'), '/');
        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);
        $currentHost = $request->getHost();

        $localHosts = ['localhost', '127.0.0.1'];

        if (
            $canonicalHost
            && in_array($currentHost, $localHosts, true)
            && $currentHost !== $canonicalHost
        ) {
            return redirect()->to($canonicalUrl.$request->getRequestUri());
        }

        return $next($request);
    }
}
