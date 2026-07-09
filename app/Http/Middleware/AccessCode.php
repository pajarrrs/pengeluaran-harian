<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session('access_granted')) {
            return $next($request);
        }

        return response()->view('auth.login');
    }
}
