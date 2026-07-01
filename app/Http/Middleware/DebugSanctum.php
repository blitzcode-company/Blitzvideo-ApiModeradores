<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DebugSanctum
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        $accessToken = $token ? \Laravel\Sanctum\PersonalAccessToken::findToken($token) : null;

        \Log::info('Middleware DebugSanctum', [
            'url' => $request->fullUrl(),
            'token' => $token,
            'token_found' => $accessToken ? true : false,
            'token_details' => $accessToken ? $accessToken->toArray() : null,
            'user' => $request->user() ? $request->user()->toArray() : null,
        ]);

        return app(EnsureFrontendRequestsAreStateful::class)->handle($request, $next);
    }
}
