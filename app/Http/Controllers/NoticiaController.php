<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Reaccion;
use App\Services\NotificationService;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class NoticiaController
{
    use ApiResponse;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var ImageService
     */
    protected $imageService;

    public function __construct(NotificationService $notificationService, ImageService $imageService)
    {
        $this->notificationService = $notificationService;
        $this->imageService = $imageService;
    }

    protected function normalizeId($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            $string = trim((string) $value);
            return $string === '' ? null : $string;
        }

        if (is_array($value) && isset($value['$oid'])) {
            $string = (string) $value['$oid'];
            return $string === '' ? null : $string;
        }

        if (is_object($value)) {
            if (isset($value->{'$oid'})) {
                $string = (string) $value->{'$oid'};
                return $string === '' ? null : $string;
            }

            if (method_exists($value, '__toString')) {
                $string = trim((string) $value);
                return $string === '' ? null : $string;
            }
        }

        return null;
    }

    protected function defaultReactionSummary(): array
    {
        $counts = [];
        foreach (Reaccion::TYPES as $type) {
            $counts[$type] = 0;
        }

        return [
            'counts' => $counts,
            'total' => 0,
            'userReaction' => null,
        ];
    }

    protected function buildReactionSummary(string $referenceId, ?string $usuarioId = null): array
    {
        $summary = $this->defaultReactionSummary();
        $counts = $summary['counts'];

        $match = [
            'tipoReferencia' => 'noticia',
            'referencia_id' => $referenceId,
        ];

        try {
            $results = Reaccion::raw(function ($collection) use ($match) {
                return $collection->aggregate([
                    ['$match' => $match],
                    ['$group' => [
                        '_id' => '$tipo',
                        'count' => ['$sum' => 1],
                    ]],
                ]);
            });

            foreach ($results as $row) {
                $rawType = null;
                if (is_object($row) && isset($row->_id)) {
                    $rawType = $row->_id;
                } elseif (is_array($row) && isset($row['_id'])) {
                    $rawType = $row['_id'];
                }

                $type = null;
                if (is_string($rawType)) {
                    $type = $rawType;
                } elseif ($rawType !== null && method_exists($rawType, '__toString')) {
                    $type = (string) $rawType;
                }

                if ($type !== null && isset($counts[$type])) {
                    $rawCount = null;
                    if (is_object($row) && isset($row->count)) {
                        $rawCount = $row->count;
                    } elseif (is_array($row) && isset($row['count'])) {
                        $rawCount = $row['count'];
                    }

                    $counts[$type] = (int) ($rawCount ?? 0);
                }
            }
        } catch (\Throwable $e) {
            $reacciones = Reaccion::where('tipoReferencia', 'noticia')
                ->where('referencia_id', $referenceId)
                ->get(['tipo']);

            foreach ($reacciones as $reaccion) {
                $tipo = $reaccion->tipo;
                if (isset($counts[$tipo])) {
                    $counts[$tipo]++;
                }
            }
        }

        $summary['counts'] = $counts;
        $summary['total'] = array_sum($counts);

        $normalizedUserId = $usuarioId ? $this->normalizeId($usuarioId) : null;

        if ($normalizedUserId) {
            $userReaction = Reaccion::where('tipoReferencia', 'noticia')
                ->where('referencia_id', $referenceId)
                ->where('usuario_id', $normalizedUserId)
                ->value('tipo');

            if (is_string($userReaction) && in_array($userReaction, Reaccion::TYPES, true)) {
                $summary['userReaction'] = $userReaction;
            }
        }

        return $summary;
    }

    protected function attachReactionSummary(Noticia $noticia, ?string $usuarioId = null): Noticia
    {
        $referenceId = $this->normalizeId($noticia->_id ?? $noticia->id ?? null);
        if ($referenceId) {
            $noticia->setAttribute('reactions', $this->buildReactionSummary($referenceId, $usuarioId));
        } else {
            $noticia->setAttribute('reactions', $this->defaultReactionSummary());
        }

        return $noticia;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $usuarioId = $this->normalizeId($request->query('usuario_id') ?? $request->query('usuarioId') ?? null);
        $noticias = Noticia::all();
        if ($noticias->isEmpty()) {
            return $this->error('No se encontraron noticias', 404);
        }

        $enriched = $noticias->map(function (Noticia $noticia) use ($usuarioId) {
            return $this->attachReactionSummary($noticia, $usuarioId);
        });

        return $this->success($enriched, 'Noticias encontradas', 200);
    }

    /**
     * Build the validator for create and update.
     */
    public function validatorNoticia(Request $request, $isUpdate = false)
    {
        $rules = $isUpdate ? [
            'tipoActividad' => 'prohibited',
            'titulo' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'fecha' => 'sometimes|date|after_or_equal:today',
            'habilitacionComentarios' => 'sometimes|boolean',
            'habilitacionAcciones' => 'sometimes|string|in:si,no',
            'resumen' => 'nullable|string',
            'autor' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'fuente' => 'nullable|string|max:255',
            'etiquetas' => 'nullable|array',
            'etiquetas.*' => 'string|max:255',
        ] : [
            'tipoActividad' => 'required|string|in:noticia',
            'titulo' => 'required|string|max:255|unique:noticias,titulo',
            'descripcion' => 'required|string',
            'imagenPrincipal' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'fecha' => 'required|date|after_or_equal:today',
            'habilitacionComentarios' => 'required|boolean',
            'habilitacionAcciones' => 'required|string|in:si,no',
            'resumen' => 'nullable|string',
            'autor' => 'nullable|string|max:255',
            'categoria' => 'nullable|string|max:255',
            'fuente' => 'nullable|string|max:255',
            'etiquetas' => 'nullable|array',
            'etiquetas.*' => 'string|max:255',
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
            return $this->error('Error de validacion', 400, $validator->errors());
        }

        // 'unique' en el validador ya maneja esto, pero una doble verificación es segura.
        if (Noticia::where('titulo', $request->titulo)->exists()) {
            return $this->error('Ya existe una noticia con el mismo titulo', 409);
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

        $this->notificationService->notifyUsers('noticia', [
            'titulo' => 'Nueva noticia',
            'mensaje' => sprintf('Se publicA3 la noticia "%s".', $noticia->titulo),
            'referencia_tipo' => 'noticia',
            'referencia_id' => $noticia->_id ?? $noticia->id ?? null,
            'datos' => [
                'titulo' => $noticia->titulo,
                'resumen' => $noticia->resumen ?? $noticia->descripcion,
                'imagen' => $noticia->imagenPrincipal,
                'categoria' => $noticia->categoria,
                'fecha' => $noticia->fecha,
                'link' => '/noticias/' . rawurlencode((string) $noticia->titulo),
            ],
            'fecha' => $noticia->fecha ?? Carbon::now(),
        ]);

        return $this->success($noticia, 'Noticia creada con exito', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $titulo)
    {
        $noticia = Noticia::where('titulo', $titulo)->first();
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        $usuarioId = $this->normalizeId($request->query('usuario_id') ?? $request->query('usuarioId') ?? null);
        $this->attachReactionSummary($noticia, $usuarioId);

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
