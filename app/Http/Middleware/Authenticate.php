<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    public function handle($request, Closure $next)
    {
        $user = $request->session()->get('user_id')
            ? User::find($request->session()->get('user_id'))
            : null;

        if (!$user) {
            $request->session()->forget('user_id');
            return redirect('/login');
        }

        $request->attributes->set('auth_user', $user);
        return $next($request);
    }
}
