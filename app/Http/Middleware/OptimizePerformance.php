<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizePerformance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply optimizations to HTML responses
        if (!$this->isHtmlResponse($response)) {
            return $response;
        }

        // Add performance headers
        $this->addPerformanceHeaders($response);

        // Add security headers
        $this->addSecurityHeaders($response);

        // Optimize HTML content
        if (config('app.env') === 'production') {
            $this->optimizeHtmlContent($response);
        }

        return $response;
    }

    /**
     * Check if response is HTML
     */
    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }

    /**
     * Add performance-related headers
     */
    private function addPerformanceHeaders(Response $response): void
    {
        $headers = [
            // Cache control for static assets
            'Cache-Control' => 'public, max-age=31536000, immutable',
            // 'Cache-Control' => 'no-cache, private',

            // Compression
            'Vary' => 'Accept-Encoding',

            // Performance hints
            'X-DNS-Prefetch-Control' => 'on',
        ];

        foreach ($headers as $key => $value) {
            if (!$response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }
    }

    /**
     * Add security headers for performance
     */
    private function addSecurityHeaders(Response $response): void
    {
        $headers = [
            // Security headers that also improve performance
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            // HSTS for HTTPS performance
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        ];

        foreach ($headers as $key => $value) {
            if (!$response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }
    }

    /**
     * Optimize HTML content for production
     */

    private function optimizeHtmlContent(Response $response): void
    {
        $content = $response->getContent();

        if (!is_string($content)) {
            return;
        }

        $result = preg_replace('/>\s+</', '><', $content);
        if ($result !== null) {
            $content = $result;
        }

        $result = preg_replace(
            '/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s',
            '',
            $content
        );

        if ($result !== null) {
            $content = $result;
        }

        $content = $this->optimizeInlineAssets($content);

        $response->setContent($content);
    }

    /**
     * Optimize inline CSS and JavaScript
     */
    private function optimizeInlineAssets(string $content): string
    {
        // Minify inline CSS
        $content = preg_replace_callback(
            '/<style[^>]*>(.*?)<\/style>/is',
            function ($matches) {
                $css = $matches[1];
                // Remove comments
                $css = preg_replace('/\/\*.*?\*\//s', '', $css);
                // Remove unnecessary whitespace
                $css = preg_replace('/\s+/', ' ', $css);
                $css = str_replace(['; ', ' {', '{ ', ' }', '} '], [';', '{', '{', '}', '}'], $css);
                return '<style' . substr($matches[0], 6, strpos($matches[0], '>') - 6) . '>' . trim($css) . '</style>';
            },
            $content
        );

        // Minify inline JavaScript
        $content = preg_replace_callback(
            '/<script([^>]*)>(.*?)<\/script>/is',
            function ($matches) {
                $attributes = $matches[1];
                $js = $matches[2];

                // Only minify actual JavaScript. Scripts with a non-JS type
                // (application/ld+json, importmap, text/template, etc.) must be
                // left untouched — otherwise the "//" comment stripper below
                // will corrupt things like JSON-LD values containing URLs
                // (e.g. "https://schema.org" becomes "https:").
                if ($this->hasNonJavaScriptType($attributes)) {
                    return '<script' . $attributes . '>' . $js . '</script>';
                }

                // Basic JS minification (remove comments and extra whitespace)
                $js = preg_replace('/\/\*.*?\*\//s', '', $js);
                // Negative lookbehind for ":" avoids matching the "//" in
                // protocol-relative / absolute URLs (e.g. "https://...").
                $js = preg_replace('#(?<!:)//.*$#m', '', $js);
                $js = preg_replace('/\s+/', ' ', $js);
                return '<script' . $attributes . '>' . trim($js) . '</script>';
            },
            $content
        );

        return $content;
    }

    /**
     * Determine whether a <script> tag's attribute string declares a type
     * that is not executable JavaScript (e.g. application/ld+json,
     * application/json, importmap, text/template). Scripts like these must
     * never be run through the JS comment-stripping minifier.
     */
    private function hasNonJavaScriptType(string $attributes): bool
    {
        if (!preg_match('/\btype\s*=\s*(["\'])(.*?)\1/i', $attributes, $match)) {
            // No type attribute at all defaults to JavaScript.
            return false;
        }

        $type = strtolower(trim($match[2]));

        $javascriptTypes = [
            '',
            'text/javascript',
            'application/javascript',
            'application/ecmascript',
            'application/x-javascript',
            'module',
        ];

        return !in_array($type, $javascriptTypes, true);
    }
}