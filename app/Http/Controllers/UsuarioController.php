<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class UsuarioController
{
    /**
     * Display a listing of the resource.
     */
    use ApiResponse;

    public function index()
    {
        //
        $usuarios = Usuario::all();
        if ($usuarios->isEmpty()) {
            return $this->error('No se encontraron usuarios', 404);
        }
        return $this->success($usuarios, 'Usuarios obtenidos exitosamente', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // SIN OPTIMIZAR, PERO PORQUE YA ESTA EN AUTHCONTROLLER
        return $this->error('No implementado. Use el endpoint de registro.', 501);
        // $validator = Validator::make($request->all(), [
        //     'nombre' => 'required|string|max:255',
        //     'apellido' => 'required|string|max:255',
        //     'nacionalidad' => 'required|string|max:255',
        //     'fechaNacimiento' => 'required|date',
        //     'correo' => 'required|string|email|max:255|unique:usuarios,email',
        //     'password' => 'required|string|min:8',
        //     'perfil' => 'required|array',
        //     'perfil.username' => 'required|string|max:255',
        //     'perfil.imagenPrincipal' => 'nullable|string|max:500',
        //     'preferenciaNotificacion' => 'nullable|array',
        //     'rol_id' => 'required|string',
        //     'estado' => 'required|string|in:activo,inactivo',
        // ]);

        // if ($validator->fails()) {
        //     return response()->json([
        //         'message' => 'Error de validación',
        //         'errors' => $validator->errors(),
        //         'status' => 400
        //     ], 400);
        // }

        // if (Usuario::where('correo', $request->correo)->exists()) {
        //     return response()->json([
        //         'message' => 'El correo ya existe',
        //         'status' => 409
        //     ], 409);
        // }

        // if (Usuario::where('perfil.username', $request->input('perfil.username'))->exists()) {
        //     return response()->json([
        //         'message' => 'El username ya existe',
        //         'status' => 409
        //     ], 409);
        // }

        // // Asumiendo que rol_id es string
        // $rol = Rol::find($request->rol_id); // find automáticamente convierte a ObjectId
        // if (!$rol) {
        //     return response()->json([
        //         'message' => 'El rol no existe',
        //         'status' => 404
        //     ], 404);
        // }

        // //
        // $usuario = new Usuario();
        // $usuario->nombre = $request->nombre;
        // $usuario->apellido = $request->apellido;
        // $usuario->nacionalidad = $request->nacionalidad;
        // $usuario->fechaNacimiento = $request->fechaNacimiento;
        // $usuario->correo = $request->correo;
        // $usuario->password = bcrypt($request->password); // Encriptar la contraseña
        // $usuario->perfil = [
        //     'username' => $request->input('perfil.username'),
        //     'imagenPrincipal' => $request->input('perfil.imagenPrincipal') ?? null
        // ];
        // $usuario->preferenciaNotificacion = $request->preferenciaNotificacion ?? [];
        // $usuario->rol_id = $request->rol_id;
        // $usuario->estado = $request->estado;
        // $usuario->save();

        // if (!$usuario) {
        //     return response()->json([
        //         'message' => 'Error al crear el usuario',
        //         'status' => 500
        //     ], 500);
        // }
        // return response()->json([
        //     'message' => 'Usuario creado exitosamente',
        //     'usuario' => $usuario,
        //     'status' => 201
        // ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username)
    {
        //
        $usuario = Usuario::where('perfil.username', $username)->first();
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }
        return $this->success($usuario, 'Usuario obtenido exitosamente', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $username)
    {
        //
        $usuario = Usuario::where('perfil.username', $username)->first();
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        // Validación
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'nacionalidad' => 'sometimes|required|string|max:255',
            'fechaNacimiento' => 'sometimes|required|date',
            'correo' => 'prohibited',
            'password' => 'sometimes|required|string|min:8',
            'perfil' => 'sometimes|required|array',
            'perfil.username' => 'prohibited',
            'perfil.imagenPrincipal' => 'nullable|string|max:500',
            'preferenciaNotificacion' => 'nullable|array',
            'rol_id' => 'sometimes|required|string',
            'estado' => 'sometimes|required|string|in:activo,inactivo,teta',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Actualizar rol si se envía
        if ($request->filled('rol_id')) {
            $rol = Rol::find($request->rol_id);
            if (!$rol) {
                return $this->error('El rol no existe', 404);
            }
            $usuario->rol_id = $request->rol_id;
        }

        // Actualizar campos simples si se envían
        foreach (['nombre', 'apellido', 'nacionalidad', 'fechaNacimiento', 'estado'] as $field) {
            if ($request->has($field)) {
                $usuario->{$field} = $request->{$field};
            }
        }

        // Actualizar password si se envía
        if ($request->filled('password')) {
            $usuario->password = bcrypt($request->password);
        }

        // Actualizar perfil (solo imagenPrincipal)
        if ($request->has('perfil') && is_array($request->perfil)) {
            $perfil = $usuario->perfil ?? [];

            // Solo actualizar imagenPrincipal si se envía
            if (isset($request->perfil['imagenPrincipal'])) {
                $perfil['imagenPrincipal'] = $request->perfil['imagenPrincipal'];
            }

            $usuario->perfil = $perfil;
        }

        // Actualizar preferencias de notificación
        if ($request->has('preferenciaNotificacion')) {
            $usuario->preferenciaNotificacion = $request->preferenciaNotificacion;
        }

        $usuario->save();

        if (!$usuario) {
            return $this->error('Error al actualizar el usuario', 500);
        }

        return $this->success($usuario, 'Usuario actualizado exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $username)
    {
        //
        $usuario = Usuario::where('perfil.username', $username)->first();
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }
        // Borramos o Modificamos y solo anulamos el correo con su username?
        $usuario->delete();
        return $this->success(null, 'Usuario eliminado exitosamente', 200);
    }
}
