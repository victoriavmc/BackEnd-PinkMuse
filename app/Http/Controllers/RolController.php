<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class RolController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Listo todos los roles
        $roles = Rol::all();

        if ($roles->isEmpty()) {
            return $this->error('No se encontraron roles', 404);
        }
        return $this->success($roles, 'Roles obtenidos exitosamente', 200);
    }


    // VALIDATOR
    public function validateRol(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
            $rules = [
                'rol' => 'prohibited',
                'permisos' => 'sometimes|required|array',
                'permisos.*.modulo' => 'prohibited',
                'permisos.*.acciones' => 'required_with:permisos|array|min:1',
                'permisos.*.acciones.*' => 'required_with:permisos|string|max:255',
            ];
        } else {
            $rules = [
                'rol' => 'required|string|max:255|unique:roles,rol',
                'permisos' => 'required|array',
                'permisos.*.modulo' => 'required|string|max:255', // nombre del módulo
                'permisos.*.acciones' => 'required|array', // acciones permitidas en el módulo
                'permisos.*.acciones.*' => 'required|string|max:255', // nombre de la acción
            ];
        }
        return Validator::make($request->all(), $rules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar datos de entrada}
        $validator = $this->validateRol($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        if (Rol::where('rol', $request->rol)->exists()) {
            return $this->error('El nombre del rol ya existe', 400);
        }

        // Validar módulos únicos
        $modulos = array_column($request->permisos, 'modulo');
        if (count($modulos) !== count(array_unique($modulos))) {
            return $this->error('Los módulos deben ser únicos dentro de un mismo rol', 400);
        }

        // Eliminar acciones repetidas dentro de cada módulo
        foreach ($request->permisos as &$permiso) {
            $permiso['acciones'] = array_values(array_unique($permiso['acciones']));
        }

        $rol = Rol::create([
            'rol' => $request->rol,
            'permisos' => $request->permisos,
        ]);

        if (!$rol) {
            return $this->error('Error al crear el rol', 500);
        }

        return $this->success($rol, 'Rol creado exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $rol)
    {
        //
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return $this->error('Rol no encontrado', 404);
        }

        return $this->success($rol, 'Rol obtenido exitosamente', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $rol)
    {
        // Buscar el rol por su nombre
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return $this->error('Rol no encontrado', 404);
        }

        $validator = $this->validateRol($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        if ($request->has('permisos')) {
            $modulos = array_column($request->permisos, 'modulo');
            if (count($modulos) !== count(array_unique($modulos))) {
                return $this->error('Los módulos deben ser únicos dentro de un mismo rol', 400);
            }
            // Eliminar acciones repetidas dentro de cada módulo
            foreach ($request->permisos as &$permiso) {
                $permiso['acciones'] = array_values(array_unique($permiso['acciones']));
            }
            $rol->permisos = $request->permisos;
        }

        if (!$rol->save()) {
            return $this->error('Error al actualizar el rol', 500);
        }

        return $this->success($rol, 'Rol actualizado exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $rol)
    {
        //
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return $this->error('Rol no encontrado', 404);
        }

        $rol->delete();
        return $this->success(null, 'Rol eliminado exitosamente', 200);
    }
}
