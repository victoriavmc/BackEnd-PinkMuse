<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Reaccion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class ComentarioController
{
    use ApiResponse;
    public $usuario;

    public function __construct()
    {
        $this->usuario = Auth::user();
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

    protected function transformUserSummary(?Usuario $usuario): ?array
    {
        if (!$usuario) {
            return null;
        }

        $id = $this->normalizeId($usuario->_id ?? $usuario->id ?? null);
        $nombre = $usuario->nombre ?? '';
        $apellido = $usuario->apellido ?? '';
        $displayName = trim($nombre . ' ' . $apellido);

        if ($displayName === '') {
            $displayName = $usuario->correo ?? $id ?? '';
        }

        return [
            'id' => $id,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'displayName' => $displayName,
            'correo' => $usuario->correo ?? null,
            'avatar' => data_get($usuario, 'perfil.imagenPrincipal')
                ?? data_get($usuario, 'perfil.avatar')
                ?? $usuario->avatar
                ?? null,
            'rol' => $usuario->rol ?? null,
        ];
    }

    protected function mapComentariosWithUsuarios($comentarios, array $reactionSummaries = []): array
    {
        if ($comentarios->isEmpty()) {
            return [];
        }

        $usuarioIds = $comentarios
            ->pluck('usuario_id')
            ->map(fn($id) => $this->normalizeId($id))
            ->filter()
            ->unique()
            ->values();

        $usuarios = collect();

        if ($usuarioIds->isNotEmpty()) {
            $usuarios = Usuario::whereIn('_id', $usuarioIds)->get();

            if ($usuarios->isEmpty()) {
                $usuarios = Usuario::whereIn('id', $usuarioIds)->get();
            }

            $usuarios = $usuarios->keyBy(function ($usuario) {
                return $this->normalizeId($usuario->_id ?? $usuario->id ?? null);
            });
        }

        return $comentarios
            ->map(function (Comentario $comentario) use ($usuarios, $reactionSummaries) {
                $usuarioId = $this->normalizeId($comentario->usuario_id ?? null);
                $usuario = $usuarioId ? ($usuarios[$usuarioId] ?? null) : null;

                $formatted = $this->formatComentario($comentario, $usuario);
                $commentId = $formatted['id'] ?? null;

                if ($commentId && isset($reactionSummaries[$commentId])) {
                    $formatted['reactions'] = $reactionSummaries[$commentId];
                } else {
                    $formatted['reactions'] = $this->defaultReactionSummary();
                }

                return $formatted;
            })
            ->values()
            ->all();
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

    protected function buildReactionSummaries($comentarios, ?string $usuarioId = null): array
    {
        if ($comentarios->isEmpty()) {
            return [];
        }

        $ids = $comentarios
            ->map(function ($comentario) {
                if ($comentario instanceof Comentario) {
                    return $this->normalizeId($comentario->_id ?? $comentario->id ?? null);
                }

                if (is_array($comentario) && isset($comentario['_id'])) {
                    return $this->normalizeId($comentario['_id']);
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $summaries = [];
        foreach ($ids as $id) {
            $summaries[$id] = $this->defaultReactionSummary();
        }

        $reacciones = Reaccion::where('tipoReferencia', 'comentario')
            ->whereIn('referencia_id', $ids->all())
            ->get(['tipo', 'referencia_id', 'usuario_id']);

        $normalizedUserId = $usuarioId ? $this->normalizeId($usuarioId) : null;

        foreach ($reacciones as $reaccion) {
            $referenceId = $this->normalizeId($reaccion->referencia_id ?? null);
            if (!$referenceId || !isset($summaries[$referenceId])) {
                continue;
            }

            $tipo = $reaccion->tipo;
            if (!is_string($tipo) || !isset($summaries[$referenceId]['counts'][$tipo])) {
                continue;
            }

            $summaries[$referenceId]['counts'][$tipo]++;
            $summaries[$referenceId]['total']++;

            if ($normalizedUserId) {
                $reactionUserId = $this->normalizeId($reaccion->usuario_id ?? null);
                if ($reactionUserId && $reactionUserId === $normalizedUserId) {
                    $summaries[$referenceId]['userReaction'] = $tipo;
                }
            }
        }

        return $summaries;
    }

    protected function formatComentario(Comentario $comentario, ?Usuario $usuario = null): array
    {
        $data = $comentario->toArray();

        $data['id'] = $this->normalizeId($comentario->_id ?? $comentario->id ?? $data['_id'] ?? $data['id'] ?? null);
        $data['usuario_id'] = $this->normalizeId($comentario->usuario_id ?? $data['usuario_id'] ?? null);
        $data['referencia_id'] = $this->normalizeId($comentario->referencia_id ?? $data['referencia_id'] ?? null);

        if ($comentario->fecha instanceof \DateTimeInterface) {
            $data['fecha'] = $comentario->fecha->toAtomString();
        } elseif (!empty($data['fecha'])) {
            try {
                $data['fecha'] = Carbon::parse($data['fecha'])->toAtomString();
            } catch (\Throwable $e) {
                $data['fecha'] = (string) $data['fecha'];
            }
        } else {
            $data['fecha'] = null;
        }

        if (!array_key_exists('meta', $data) || $data['meta'] === null) {
            $data['meta'] = [];
        }

        $data['usuario'] = $this->transformUserSummary($usuario);

        return $data;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comentarios = Comentario::orderBy('fecha', 'desc')->get();

        if ($comentarios->isEmpty()) {
            return $this->success([], 'No se encontraron comentarios', 200);
        }

        return $this->success(
            $this->mapComentariosWithUsuarios($comentarios),
            'Listado de comentarios',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'texto' => 'required|string',
            'fecha' => 'sometimes|date',
            'tipoReferencia' => 'required|string|in:noticia,producto',
            'referencia_id' => 'required|string',
            'usuario_id' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validaci?n', 400, $validator->errors());
        }

        $payload = $validator->validated();

        $usuario = Usuario::find($payload['usuario_id']);
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        if ($payload['tipoReferencia'] === 'noticia') {
            $noticia = Noticia::find($payload['referencia_id']);
            if (!$noticia) {
                return $this->error('Noticia no encontrada', 404);
            }

            if (!($noticia->habilitacionComentarios ?? false)) {
                return $this->error('Los comentarios est?n deshabilitados para esta noticia', 403);
            }
        } else {
            $producto = Producto::find($payload['referencia_id']);
            if (!$producto) {
                return $this->error('Producto no encontrado', 404);
            }
        }

        $fecha = $payload['fecha'] ?? now();
        try {
            $fecha = Carbon::parse($fecha);
        } catch (\Throwable $e) {
            $fecha = now();
        }

        $comentario = Comentario::create([
            'texto' => $payload['texto'],
            'fecha' => $fecha,
            'tipoReferencia' => $payload['tipoReferencia'],
            'referencia_id' => $payload['referencia_id'],
            'usuario_id' => $payload['usuario_id'],
            'meta' => $payload['meta'] ?? null,
        ]);

        return $this->success(
            $this->formatComentario($comentario, $usuario),
            'Comentario creado exitosamente',
            201
        );
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

        $usuario = null;
        $usuarioId = $this->normalizeId($comentario->usuario_id ?? null);
        if ($usuarioId) {
            $usuario = Usuario::find($usuarioId);
        }

        return $this->success(
            $this->formatComentario($comentario, $usuario),
            'Comentario encontrado',
            200
        );
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
            'meta' => 'sometimes|array',
            'fecha' => 'prohibited',
            'usuario_id' => 'prohibited',
            'referencia_id' => 'prohibited',
            'tipoReferencia' => 'prohibited',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validaci?n', 400, $validator->errors());
        }

        $updates = [];

        if ($request->has('texto')) {
            $updates['texto'] = $request->input('texto');
        }

        if ($request->has('meta')) {
            $updates['meta'] = $request->input('meta');
        }

        if (!empty($updates)) {
            $comentario->fill($updates);
            $comentario->save();
        }

        $usuario = null;
        $usuarioId = $this->normalizeId($comentario->usuario_id ?? null);
        if ($usuarioId) {
            $usuario = Usuario::find($usuarioId);
        }

        return $this->success(
            $this->formatComentario($comentario, $usuario),
            'Comentario actualizado exitosamente',
            200
        );
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

        $usuario = null;
        $usuarioId = $this->normalizeId($comentario->usuario_id ?? null);
        if ($usuarioId) {
            $usuario = Usuario::find($usuarioId);
        }

        $payload = $this->formatComentario($comentario, $usuario);
        $comentario->delete();

        return $this->success($payload, 'Comentario eliminado correctamente', 200);
    }

    public function listByNoticia(Request $request, string $noticiaId)
    {
        $noticia = Noticia::find($noticiaId);
        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        $comentarios = Comentario::where('tipoReferencia', 'noticia')
            ->where('referencia_id', $noticiaId)
            ->orderBy('fecha', 'desc')
            ->get();

        if ($comentarios->isEmpty()) {
            return $this->success([], 'La noticia no tiene comentarios', 200);
        }

        $usuarioId = $this->normalizeId($request->query('usuario_id') ?? $request->query('usuarioId') ?? null);
        $reactionSummaries = $this->buildReactionSummaries($comentarios, $usuarioId);

        return $this->success(
            $this->mapComentariosWithUsuarios($comentarios, $reactionSummaries),
            'Comentarios de la noticia',
            200
        );
    }

    public function storeForNoticia(Request $request, string $noticiaId)
    {
        $request->merge([
            'tipoReferencia' => 'noticia',
            'referencia_id' => $noticiaId,
        ]);

        if (!$request->has('fecha') || empty($request->input('fecha'))) {
            $request->merge([
                'fecha' => now()->toAtomString(),
            ]);
        }

        return $this->store($request);
    }
}
