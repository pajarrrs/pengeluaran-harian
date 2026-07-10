<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessCode
{
    const VALID_CODES = ['230205', '200705'];

    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('access_code')) {
            return $next($request);
        }
        return response()->view('auth.login');
    }

    public static function isValid(string $code): bool
    {
        return in_array($code, self::VALID_CODES);
    }
}
