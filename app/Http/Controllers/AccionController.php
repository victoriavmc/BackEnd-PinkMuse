<?php

namespace App\Http\Controllers;

use App\Models\Accion;
use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccionController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $acciones = Accion::all();
        if ($acciones->isEmpty()) {
            $data = [
                'message' => 'No se encontraron acciones',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'acciones' => $acciones,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string|in:like,dislike,reporte',
            'causa' => 'nullable|string',
            'fecha' => 'required|date',
            'tipoReferencia' => 'required|string|in:noticia,producto,comentario',
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

        // Verificar usuario
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404
            ], 404);
        }

        // Verificar referencia
        $tipo = $request->tipoReferencia;
        $referencia = match ($tipo) {
            'noticia'   => Noticia::find($request->referencia_id),
            'producto'  => Producto::find($request->referencia_id),
            'comentario' => Comentario::find($request->referencia_id),
            default     => null,
        };

        if (!$referencia) {
            return response()->json([
                'message' => ucfirst($tipo) . ' no encontrada',
                'status' => 404
            ], 404);
        }

        // Verificar si ya existe una acción de este usuario en esta referencia
        $accionExistente = Accion::where('usuario_id', $request->usuario_id)
            ->where('referencia_id', $request->referencia_id)
            ->where('tipoReferencia', $request->tipoReferencia)
            ->where(function ($q) use ($request) {
                if (in_array($request->tipo, ['like', 'dislike'])) {
                    $q->whereIn('tipo', ['like', 'dislike']);
                } else {
                    $q->where('tipo', $request->tipo);
                }
            })
            ->first();

        if ($accionExistente) {
            return response()->json([
                'message' => "El usuario ya tiene una acción '{$accionExistente->tipo}' sobre esta referencia. Use update si quiere modificar.",
                'status' => 409
            ], 409);
        }

        // Validar que "causa" sea obligatoria solo si es reporte
        if ($request->tipo === 'reporte' && empty($request->causa)) {
            return response()->json([
                'message' => 'La causa es obligatoria para el tipo reporte',
                'status' => 400
            ], 400);
        }

        // Crear la acción
        $accion = Accion::create([
            'tipo' => $request->tipo,
            'causa' => $request->causa,
            'fecha' => $request->fecha,
            'tipoReferencia' => $request->tipoReferencia,
            'referencia_id' => $request->referencia_id,
            'usuario_id' => $request->usuario_id,
        ]);

        return response()->json([
            'message' => 'Acción creada exitosamente',
            'accion' => $accion,
            'status' => 201
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $accion = Accion::find($id);

        if (!$accion) {
            return response()->json([
                'message' => 'Accion no encontrada',
                'status' => 404
            ], 404);
        }
        return response()->json([
            'message' => 'Accion encontrada',
            'accion' => $accion,
            'status' => 201
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accion = Accion::find($id);
        if (!$accion) {
            return response()->json([
                'message' => 'Acción no encontrada',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipo' => 'sometimes|string|in:like,dislike,reporte',
            'fecha' => 'required|date',
            'causa' => 'nullable|string',
            'tipoReferencia' => 'prohibited',
            'referencia_id' => 'prohibited',
            'usuario_id' => 'prohibited',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Si se cambia el tipo
        if ($request->has('tipo')) {
            $nuevoTipo = $request->tipo;

            // Si es reporte → causa obligatoria
            if ($nuevoTipo === 'reporte' && empty($request->causa)) {
                return response()->json([
                    'message' => 'La causa es obligatoria para el tipo reporte',
                    'status' => 400
                ], 400);
            }

            $accion->tipo = $nuevoTipo;
        }

        // Si viene fecha, actualizarla
        $accion->fecha = $request->fecha;

        // Si viene causa, actualizarla
        if ($request->has('causa')) {
            $accion->causa = $request->causa;
        }

        $accion->save();
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }
        return response()->json([
            'message' => 'Accion actualizada correctamente',
            'accion' => $accion,
            'status' => 201
        ], 201);
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $accion = Accion::find($id);

        if (!$accion) {
            return response()->json([
                'message' => 'Accion no encontrada',
                'status' => 404
            ], 404);
        }
        $accion->delete();
        return response()->json([
            'message' => 'Accion eliminada correctamente',
            'accion' => $accion,
            'status' => 201
        ], 201);
    }
}
