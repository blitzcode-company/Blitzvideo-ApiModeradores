<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;
use LdapRecord\Laravel\Auth\Guard;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'samaccountname' => $request->username,
            'password' => $request->password,
        ];

        if (Auth::guard('ldap')->attempt($credentials)) {
            $user = Auth::guard('ldap')->user();

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

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Cierre de sesión exitoso.',
        ]);
    }

    public function obtenerDatosUser(Request $request)
    {
        if (Auth::guard('ldap')->check()) {
            $user = Auth::guard('ldap')->user();

            return response()->json([
                'message' => 'Datos del usuario obtenidos exitosamente.',
                'user' => $user,
            ], 200);
        }

        return response()->json([
            'message' => 'Usuario no autenticado.',
        ], 401);
    }
}
