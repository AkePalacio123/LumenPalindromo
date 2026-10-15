<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;

class EnsureAdministrator
{
    public function handle($request, Closure $next)
    {
        $user = User::find($request->session()->get('user_id'));
        if (!$user || $user->role !== 'Administrador') {
            return redirect('/dashboard');
        }

        $request->attributes->set('auth_user', $user);
        return $next($request);
    }
}
