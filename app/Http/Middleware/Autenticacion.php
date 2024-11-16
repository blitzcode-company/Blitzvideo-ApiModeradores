<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class Autenticacion
{

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return $next($request);
        }

        $username = $request->input('username');
        $password = $request->input('password');

        $ldapUser = Ldap::connect()->bind($username, $password);

        if ($ldapUser) {
            Auth::loginUsingId($username);
            return $next($request);
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}

