<?php

namespace App\Http\Controllers;

use App\Models\Noticia;
use App\Models\Reaccion;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class NoticiaController
{
    use ApiResponse;

    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
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
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'sometimes|date',
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
            'titulo' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'imagenPrincipal' => 'nullable|string|max:255',
            'imagenes' => 'nullable|array',
            'imagenes.*' => 'string|max:255',
            'fecha' => 'required|date',
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

        if (Noticia::where('titulo', $request->titulo)->exists()) {
            return $this->error('Ya existe una noticia con el mismo titulo', 409);
        }

        $payload = $request->only([
            'tipoActividad',
            'titulo',
            'descripcion',
            'resumen',
            'imagenPrincipal',
            'imagenes',
            'fecha',
            'habilitacionComentarios',
            'habilitacionAcciones',
            'autor',
            'categoria',
            'fuente',
            'etiquetas',
        ]);

        if ($request->has('habilitacionComentarios')) {
            $payload['habilitacionComentarios'] = (bool) $request->habilitacionComentarios;
        }

        if ($request->has('imagenes')) {
            $payload['imagenes'] = $payload['imagenes'] ? array_values($payload['imagenes']) : [];
        }

        if ($request->has('etiquetas')) {
            $payload['etiquetas'] = $payload['etiquetas'] ? array_values($payload['etiquetas']) : [];
        }

        $noticia = Noticia::create($payload);

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
            return $this->error('Error de validacion', 400, $validator->errors());
        }

        $updates = $request->only([
            'titulo',
            'descripcion',
            'resumen',
            'imagenPrincipal',
            'imagenes',
            'fecha',
            'habilitacionComentarios',
            'habilitacionAcciones',
            'autor',
            'categoria',
            'fuente',
            'etiquetas',
        ]);

        if (array_key_exists('titulo', $updates) && $updates['titulo'] !== $noticia->titulo) {
            if (Noticia::where('titulo', $updates['titulo'])->where('_id', '!=', $noticia->_id)->exists()) {
                return $this->error('Ya existe una noticia con el mismo titulo', 409);
            }
        }

        if (array_key_exists('habilitacionComentarios', $updates)) {
            $updates['habilitacionComentarios'] = (bool) $updates['habilitacionComentarios'];
        }

        if (array_key_exists('imagenes', $updates)) {
            $updates['imagenes'] = $updates['imagenes'] ? array_values($updates['imagenes']) : [];
        }

        if (array_key_exists('etiquetas', $updates)) {
            $updates['etiquetas'] = $updates['etiquetas'] ? array_values($updates['etiquetas']) : [];
        }

        $noticia->fill($updates);
        $noticia->save();

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

        $noticia->delete();

        return $this->success($noticia, 'Noticia eliminada exitosamente', 200);
    }
}
