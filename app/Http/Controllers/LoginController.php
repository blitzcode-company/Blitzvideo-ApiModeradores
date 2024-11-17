<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Laravel\Auth\Guard as LdapAuth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $ldapAuth;

    public function __construct(LdapAuth $ldapAuth)
    {
        $this->ldapAuth = $ldapAuth;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Datos inválidos'], 400);
        }

        $credentials = [
            'samaccountname' => $request->username, 
            'password' => $request->password,
        ];

        if ($this->ldapAuth->attempt($credentials)) {
            $user = $this->ldapAuth->user();

            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Inicio de sesión exitoso.',
                'token' => $token,
            ], 200);
        }

        throw ValidationException::withMessages([
            'username' => ['Las credenciales no son válidas o el usuario no pertenece a la unidad organizativa permitida.'],
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Cierre de sesión exitoso.'], 200);
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $this->sendLockoutResponse($request);
        }
    }

    protected function handleFailedLogin(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => trans('auth.failed'),
        ]);
    }

    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('username')) . '|' . $request->ip();
    }

    protected function sendLockoutResponse(Request $request): void
    {
        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }
}
