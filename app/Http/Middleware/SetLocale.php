<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get("locale", "pl");

        if (in_array($locale, ["pl", "en"], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
