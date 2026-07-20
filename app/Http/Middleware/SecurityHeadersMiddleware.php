<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach HTTP Security Headers including CSP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        // Define Content Security Policy (CSP) directives
        $cspDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $cspHeaderValue = implode('; ', $cspDirectives);

        // Set Security Headers
        $response->headers->set('Content-Security-Policy', $cspHeaderValue);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // Remove information disclosure headers (X-Powered-By, Server)
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        // Sanitize redirect response body to prevent Big Redirect / Data Leakage
        if ($response->isRedirection()) {
            $location = htmlspecialchars((string) $response->headers->get('Location', ''), ENT_QUOTES, 'UTF-8');
            $minimalBody = sprintf(
                '<!DOCTYPE html><html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=%s" /><title>Redirecting</title></head><body>Redirecting to <a href="%s">Redirect</a>.</body></html>',
                $location,
                $location
            );
            $response->setContent($minimalBody);
        }

        return $response;
    }
}
