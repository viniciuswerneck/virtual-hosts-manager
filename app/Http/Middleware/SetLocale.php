<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($locale = $request->session()->get('locale')) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
