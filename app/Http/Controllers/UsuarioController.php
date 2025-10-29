<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use App\Services\ImageService; // Importar el servicio

class UsuarioController
{
    /**
     * Display a listing of the resource.
     */
    use ApiResponse;

    // --- INYECCIÓN DE DEPENDENCIAS ---
    private ImageService $imageService;

    /**
     * Inyectar el ImageService en el constructor.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function index()
    {
        $usuarios = Usuario::all();
        if ($usuarios->isEmpty()) {
            return $this->error('No se encontraron usuarios', 404);
        }
        return $this->success($usuarios, 'Usuarios obtenidos exitosamente', 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $username)
    {
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
        $usuario = Usuario::where('perfil.username', $username)->first();
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'nacionalidad' => 'sometimes|required|string|max:255',
            'fechaNacimiento' => 'sometimes|required|date',
            'correo' => 'prohibited',
            'password' => 'sometimes|required|string|min:8',
            'perfil' => 'sometimes|array',
            'perfil.username' => 'prohibited',
            'perfil.imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'preferenciaNotificacion' => 'nullable|array',
            'rol_id' => 'sometimes|required|string',
            'estado' => 'sometimes|required|string|in:activo,inactivo,teta',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }
        
        $data = $validator->validated();

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

        $perfil = $usuario->perfil ?? [];
        if ($request->hasFile('perfil.imagenPrincipal')) {
            // 1. Eliminar la imagen anterior (si existe)
            $oldImage = $perfil['imagenPrincipal'] ?? null;
            $this->imageService->eliminar($oldImage);

            // 2. Guardar la nueva imagen
            $rutas = $this->imageService->guardar(
                $request->file('perfil.imagenPrincipal'),
                'usuario',
                $username,
                false,
                0
            );
            
            // 3. Asignar la nueva ruta
            $perfil['imagenPrincipal'] = $rutas[0];
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
        $usuario = Usuario::where('perfil.username', $username)->first();
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        try {
            // 1. Eliminar la imagen de perfil (si existe)
            $oldImage = $usuario->perfil['imagenPrincipal'] ?? null;
            $this->imageService->eliminar($oldImage);
            
            // 2. Borramos el usuario
            $usuario->delete();
            
            return $this->success(null, 'Usuario eliminado exitosamente', 204);

        } catch (\Exception $e) {
            return $this->error('Error al eliminar el usuario', 500, $e->getMessage());
        }
    }
}