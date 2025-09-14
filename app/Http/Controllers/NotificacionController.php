<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class NotificacionController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notificaciones = Notificacion::orderBy('fecha', 'desc')->get();
        if ($notificaciones->isEmpty()) {
            return $this->error('No se encontraron notificaciones', 404);
        }
        return $this->success($notificaciones, 'Listado de notificaciones', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipoReferencia' => 'required|string|in:evento,producto,comprobante',
            'mensaje' => 'required|string|max:500',
            'referencia_id' => 'required|string',
            'fecha' => 'required|date'
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $notificacion = Notificacion::create($request->all());
        if (!$notificacion) {
            return $this->error('Error al crear notificación', 500);
        }

        return $this->success($notificacion, 'Notificación creada exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return $this->error('Notificación no encontrada', 404);
        }

        return $this->success($notificacion, 'Notificación encontrada', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return $this->error('Notificación no encontrada', 404);
        }

        $validator = Validator::make($request->all(), [
            'tipoReferencia' => 'sometimes|string|in:evento,producto,comprobante',
            'mensaje' => 'sometimes|string|max:500',
            'referencia_id' => 'sometimes|string',
            'fecha' => 'sometimes|date'
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $notificacion->update($request->all());

        return $this->success($notificacion, 'Notificación actualizada', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return $this->error('Notificación no encontrada', 404);
        }

        $notificacion->delete();

        return $this->success(null, 'Notificación eliminada', 200);
    }
}
