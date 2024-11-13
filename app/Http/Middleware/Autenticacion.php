<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class Autenticacion
{

    public function handle(Request $request, Closure $next)
    {

        $tokenHeader = ["Authorization" => $request->header("Authorization")];
    
        $authApiUrl = config('auth.api_url');
    
        $response = Http::withHeaders($tokenHeader)->get($authApiUrl);
    
        if ($response->successful()) {
            return $next($request);
        }
    
        return response(['message' => 'Not Allowed'], 403);
    }
}

