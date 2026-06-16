<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use voku\helper\HtmlMin;

class MinifyHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (
            app()->environment('production') &&
            str_contains((string) $response->headers->get('Content-Type'), 'text/html')
        ) {
            $htmlMin = new HtmlMin();

            // Disable DOM-based optimization that rewrites tags/attributes
            $htmlMin->doOptimizeViaHtmlDomParser(false);

            $response->setContent(
                $htmlMin->minify($response->getContent())
            );
        }

        return $response;
    }
}