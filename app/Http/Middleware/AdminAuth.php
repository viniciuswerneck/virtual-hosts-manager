<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $password = config('app.admin_password');

        if (empty($password)) {
            return $next($request);
        }

        if ($request->session()->get('admin_authenticated') === true) {
            return $next($request);
        }

        return redirect()->route('admin.login');
    }
}
