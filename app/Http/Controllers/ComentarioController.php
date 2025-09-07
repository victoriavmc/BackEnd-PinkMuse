<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComentarioController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $comentarios = Comentario::all();
        if ($comentarios->isEmpty()) {
            $data = [
                'message' => 'No se encontraron comentarios',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'comentarios' => $comentarios,
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
            'texto' => 'required|string',
            'fecha' => 'required|date',
            'tipoReferencia' => 'required|string|in:noticia,producto',
            'referencia_id' => 'required|string',
            'usuario_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        //Verificar si existe el usuario_id
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404
            ], 404);
        }

        //Verificar si existe la noticia/producto (tipoReferencia define), con el id (referencia_id)
        $tipo = $request->tipoReferencia;
        $referencia = null;

        if ($tipo === 'noticia') {
            $referencia = Noticia::find($request->referencia_id);
        } elseif ($tipo === 'producto') {
            $referencia = Producto::find($request->referencia_id);
        }

        if (!$referencia) {
            return response()->json([
                'message' => ucfirst($tipo) . ' no encontrada',
                'status' => 404
            ], 404);
        }

        // Crear el registro
        $comentario = Comentario::create([
            'texto' => $request->texto,
            'fecha' => $request->fecha,
            'tipoReferencia' => $tipo,
            'referencia_id' => $request->referencia_id,
            'usuario_id' => $request->usuario_id,
        ]);

        return response()->json([
            'message' => 'Comentario creado exitosamente',
            'comentario' => $comentario,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'message' => 'Comentario no encontrado',
                'status' => 404
            ], 404);
        }
        return response()->json([
            'message' => 'Comentario encontrado',
            'comentario' => $comentario,
            'status' => 201
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'message' => 'Comentario no encontrado',
                'status' => 404
            ], 404);
        }

        // Validación
        $validator = Validator::make($request->all(), [
            'texto' => 'sometimes|string',
            'fecha' => 'prohibited',
            'usuario_id' => 'prohibited',
            'referencia_id' => 'prohibited',
            'tipoReferencia' => 'prohibited',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if ($request->has('texto')) {
            $comentario->texto = $request->input('texto');
            $comentario->save();
        }

        return response()->json([
            'message' => 'Comentario actualizado exitosamente',
            'comentario' => $comentario,
            'status' => 200
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $comentario = Comentario::find($id);

        if (!$comentario) {
            return response()->json([
                'message' => 'Comentario no encontrado',
                'status' => 404
            ], 404);
        }

        $comentario->delete();
        return response()->json([
            'message' => 'Comentario eliminado correctamente',
            'comentario' => $comentario,
            'status' => 201
        ], 201);
    }
}
