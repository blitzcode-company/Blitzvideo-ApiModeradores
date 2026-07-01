<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        if (app()->environment('local') && env('SIMULATE_LDAP_LOGIN', false)) {
            return $this->loginTestLDAP($request);
        }
    
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
            app()->environment('local') ? 'username' : 'samaccountname' => $request->username,
            'password' => $request->password,
        ];
    
        if (!Auth::guard('api')->attempt($credentials)) {
            return $this->handleFailedLogin($request);
        }
    
        $user = Auth::guard('api')->user();
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



    private function loginTestLDAP(Request $request)
    {
    
        $user = User::where('name', $request->username)->first();
    
        if ($user) {
            $token = $user->createToken('api-token')->plainTextToken;
            \Log::info('Token generado', ['user_id' => $user->id, 'token' => $token]);
    
            return response()->json([
                'message' => 'Login exitoso (simulated)',
                'user' => $user,
                'token' => $token,
            ]);
        }
    
        \Log::error('Usuario no encontrado', ['name' => $request->username]);
        return response()->json([
            'message' => 'Usuario no encontrado en el entorno de desarrollo.',
        ], 404);
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

        if (!session()->has('ldap_auth_error')) {
            return response()->json(['message' => 'Credenciales invalidas, verifique sus datos'], 401);
        }
        


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
