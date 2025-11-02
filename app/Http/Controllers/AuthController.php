<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class AuthController
{
    use ApiResponse;

    public function registro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'nacionalidad' => 'required|string|max:255',
            'fechaNacimiento' => 'required|date',
            'correo' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'perfil.username' => 'required|string|max:255|unique:usuarios,perfil->username',
            'perfil.imagenPrincipal' => 'nullable|url',
            'rol_id' => 'nullable|exists:rols,_id',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validaci�n', 400, $validator->errors());
        }

        if (Usuario::where('correo', $request->correo)->exists()) {
            return $this->error('El correo ya existe', 409);
        }

        if (Usuario::where('perfil.username', $request->input('perfil.username'))->exists()) {
            return $this->error('El username ya existe', 409);
        }

        $rol_id = $request->filled('rol_id') && !empty($request->input('rol_id'))
        ? $request->input('rol_id')
        : '6907bc2212642a84a100afc3';

        $rol = Rol::find($rol_id);
        if (!$rol) {
            return $this->error('El rol no existe', 404);
        }

        $usuario = new Usuario();
        $usuario->nombre = $request->nombre;
        $usuario->apellido = $request->apellido;
        $usuario->nacionalidad = $request->nacionalidad;
        $usuario->fechaNacimiento = $request->fechaNacimiento;
        $usuario->correo = $request->correo;
        $usuario->password = bcrypt($request->password);
        $usuario->perfil = [
            'username' => $request->input('perfil.username'),
            'imagenPrincipal' => $request->input('perfil.imagenPrincipal') ?? null
        ];
        $usuario->rol_id = $rol_id;
        $usuario->rol = $rol->rol;
        $usuario->estado = 'Activo';
        $usuario->save();

        if (!$usuario) {
            return $this->error('Error al crear el usuario', 500);
        }

        return $this->success($usuario->fresh(), 'Usuario creado correctamente', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'El correo debe ser una direcci�n de email v�lida.',
            'password.required' => 'El campo contrase�a es obligatorio.',
            'password.string' => 'La contrase�a debe ser texto.',
            'password.min' => 'La contrase�a debe tener al menos 6 caracteres.',
        ]);

        $user = Usuario::where('correo', $credentials['correo'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'correo' => ['No existe ningun usuario con estas credenciales.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->fresh(),
            'token' => $token,
        ]);
    }

    public function cerrarsesion(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();

        if (!$currentToken) {
            return response()->json(['message' => 'Token no v�lido'], 401);
        }

        $request->user()->revokeToken($currentToken->id);

        return response()->json(['message' => 'Sesi�n cerrada correctamente']);
    }
}