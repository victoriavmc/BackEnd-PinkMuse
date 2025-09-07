<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlbumController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $album = Album::all();
        if ($album->isEmpty()) {
            $data = [
                'message' => 'No se encontraron albums',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'album' => $album,
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
            'artista' => 'required|string|max:255',
            'fecha' => 'required|date',
            'imagenPrincipal' => 'nullable|string|max:500',
            'nombre' => 'required|string|max:255|unique:albums,nombre', // nombre único
            'redesSociales' => 'nullable|array|max:255',
            // Validación de canciones
            'canciones' => 'nullable|array',
            'canciones.*.titulo' => 'nullable|string|max:255', // cada título único dentro del array
            'canciones.*.letra' => 'nullable|string',
            'canciones.*.feat' => 'nullable|string|max:255',
            'canciones.*.imagenPrincipal' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // en un album no puede haber una canciones con el mismo titulo (repetidas)
        if ($request->has('canciones')) {
            $titulos = array_column($request->canciones, 'titulo');
            if (count($titulos) !== count(array_unique($titulos))) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['canciones' => ['No puede haber canciones con títulos repetidos en el mismo álbum']],
                    'status' => 400
                ], 400);
            }
        }

        //Verificar que no se dupliquen las redes sociales dentro del array.
        if ($request->has('redesSociales')) {
            $redes = $request->redesSociales;
            if (count($redes) !== count(array_unique($redes))) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['redesSociales' => ['No puede haber redes sociales duplicadas en el mismo álbum']],
                    'status' => 400
                ], 400);
            }
        }

        $album = new Album();
        $album->artista = $request->artista;
        $album->fecha = $request->fecha;
        $album->imagenPrincipal = $request->imagenPrincipal ?? null;
        $album->nombre = $request->nombre;
        $album->redesSociales = $request->redesSociales ?? null;
        $album->canciones = $request->canciones ?? [];
        $album->save();

        if (!$album) {
            return response()->json([
                'message' => 'Error al crear el album',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Album creado exitosamente',
            'album' => $album,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $titulo)
    {
        //
        $album = Album::where('nombre', $titulo)->first();
        if (!$album) {
            return response()->json([
                'message' => 'album no encontrado',
                'status' => 404
            ], 404);
        }
        return response()->json([
            'album' => $album,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $titulo)
    {
        //
        $album = Album::where('nombre', $titulo)->first();
        if (!$album) {
            return response()->json([
                'message' => 'album no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'artista' => 'required|string|max:255',
            'fecha' => 'required|date',
            'imagenPrincipal' => 'nullable|string|max:500',
            'nombre' => 'prohibited',
            'redesSociales' => 'nullable|array',
            // Validación de canciones
            'canciones' => 'nullable|array',
            'canciones.*.titulo' => 'sometimes|required|string|max:255',
            'canciones.*.letra' => 'nullable|string',
            'canciones.*.feat' => 'nullable|string|max:255',
            'canciones.*.imagenPrincipal' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if ($request->has('redesSociales')) {
            $redes = $request->redesSociales;
            if (count($redes) !== count(array_unique($redes))) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => ['redesSociales' => ['No puede haber redes sociales duplicadas en el mismo álbum']],
                    'status' => 400
                ], 400);
            }
        }
        // Actualizar campos simples
        foreach (['artista', 'fecha', 'imagenPrincipal', 'redesSociales'] as $field) {
            if ($request->filled($field)) {
                $album->{$field} = $request->{$field};
            }
        }

        if ($request->has('canciones')) {
            $cancionesActuales = $album->canciones ?? [];
            $titulosActuales = array_column($cancionesActuales, 'titulo');
            $titulosNuevos = [];

            foreach ($request->canciones as $cancionNueva) {
                $titulo = $cancionNueva['titulo'];

                // Verifica duplicado dentro del mismo request
                if (in_array($titulo, $titulosNuevos)) {
                    return response()->json([
                        'message' => 'Error de validación',
                        'errors' => [
                            'canciones' => ["El título '{$titulo}' se repite en la solicitud"]
                        ],
                        'status' => 400
                    ], 400);
                }
                $titulosNuevos[] = $titulo;

                if (in_array($titulo, $titulosActuales)) {
                    // Actualizar canción existente
                    foreach ($cancionesActuales as &$cancionActual) {
                        if ($cancionActual['titulo'] === $titulo) {
                            $cancionActual['letra'] = $cancionNueva['letra'] ?? $cancionActual['letra'];
                            $cancionActual['feat'] = $cancionNueva['feat'] ?? $cancionActual['feat'];
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

        return response()->json([
            'message' => 'Álbum actualizado exitosamente',
            'album' => $album,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $nombreAlbum)
    {
        $album = Album::where('nombre', $nombreAlbum)->first();

        if (!$album) {
            return response()->json([
                'message' => 'Álbum no encontrado',
                'status' => 404
            ], 404);
        }

        // Si se envía 'tituloCancion', borramos solo esa canción
        if ($request->has('tituloCancion')) {
            $titulo = $request->input('tituloCancion');
            $cancionesActuales = $album->canciones ?? [];

            $nuevasCanciones = array_filter($cancionesActuales, function ($cancion) use ($titulo) {
                return $cancion['titulo'] !== $titulo;
            });

            // Si no se encontró la canción
            if (count($nuevasCanciones) === count($cancionesActuales)) {
                return response()->json([
                    'message' => "La canción '{$titulo}' no existe en este álbum",
                    'status' => 404
                ], 404);
            }

            $album->canciones = array_values($nuevasCanciones); // reindexar
            $album->save();

            return response()->json([
                'message' => "Canción '{$titulo}' eliminada exitosamente",
                'album' => $album,
                'status' => 200
            ], 200);
        }

        // Si no se envía 'tituloCancion', borramos todo el álbum
        $album->delete();

        return response()->json([
            'message' => 'Álbum eliminado exitosamente',
            'status' => 200
        ], 200);
    }
}
