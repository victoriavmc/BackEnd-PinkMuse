<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;

class UsuarioController
{
    use ApiResponse;
    public $usuario;

    protected NotificationService $notificationService;
    protected ImageService $imageService;

    public function __construct(NotificationService $notificationService, ImageService $imageService)
    {
        $this->usuario = Auth::user();
        $this->notificationService = $notificationService;
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // SIN OPTIMIZAR, PERO PORQUE YA ESTA EN AUTHCONTROLLER
        return $this->error('No implementado. Use el endpoint de registro.', 501);
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
        $usuario->refresh();
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
            'perfil.imagenPrincipal' => 'nullable',
            'preferenciaNotificacion' => 'nullable|array',
            'rol_id' => 'sometimes|required|string',
            'estado' => 'sometimes|required|string|in:activo,inactivo',
        ]);

        $validator->sometimes('perfil.imagenPrincipal', 'image|mimes:jpeg,png,jpg,webp|max:2048', function () use ($request) {
            return $request->hasFile('perfil.imagenPrincipal');
        });

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
            $usuario->rol = $rol->rol;
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

        $perfilInput = $request->input('perfil', []);
        if (!is_array($perfilInput)) {
            $perfilInput = [];
        }

        $perfil = is_array($usuario->perfil) ? $usuario->perfil : [];
        foreach ($perfilInput as $clave => $valor) {
            if ($clave === 'imagenPrincipal') {
                continue;
            }
            $perfil[$clave] = $valor;
        }

        $imagenActual = $perfil['imagenPrincipal'] ?? null;
        if ($request->hasFile('perfil.imagenPrincipal')) {
            // 1. Eliminar la imagen anterior (si existe)
            if ($imagenActual) {
                $this->imageService->eliminar($imagenActual);
            }

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
        } elseif (array_key_exists('imagenPrincipal', $perfilInput)) {
            $imagenSanitizada = $this->sanitizeImagenEntrada($perfilInput['imagenPrincipal']);

            if ($imagenSanitizada === null) {
                if ($imagenActual) {
                    $this->imageService->eliminar($imagenActual);
                    unset($perfil['imagenPrincipal']);
                }
            } else {
                if (!$this->imagenesSonIguales($imagenSanitizada, $imagenActual) && $imagenActual) {
                    $this->imageService->eliminar($imagenActual);
                }
                $perfil['imagenPrincipal'] = $imagenSanitizada;
            }

            $usuario->perfil = $perfil;
        } else {
            $usuario->perfil = $perfil;
        }

        $usuario->save();

        if (!$usuario) {
            return $this->error('Error al actualizar el usuario', 500);
        }

        $usuario->refresh();
        return $this->success($usuario, 'Usuario actualizado exitosamente', 200);
    }

    private function sanitizeImagenEntrada($valor)
    {
        if (is_null($valor)) {
            return null;
        }

        if (is_string($valor)) {
            $trimmed = trim($valor);
            return $trimmed !== '' ? $trimmed : null;
        }

        if (is_array($valor)) {
            $keys = array_keys($valor);
            $isList = empty($valor) ? true : $keys === range(0, count($valor) - 1);

            if ($isList) {
                foreach ($valor as $item) {
                    $sanitizado = $this->sanitizeImagenEntrada($item);
                    if ($sanitizado !== null) {
                        return $sanitizado;
                    }
                }
                return null;
            }

            $permitidos = array_intersect_key($valor, array_flip(['png', 'webp', 'path', 'principal']));
            return !empty($permitidos) ? $permitidos : null;
        }

        return null;
    }

    private function imagenesSonIguales($a, $b): bool
    {
        if ($a === $b) {
            return true;
        }

        if (is_string($a) && is_string($b)) {
            return trim($a) === trim($b);
        }

        if (is_array($a) && is_array($b)) {
            return ($a['webp'] ?? null) === ($b['webp'] ?? null)
                && ($a['png'] ?? null) === ($b['png'] ?? null);
        }

        return false;
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
