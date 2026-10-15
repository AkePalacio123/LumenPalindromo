<?php

namespace App\Http\Middleware;

use Closure;

class VerifyCsrfToken
{
    public function handle($request, Closure $next)
    {
        if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'], true)) {
            $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
            $sessionToken = $request->session()->token();

            if (!$token || !hash_equals($sessionToken, $token)) {
                return response('La sesión venció. Recarga la página e inténtalo de nuevo.', 419);
            }
        }

        return $next($request);
    }
}
