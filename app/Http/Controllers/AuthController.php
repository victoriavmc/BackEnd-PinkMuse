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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\PasswordResetToken;


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

    //
    public function rules(Request $request, $isForgoten = false)
    {
        $rules = [
            'correo' => 'required|string|email|max:255',
        ];

        if (!$isForgoten) {
            $rules['password'] = 'required|string|min:8|max:255';
            $rules['token'] = 'required|string';
        }

        return $rules;
    }

    // Forgoten
    public function forgotten(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($request, true));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 'error'
            ], 422);
        }

        $validated = $validator->validated();
        $email = $validated['correo'];

        $user = Usuario::where('correo', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'No se encontró un usuario con ese correo electrónico',
                'status' => "error",
            ], 404);
        }

        $token = Str::random(64);

        PasswordResetToken::updateOrCreate(
            ['correo' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetUrl = "http://localhost:5173/reset-password?token=$token&email=" . urlencode($email);

        Mail::to($email)->send(new ResetPasswordMail($resetUrl));

        return response()->json([
            'message' => 'Se ha enviado un enlace para restablecer la contraseña',
            'status' => "success",
        ], 200);
    }

    // Reset
    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), $this->rules($request, false));

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 'error',
            ], 422);
        }

        $validated = $validator->validated();

        $record = PasswordResetToken::where('correo', $validated['correo'])->first();

        if (!$record || !Hash::check($validated['token'], $record->token)) {
            return response()->json([
                'message' => 'Token inválido o expirado',
                'status' => 'error',
            ], 400);
        }

        if (now()->diffInMinutes($record->created_at) > 60) {
            return response()->json([
                'message' => 'El token ha expirado',
                'status' => 'error',
            ], 400);
        }

        $user = Usuario::where('correo', $validated['correo'])->firstOrFail();
        $user->password = bcrypt($validated['password']);
        $user->save();

        PasswordResetToken::where('correo', $validated['correo'])->delete();

        return response()->json([
            'message' => 'Contraseña restablecida correctamente',
            'status' => 'success',
        ], 200);
    }
}
