<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use phpDocumentor\Reflection\PseudoTypes\True_;

class NoticiaController
{
    use ApiResponse;

    private ImageService $imageService;

    /**
     * Inyectar el ImageService en el constructor.
     */
    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
            'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'fecha' => 'sometimes|date',
            'habilitacionComentarios' => 'sometimes|boolean',
            'habilitacionAcciones' => 'sometimes|string|in:si,no',
        ] : [
            'tipoActividad' => 'required|string|in:noticia',
            'titulo' => 'required|string|max:255|unique:noticias,titulo',
            'descripcion' => 'required|string',
            'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
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
        $validator = $this->validatorNoticia($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // 'unique' en el validador ya maneja esto, pero una doble verificación es segura.
        if (Noticia::where('titulo', $request->titulo)->exists()) {
            return $this->error('Ya existe una noticia con el mismo título', 409);
        }

        // Obtenemos los datos validados
        $data = $validator->validated();
        $nombreBaseNoticia = $data['titulo'];

        // 1. Procesar imagenPrincipal
        $rutasImagenPrincipal = null;
        if ($request->hasFile('imagenPrincipal')) {
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'noticia',
                $nombreBaseNoticia . '_principal',
                false,
                0
            );
            $rutasImagenPrincipal = $rutas[0];
        }

        // 2. Procesar 'imagenes'
        $rutasImagenes = [];
        if ($request->hasFile('imagenes')) {
            $rutasImagenes = $this->imageService->guardar(
                $request->file('imagenes'),
                'noticia',
                $nombreBaseNoticia,
                true,
                0
            );
        }

        // 3. Asignar rutas a los datos
        $data['imagenPrincipal'] = $rutasImagenPrincipal;
        $data['imagenes'] = $rutasImagenes;

        // 4. Crear la noticia
        $noticia = Noticia::create($data);

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
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        $validator = $this->validatorNoticia($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $data = $validator->validated();
        // Usar el título original para la ruta base
        $nombreBaseNoticia = $noticia->titulo;

        // 1. Actualizar imagenPrincipal (si se envió una nueva)
        if ($request->hasFile('imagenPrincipal')) {
            // Eliminar la anterior
            $this->imageService->eliminar($noticia->imagenPrincipal);

            // Guardar la nueva
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'noticia',
                $nombreBaseNoticia . '_principal',
                false,
                0
            );
            $data['imagenPrincipal'] = $rutas[0];
        }

        // 2. Actualizar 'imagenes' (si se enviaron nuevas)
        if ($request->hasFile('imagenes')) {
            // Eliminar todas las imágenes antiguas
            if (is_array($noticia->imagenes)) {
                foreach ($noticia->imagenes as $oldImage) {
                    $this->imageService->eliminar($oldImage);
                }
            }

            // Guardar las nuevas
            $data['imagenes'] = $this->imageService->guardar(
                $request->file('imagenes'),
                'noticia',
                $nombreBaseNoticia,
                true,
                0
            );
        }

        // 3. Actualizar la noticia en la BD
        $noticia->update($data);

        return $this->success($noticia, 'Noticia actualizada exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $titulo)
    {
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        try {
            // 1. Eliminar la imagen principal
            $this->imageService->eliminar($noticia->imagenPrincipal);

            // 2. Eliminar todas las imágenes del array 'imagenes'
            if (is_array($noticia->imagenes)) {
                foreach ($noticia->imagenes as $imagen) {
                    $this->imageService->eliminar($imagen);
                }
            }

            // 3. Eliminar la noticia de la base de datos
            $noticia->delete();

            return $this->success(null, 'Noticia eliminada exitosamente', 204);
        } catch (\Exception $e) {
            return $this->error('Error al eliminar la noticia', 500, $e->getMessage());
        }
    }
}
