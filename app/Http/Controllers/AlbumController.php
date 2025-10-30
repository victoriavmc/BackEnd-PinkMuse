<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\ImageService;

class AlbumController
{
    use ApiResponse;

    // --- INYECCIÓN DE DEPENDENCIAS ---
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
        //
        $album = Album::all();
        if ($album->isEmpty()) {
            return $this->error('No se encontraron albums', 404);
        }
        return $this->success(['albums' => $album], 200);
    }

    public function validateAlbum(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
            $rules = [
                'artista' => 'sometimes|required|string|max:255',
                'fecha' => 'sometimes|required|date',
                'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // CAMBIO
                'nombre' => 'prohibited',
                'redesSociales' => 'nullable|array|max:255',
                // Validación de canciones
                'canciones' => 'nullable|array',
                'canciones.*.titulo' => 'sometimes|required|string|max:255',
                'canciones.*.letra' => 'nullable|string',
                'canciones.*.feat' => 'nullable|string|max:255',
                'canciones.*.imagenPrincipal' => 'nullable|string|max:500', // Se mantiene como string (URL)
            ];
        } else {
            $rules = [
                'artista' => 'required|string|max:255',
                'fecha' => 'required|date',
                'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // CAMBIO
                'nombre' => 'required|string|max:255|unique:albums,nombre', // nombre único
                'redesSociales' => 'nullable|array|max:255',
                // Validación de canciones
                'canciones' => 'nullable|array',
                'canciones.*.titulo' => 'nullable|string|max:255', // cada título único dentro del array
                'canciones.*.letra' => 'nullable|string',
                'canciones.*.feat' => 'nullable|string|max:255',
                'canciones.*.imagenPrincipal' => 'nullable|string|max:500', // Se mantiene como string (URL)
            ];
        }
        return Validator::make($request->all(), $rules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validateAlbum($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // en un album no puede haber una canciones con el mismo titulo (repetidas)
        if ($request->has('canciones')) {
            $titulos = array_column($request->canciones, 'titulo');
            if (count($titulos) !== count(array_unique($titulos))) {
                return $this->error('Error de validación', 400, ['canciones' => ['No puede haber canciones con el mismo título en un mismo álbum']]);
            }
        }

        //Verificar que no se dupliquen las redes sociales dentro del array.
        if ($request->has('redesSociales')) {
            $redes = $request->redesSociales;
            if (count($redes) !== count(array_unique($redes))) {
                return $this->error('Error de validación', 400, ['redesSociales' => ['No puede haber redes sociales duplicadas en el mismo álbum']]);
            }
        }

        $rutasImagenPrincipal = null;
        if ($request->hasFile('imagenPrincipal')) {
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'album',
                $request->nombre,
                false,
                0
            );
            $rutasImagenPrincipal = $rutas[0];
        }

        $album = new Album();
        $album->artista = $request->artista;
        $album->fecha = $request->fecha;
        $album->imagenPrincipal = $rutasImagenPrincipal; // Asignar ruta
        $album->nombre = $request->nombre;
        $album->redesSociales = $request->redesSociales ?? null;
        $album->canciones = $request->canciones ?? [];
        $album->save();

        if (!$album) {
            return $this->error('Error al crear el album', 500);
        }

        return $this->success($album, 'Album creado exitosamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $titulo)
    {
        //
        $album = Album::where('nombre', $titulo)->first();
        if (!$album) {
            return $this->error('album no encontrado', 404);
        }
        return $this->success(['album' => $album], 200);
    }

    // Mostrar una cancion especifica de un album
    public function showSong(string $tituloAlbum, string $tituloCancion)
    {
        $album = Album::where('nombre', $tituloAlbum)->first();
        if (!$album) {
            return $this->error('Álbum no encontrado', 404);
        }
        $canciones = $album->canciones ?? [];
        foreach ($canciones as $cancion) {
            if ($cancion['titulo'] === $tituloCancion) {
                return $this->success(['cancion' => $cancion], 200);
            }
        }
        return $this->error('Canción no encontrada en este álbum', 404);
    }

    // Actualizar cancion especifica de un album
    public function updateSong(Request $request, string $tituloAlbum, string $tituloCancion)
    {
        $album = Album::where('nombre', $tituloAlbum)->first();
        if (!$album) {
            return $this->error('Álbum no encontrado', 404);
        }
        $canciones = $album->canciones ?? [];
        foreach ($canciones as $cancion) {
            if ($cancion['titulo'] === $tituloCancion) {

                // Falta los atributos para actualizar
            }
        }
        return $this->error('Canción no encontrada en este álbum', 404);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $titulo)
    {
        //
        $album = Album::where('nombre', $titulo)->first();
        if (!$album) {
            return $this->error('Álbum no encontrado', 404);
        }

        $validator = $this->validateAlbum($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        if ($request->has('redesSociales')) {
            $redes = $request->redesSociales;
            if (count($redes) !== count(array_unique($redes))) {
                return $this->error('Error de validación', 400, ['redesSociales' => ['No puede haber redes sociales duplicadas en el mismo álbum']]);
            }
        }

        if ($request->hasFile('imagenPrincipal')) {
            // 1. Eliminar la imagen anterior
            $this->imageService->eliminar($album->imagenPrincipal);

            // 2. Guardar la nueva
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'album',
                $album->nombre,
                false,
                0
            );

            // 3. Asignar la nueva ruta
            $album->imagenPrincipal = $rutas[0];
        }

        // Actualizar campos simples
        foreach (['artista', 'fecha', 'redesSociales'] as $field) {
            if ($request->filled($field)) {
                $album->{$field} = $request->{$field};
            }
        }

        // Lógica de actualización de canciones (existente)
        if ($request->has('canciones')) {
            $cancionesActuales = $album->canciones ?? [];
            $titulosActuales = array_column($cancionesActuales, 'titulo');
            $titulosNuevos = [];

            foreach ($request->canciones as $cancionNueva) {
                $tituloCancion = $cancionNueva['titulo'];

                // Verifica duplicado dentro del mismo request
                if (in_array($tituloCancion, $titulosNuevos)) {
                    return $this->error("Error de validación: La canción '{$tituloCancion}' está duplicada en la solicitud", 400);
                }
                $titulosNuevos[] = $tituloCancion;

                if (in_array($tituloCancion, $titulosActuales)) {
                    // Actualizar canción existente
                    foreach ($cancionesActuales as &$cancionActual) {
                        if ($cancionActual['titulo'] === $tituloCancion) {
                            $cancionActual['letra'] = $cancionNueva['letra'] ?? $cancionActual['letra'];
                            $cancionActual['feat'] = $cancionNueva['feat'] ?? $cancionActual['feat'];
                            // Actualiza la imagen de la canción (como string/URL)
                            $cancionActual['imagenPrincipal'] = $cancionNueva['imagenPrincipal'] ?? $cancionActual['imagenPrincipal'];
                        }
                    }
                } else {
                    // Agregar nueva canción
                    $cancionesActuales[] = $cancionNueva;
                }
            }

            $album->canciones = $cancionesActuales;
        }

        $album->save();

        return $this->success($album, 'Álbum actualizado exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $nombreAlbum)
    {
        $album = Album::where('nombre', $nombreAlbum)->first();

        if (!$album) {
            return $this->error('Álbum no encontrado', 404);
        }

        // Si se envía 'tituloCancion', borramos solo esa canción (lógica existente)
        if ($request->has('tituloCancion')) {
            $titulo = $request->input('tituloCancion');
            $cancionesActuales = $album->canciones ?? [];

            $nuevasCanciones = array_filter($cancionesActuales, function ($cancion) use ($titulo) {
                return $cancion['titulo'] !== $titulo;
            });

            // Si no se encontró la canción
            if (count($nuevasCanciones) === count($cancionesActuales)) {
                return $this->error("La canción '{$titulo}' no existe en este álbum", 404);
            }
            $album->canciones = array_values($nuevasCanciones); // reindexar
            $album->save();
            return $this->success(null, "Canción '{$titulo}' eliminada exitosamente", 200);
        }

        // Si no se envía 'tituloCancion', borramos todo el álbum
        try {
            // 1. Eliminar la imagen principal del álbum (si existe)
            $this->imageService->eliminar($album->imagenPrincipal);

            // NOTA: Esto no borra imágenes de canciones individuales

            // 2. Borrar el álbum de la BD
            $album->delete();

            return $this->success(null, "Álbum eliminado exitosamente", 204);

        } catch (\Exception $e) {
            return $this->error('Error al eliminar el álbum', 500, $e->getMessage());
        }
    }
}
