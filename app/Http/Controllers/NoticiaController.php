<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use phpDocumentor\Reflection\PseudoTypes\True_;

class NoticiaController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $noticias = Noticia::all();
        if ($noticias->isEmpty()) {
            return $this->error('No se encontraron noticias', 404);
        }
        return $this->success($noticias, 'Noticias encontradas', 200);
    }

    // Validacion
    public function validatorNoticia(Request $request, $isUpdate = false)
    {
        $rules = $isUpdate ? [
            'tipoActividad' => 'prohibited',
            'titulo' => 'prohibited',
            'descripcion' => 'sometimes|string',
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'sometimes|date',
            'habilitacionComentarios' => 'sometimes|boolean',
            'habilitacionAcciones' => 'sometimes|string|in:si,no',
        ] : [
            'tipoActividad' => 'required|string|in:noticia',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'required|date',
            'habilitacionComentarios' => 'required|boolean',
            'habilitacionAcciones' => 'required|string|in:si,no',
        ];

        return Validator::make($request->all(), $rules);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = $this->validatorNoticia($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Verificar si ya existe una noticia con el mismo título
        if (Noticia::where('titulo', $request->titulo)->exists()) {
            return $this->error('Ya existe una noticia con el mismo título', 409);
        }

        $noticia = Noticia::create($request->all());

        if (!$noticia) {
            return $this->error('Error al crear la noticia', 500);
        }
        return $this->success($noticia, 'Noticia creada con éxito', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }
        return $this->success($noticia, 'Noticia encontrada', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        $validator = $this->validatorNoticia($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 404, $validator->errors());
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
        return $this->success($noticia, 'Noticia actualizada exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $titulo)
    {
        //
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }
        $noticia->delete();
        return $this->success($noticia, 'Noticia eliminada exitosamente', 200);
    }
}
