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

class AuditoriaController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auditoria = Auditoria::orderBy('fecha', 'desc')->get();
        if ($auditoria->isEmpty()) {
            $data = [
                'message' => 'No se encontraron movientos para la auditoria',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        return response()->json([
            'message' => 'Listado de auditoria',
            'auditoria' => $auditoria,
            'status' => 200
        ], 200);
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
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        //Verifico si existe el usuario_id
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404
            ], 404);
        }

        $auditoria = Auditoria::create([
            'accion' => $request->accion,
            'coleccion' => $request->coleccion,
            'fecha' => $request->fecha,
            'datos' => $request->datos,
            'usuario_id' => $request->usuario_id
        ]);

        if (!$auditoria) {
            $data = [
                'message' => 'No se puede registrar movientos en la auditoria',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        return response()->json([
            'message' => 'Movimiento de Auditoria registrado exitosamente',
            'auditoria' => $auditoria,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auditoria = Auditoria::find($id);

        if (!$auditoria) {
            return response()->json([
                'message' => 'Movimiento en auditoria no encontrada',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Movimiento en auditoria encontrada',
            'auditoria' => $auditoria,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $auditoria = Auditoria::find($id);

        if (!$auditoria) {
            return response()->json([
                'message' => 'Movimiento en auditoria no encontrada',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'NO PODES EDITAR NADA DE AUDITORIA BOBO, ANDA PA ALLA',
            'status' => 404
        ], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $auditoria = Auditoria::find($id);
        if (!$auditoria) {
            return response()->json([
                'message' => 'Movimiento en auditoria no encontrada',
                'status' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'NO PODES BORRAR NADA DE AUDITORIA BOBO, ANDA PA ALLA',
            'status' => 404
        ], 404);
    }
}
