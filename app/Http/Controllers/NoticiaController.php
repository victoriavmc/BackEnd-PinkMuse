<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NoticiaController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $noticias = Noticia::all();
        if ($noticias->isEmpty()) {
            $data = [
                'message' => 'No se encontraron noticias',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'noticias' => $noticias,
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
            'tipoActividad' => 'required|string|in:noticia',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'required|date',
            'habilitacionComentarios' => 'required|boolean',
            'habilitacionAcciones' => 'required|string|in:si,no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Verificar si ya existe una noticia con el mismo título
        if (Noticia::where('titulo', $request->titulo)->exists()) {
            return response()->json([
                'message' => 'Ya existe una noticia con el mismo título',
                'status' => 409
            ], 409);
        }

        $noticia = Noticia::create($request->all());

        if (!$noticia) {
            return response()->json([
                'message' => 'Error al crear la noticia',
                'status' => 500
            ], 500);
        }

        $data = [
            'message' => 'Noticia creada con éxito',
            'noticia' => $noticia,
            'status' => 201
        ];
        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return response()->json([
                'message' => 'Noticia no encontrada',
                'status' => 404
            ], 404);
        }
        return response()->json([
            'noticia' => $noticia,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return response()->json([
                'message' => 'Noticia no encontrada',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tipoActividad' => 'prohibited',
            'titulo' => 'prohibited',
            'descripcion' => 'sometimes|string',
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'sometimes|date',
            'habilitacionComentarios' => 'sometimes|boolean',
            'habilitacionAcciones' => 'sometimes|string|in:si,no',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Actualizar campos simples
        foreach ($request->except('imagenes') as $key => $value) {
            $noticia->{$key} = $value;
        }

        // Reemplazar imágenes si vienen en el request
        if ($request->has('imagenes')) {
            $noticia->imagenes = $request->imagenes;
        }

        $noticia->save();

        return response()->json([
            'message' => 'Noticia actualizada exitosamente',
            'noticia' => $noticia,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return response()->json([
                'message' => 'Noticia no encontrada',
                'status' => 404
            ], 404);
        }
        $noticia->delete();

        return response()->json([
            'message' => 'Noticia eliminada exitosamente',
            'status' => 200
        ], 200);
    }
}
