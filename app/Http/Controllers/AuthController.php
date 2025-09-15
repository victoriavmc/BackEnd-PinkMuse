<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Rol;
use App\Models\Usuario;
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
            'rol_id' => 'sometimes|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        if (Usuario::where('correo', $request->correo)->exists()) {
            return $this->error('El correo ya existe', 409);
        }

        if (Usuario::where('perfil.username', $request->input('perfil.username'))->exists()) {
            return $this->error('El username ya existe', 409);
        }

        $rol_id = $request->rol_id ?? '68c435957004b39bc425dfce'; // Rol por defecto "Usuario"
        $rol = Rol::find($rol_id);
        if (!$rol) {
            return $this->error('El rol no existe', 404);
        }

        //
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
        $usuario->preferenciaNotificacion = [];

        $usuario->rol_id = $rol_id;
        $usuario->estado = 'Activo';
        $usuario->save();

        if (!$usuario) {
            return $this->error('Error al crear el usuario', 500);
        }

        return $this->success($usuario, 'Usuario creado correctamente', 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'correo.required' => 'El campo correo es obligatorio.',
            'correo.email' => 'El correo debe ser una dirección de email válida.',
            'password.required' => 'El campo contraseña es obligatorio.',
            'password.string' => 'La contraseña debe ser texto.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        $user = Usuario::where('correo', $credentials['correo'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'correo' => ['No existe ningun usuario con estas credenciales.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function cerrarsesion(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();

        if (!$currentToken) {
            return response()->json(['message' => 'Token no válido'], 401);
        }

        $request->user()->revokeToken($currentToken->id);

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}
