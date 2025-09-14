<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class ComentarioController
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comentarios = Comentario::all();
        if ($comentarios->isEmpty()) {
            return $this->error('No se encontraron comentarios', 404);
        }
        return $this->success($comentarios, 'Listado de comentarios', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'texto' => 'required|string',
            'fecha' => 'required|date',
            'tipoReferencia' => 'required|string|in:noticia,producto',
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

        // Verificar noticia/producto
        $referencia = match ($request->tipoReferencia) {
            'noticia' => Noticia::find($request->referencia_id),
            'producto' => Producto::find($request->referencia_id),
            default => null,
        };

        if (!$referencia) {
            return $this->error(ucfirst($request->tipoReferencia) . ' no encontrada', 404);
        }

        $comentario = Comentario::create([
            'texto' => $request->texto,
            'fecha' => $request->fecha,
            'tipoReferencia' => $request->tipoReferencia,
            'referencia_id' => $request->referencia_id,
            'usuario_id' => $request->usuario_id,
        ]);

        return $this->success($comentario, 'Comentario creado exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario) {
            return $this->error('Comentario no encontrado', 404);
        }
        return $this->success($comentario, 'Comentario encontrado', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario) {
            return $this->error('Comentario no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'texto' => 'sometimes|string',
            'fecha' => 'prohibited',
            'usuario_id' => 'prohibited',
            'referencia_id' => 'prohibited',
            'tipoReferencia' => 'prohibited',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        if ($request->has('texto')) {
            $comentario->texto = $request->input('texto');
            $comentario->save();
        }

        return $this->success($comentario, 'Comentario actualizado exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $comentario = Comentario::find($id);
        if (!$comentario) {
            return $this->error('Comentario no encontrado', 404);
        }

        $comentario->delete();
        return $this->success($comentario, 'Comentario eliminado correctamente', 200);
    }
}
