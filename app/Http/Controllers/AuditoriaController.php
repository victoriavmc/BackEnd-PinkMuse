<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class AuditoriaController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auditoria = Auditoria::orderBy('fecha', 'desc')->get();
        if ($auditoria->isEmpty()) {
            return $this->error('No se encontraron movimientos para la auditoria', 404);
        }
        return $this->success($auditoria, 'Listado de auditoria', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'accion' => 'required|string|in:crear,actualizar,eliminar',
            'coleccion' => 'required|string',
            'fecha' => 'required|date',
            'datos' => 'required|array',
            'usuario_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        $auditoria = Auditoria::create([
            'accion' => $request->accion,
            'coleccion' => $request->coleccion,
            'fecha' => $request->fecha,
            'datos' => $request->datos,
            'usuario_id' => $request->usuario_id
        ]);

        if (!$auditoria) {
            return $this->error('No se puede registrar movimientos en la auditoria', 500);
        }

        return $this->success($auditoria, 'Movimiento de auditoria registrado exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auditoria = Auditoria::find($id);

        if (!$auditoria) {
            return $this->error('Movimiento en auditoria no encontrado', 404);
        }

        return $this->success($auditoria, 'Movimiento en auditoria encontrado', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $auditoria = Auditoria::find($id);

        if (!$auditoria) {
            return $this->error('Movimiento en auditoria no encontrado', 404);
        }

        return $this->error('NO PODES EDITAR NADA DE AUDITORIA BOBO, ANDA PA ALLA', 403);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $auditoria = Auditoria::find($id);
        if (!$auditoria) {
            return $this->error('Movimiento en auditoria no encontrado', 404);
        }

        return $this->error('NO PODES BORRAR NADA DE AUDITORIA BOBO, ANDA PA ALLA', 403);
    }
}
