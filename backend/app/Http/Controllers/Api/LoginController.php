<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function login(Request $request)
    { {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required'
            ]);

            try {
                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Usuario no encontrado'
                    ], 401);
                }

                if (!Hash::check($request->password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Contraseña incorrecta'
                    ], 401);
                }

                $roles = $user->roles()->pluck('key')->toArray();
                $schools = $user->schools()->pluck('schools.id', 'schools.name')->toArray();

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login exitoso',
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'roles' => $roles,
                        'schools' => $schools,
                        'primary_role' => count($roles) > 0 ? $roles[0] : 'user',
                        'primary_school_id' => count($schools) > 0 ? array_values($schools)[0] : null
                    ]
                ]);

            } catch (\Exception $e) {
                \Log::error('Error en login: ' . $e->getMessage());

                return response()->json([
                    'success' => false,
                    'message' => 'Error interno del servidor'
                ], 500);
            }
        }

    }
}
