<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RolController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Listo todos los roles
        $roles = Rol::all();

        if ($roles->isEmpty()) {
            $data = [
                'message' => 'No se encontraron roles',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'roles' => $roles,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'rol' => 'required|string|max:255|unique:roles,rol',
            'permisos' => 'required|array',
            'permisos.*.modulo' => 'required|string|max:255', // nombre del módulo
            'permisos.*.acciones' => 'required|array', // acciones permitidas en el módulo
            'permisos.*.acciones.*' => 'required|string|max:255', // nombre de la acción
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if (Rol::where('rol', $request->rol)->exists()) {
            return response()->json([
                'message' => 'El rol ya existe',
                'status' => 409
            ], 409);
        }

        // Validar módulos únicos
        $modulos = array_column($request->permisos, 'modulo');
        if (count($modulos) !== count(array_unique($modulos))) {
            return response()->json([
                'message' => 'Los módulos deben ser únicos dentro de un mismo rol',
                'status' => 400
            ], 400);
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
            return response()->json([
                'message' => 'Error al crear el rol',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'rol' => $rol,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $rol)
    {
        //
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return response()->json([
                'message' => 'Rol no encontrado',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'rol' => $rol,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $rol)
    {
        // Buscar el rol por su nombre
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return response()->json([
                'message' => 'Rol no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'rol' => 'prohibited',
            'permisos' => 'sometimes|required|array',
            'permisos.*.modulo' => 'prohibited',
            'permisos.*.acciones' => 'required_with:permisos|array|min:1',
            'permisos.*.acciones.*' => 'required_with:permisos|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if ($request->has('permisos')) {
            $modulos = array_column($request->permisos, 'modulo');
            if(count($modulos) !== count(array_unique($modulos))){
                return response()->json([
                    'message' => 'Los módulos deben ser únicos dentro de un mismo rol',
                    'status' => 400
                ], 400);
            }
            // Eliminar acciones repetidas dentro de cada módulo
            foreach ($request->permisos as &$permiso) {
                $permiso['acciones'] = array_values(array_unique($permiso['acciones']));
            }
            $rol->permisos = $request->permisos;
        }

        if (!$rol->save()) {
            return response()->json([
                'message' => 'Error al actualizar el rol',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'rol' => $rol,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $rol)
    {
        //
        $rol = Rol::where('rol', $rol)->first();

        if (!$rol) {
            return response()->json([
                'message' => 'Rol no encontrado',
                'status' => 404
            ], 404);
        }

        $rol->delete();
        return response()->json([
            'message' => 'Rol eliminado exitosamente',
            'status' => 200
        ], 200);
    }
}