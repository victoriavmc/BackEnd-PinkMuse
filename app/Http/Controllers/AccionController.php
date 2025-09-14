<?php

namespace App\Http\Controllers;

use App\Models\Accion;
use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class AccionController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $acciones = Accion::all();

        if ($acciones->isEmpty()) {
            return $this->error('No se encontraron acciones', 404);
        }

        return $this->success($acciones, 'Listado de acciones');
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
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Verificar usuario
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        // Verificar referencia
        $tipo = $request->tipoReferencia;
        $referencia = match ($tipo) {
            'noticia'    => Noticia::find($request->referencia_id),
            'producto'   => Producto::find($request->referencia_id),
            'comentario' => Comentario::find($request->referencia_id),
            default      => null,
        };

        if (!$referencia) {
            return $this->error(ucfirst($tipo) . ' no encontrada', 404);
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
            return $this->error(
                "El usuario ya tiene una acción '{$accionExistente->tipo}' sobre esta referencia. Use update si quiere modificar.",
                409
            );
        }

        // Validar que "causa" sea obligatoria solo si es reporte
        if ($request->tipo === 'reporte' && empty($request->causa)) {
            return $this->error('La causa es obligatoria para el tipo reporte', 400);
        }

        // Crear la acción
        $accion = Accion::create($request->all());

        return $this->success($accion, 'Acción creada exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accion = Accion::find($id);

        if (!$accion) {
            return $this->error('Acción no encontrada', 404);
        }

        return $this->success($accion, 'Acción encontrada');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accion = Accion::find($id);

        if (!$accion) {
            return $this->error('Acción no encontrada', 404);
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
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Si se cambia el tipo
        if ($request->has('tipo')) {
            if ($request->tipo === 'reporte' && empty($request->causa)) {
                return $this->error('La causa es obligatoria para el tipo reporte', 400);
            }
            $accion->tipo = $request->tipo;
        }

        $accion->fecha = $request->fecha;
        if ($request->has('causa')) {
            $accion->causa = $request->causa;
        }

        $accion->save();

        return $this->success($accion, 'Acción actualizada correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accion = Accion::find($id);

        if (!$accion) {
            return $this->error('Acción no encontrada', 404);
        }

        $accion->delete();

        return $this->success($accion, 'Acción eliminada correctamente');
    }
}
