<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 422);
        }
    
        $this->ensureIsNotRateLimited($request);
    
        $credentials = [
            'samaccountname' => $request->username,
            'password' => $request->password,
        ];
    
        try {
            if (!Auth::guard('web')->attempt($credentials)) {
                return $this->handleFailedLogin($request);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error crítico interno del servidor al procesar el login.',
                'error' => $e->getMessage()
            ], 500);
        }
    
        $user = Auth::guard('web')->user();
        $token = $user->createToken('api-token')->plainTextToken;
    
        RateLimiter::clear($this->throttleKey($request));
    
        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function obtenerDatosUser(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Usuario no autenticado'], 401);
        }

        return response()->json([
            'message' => 'Usuario autenticado',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso',
        ]);
    }

    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            $this->sendLockoutResponse($request);
        }
    }

    protected function handleFailedLogin(Request $request): JsonResponse
    {
        RateLimiter::hit($this->throttleKey($request));

        if (session('ldap_auth_error') === 'rule_failed') {
            return response()->json(['message' => 'El usuario no pertenece al grupo Moderadores'], 405);
        }

        return response()->json(['message' => 'Credenciales invalidas, verifique sus datos'], 401);
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