<?php

namespace App\Http\Controllers;

use App\Models\Accion;
use App\Models\Album;
use App\Models\Auditoria;
use App\Models\Comentario;
use App\Models\Comprobante;
use App\Models\Evento;
use App\Models\Noticia;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\RedSocial;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificacionController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notificaciones = Notificacion::orderBy('fecha', 'desc')->get();
        if ($notificaciones->isEmpty()) {
            $data = [
                'message' => 'No se encontraron notificaciones',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        return response()->json([
            'message' => 'Listado de notificaciones',
            'notificaciones' => $notificaciones,
            'status' => 200
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipoRefencia' => 'required|string|in:evento,producto,comprobante',
            'mensaje' => 'required|string|max:500',
            'referencia_id' => 'required|string',
            'fecha' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $notificacion = Notificacion::create($request->all());
        if (!$notificacion) {
            return response()->json([
                'message' => 'Error al crear Notificación',
                'status' => 400
            ], 400);
        }

        return response()->json([
            'message' => 'Notificación creada exitosamente',
            'notificacion' => $notificacion,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return response()->json([
                'message' => 'Notificación no encontrada',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Notificación encontrada',
            'notificacion' => $notificacion,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return response()->json([
                'message' => 'Notificación no encontrada',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|string|in:evento,producto,comprobante',
            'mensaje' => 'sometimes|string|max:500',
            'referencia_id' => 'sometimes|string',
            'fecha' => 'sometimes|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        $notificacion->update($request->all());

        return response()->json([
            'message' => 'Notificación actualizada',
            'notificacion' => $notificacion,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $notificacion = Notificacion::find($id);
        if (!$notificacion) {
            return response()->json([
                'message' => 'Notificación no encontrada',
                'status' => 404
            ], 404);
        }

        $notificacion->delete();

        return response()->json([
            'message' => 'Notificación eliminada',
            'status' => 200
        ], 200);
    }
}
