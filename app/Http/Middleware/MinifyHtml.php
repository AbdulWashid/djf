<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use voku\helper\HtmlMin;

class MinifyHtml
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (config('app.env') === 'production') {
            if (str_contains($response->headers->get('Content-Type'), 'text/html')) {
                $htmlMin = new HtmlMin();
                $response->setContent(
                    $htmlMin->minify($response->getContent())
                );
            }
        }

        return $response;
    }
}
