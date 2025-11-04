<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Prevent information disclosure
        $response->headers->set('X-Powered-By', 'MLUC Sentinel');

        // Referrer policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (adjust based on your needs)
        if (config('app.env') === 'production') {
            $csp = "default-src 'self'; "
                ."script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; "
                ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
                ."font-src 'self' https://fonts.gstatic.com; "
                ."img-src 'self' data: https:; "
                ."connect-src 'self' ws: wss:; "
                ."frame-ancestors 'self';";

            $response->headers->set('Content-Security-Policy', $csp);
        }

        // Strict Transport Security (HTTPS only)
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Permissions Policy (formerly Feature Policy)
        // Allow camera and microphone for license capture, QR scanning, and patrol features
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(self), microphone=(self)');

        return $response;
    }
}
