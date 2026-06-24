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

        if ($request->is('admin/login') || $request->is('admin/login/*')) {
            return $next($request);
        }

        if ($request->isMethod('POST') && $request->path() === 'admin/login') {
            if ($request->input('password') === $password) {
                $request->session()->put('admin_authenticated', true);
                return redirect()->intended(route('virtual-hosts.index'));
            }

            return back()->with('error', 'Senha incorreta.');
        }

        return redirect()->route('admin.login');
    }
}
