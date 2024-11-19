<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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
                'message' => 'Error de validacion',
                'errors' => $validator->errors(),
            ], 422);
        }

        $this->ensureIsNotRateLimited($request);

        $credentials = [
            'samaccountname' => $request->username,
            'password' => $request->password,
        ];

        if (!Auth::attempt($credentials)) {
            return $this->handleFailedLogin($request);
        }

        $user = Auth::user();
        $token = $user->createToken('API Token')->plainTextToken;

        RateLimiter::clear($this->throttleKey($request));

        return response()->json([
            'message' => 'Login exitoso',
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function obtenerDatosUser(Request $request) {
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
        $user = User::where('username', $request->username)->first();

        if ($user) {
            $token = $user->createToken('API Token')->plainTextToken;

            return response()->json([
                'message' => 'Login exitoso (simulated)',
                'user' => $user,
                'token' => $token,
            ]);
        }

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
