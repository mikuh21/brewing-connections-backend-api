<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->remove('X-Powered-By');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');
        $response->headers->set(
            'Strict-Transport-Security',
            'max-age=31536000; includeSubDomains'
        );

        // BrewHub currently uses inline Alpine/Blade scripts and trusted CDNs
        // for Alpine, Leaflet, QR code, Chart.js, jsPDF and the QR scanner.
        // Keep those dependencies explicitly allowlisted while blocking
        // object/plugin content and cross-origin framing.
        $response->headers->set(
            'Content-Security-Policy',
            implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "object-src 'none'",
                "script-src 'self' 'unsafe-inline' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: blob: https:",
                "connect-src 'self' https://api.mapbox.com https://maps.googleapis.com https://www.google-analytics.com https://language.googleapis.com https://bigquery.googleapis.com https://*.pusher.com wss://*.pusher.com https://*.supabase.co wss://*.supabase.co https://nominatim.openstreetmap.org",
                "media-src 'self' blob:",
                "worker-src 'self' blob:",
                "manifest-src 'self'",
                "upgrade-insecure-requests",
            ])
        );

        return $response;
    }
}
