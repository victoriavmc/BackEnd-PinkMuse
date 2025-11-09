<?php

namespace App\Http\Controllers;

use App\Models\Comentario;
use App\Models\Noticia;
use App\Models\Reaccion;
use App\Models\Usuario;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ReaccionController
{
    use ApiResponse;
    public $usuario;

    public function __construct()
    {
        $this->usuario = Auth::user();
    }
    protected array $reactionTypes = ['like', 'love', 'wow', 'angry', 'dislike'];

    protected function normalizeId($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) || is_int($value) || is_float($value)) {
            $string = trim((string) $value);
            return $string === '' ? null : $string;
        }

        if (is_array($value)) {
            if (isset($value['$oid'])) {
                return $this->normalizeId($value['$oid']);
            }
            if (isset($value['_id'])) {
                return $this->normalizeId($value['_id']);
            }
        }

        if (is_object($value)) {
            if (isset($value->{'$oid'})) {
                return $this->normalizeId($value->{'$oid'});
            }
            if (isset($value->_id)) {
                return $this->normalizeId($value->_id);
            }
            if (method_exists($value, '__toString')) {
                $string = trim((string) $value);
                return $string === '' ? null : $string;
            }
        }

        return null;
    }

    protected function findUsuario(string $usuarioId): ?Usuario
    {
        $user = Usuario::find($usuarioId);

        if ($user) {
            return $user;
        }

        return Usuario::where('id', $usuarioId)->first();
    }

    protected function findReferencia(string $tipo, string $referenciaId)
    {
        return match ($tipo) {
            'noticia' => $this->findNoticia($referenciaId),
            'comentario' => Comentario::find($referenciaId),
            default => null,
        };
    }

    protected function findNoticia(string $identifier): ?Noticia
    {
        $noticia = Noticia::find($identifier);
        if ($noticia) {
            return $noticia;
        }

        $noticia = Noticia::where('titulo', $identifier)->first();
        if ($noticia) {
            return $noticia;
        }

        return Noticia::where('_id', $identifier)->first();
    }

    protected function buildSummary(string $tipoReferencia, string $referenciaId, ?string $usuarioId = null): array
    {
        $counts = [];
        foreach ($this->reactionTypes as $type) {
            $counts[$type] = 0;
        }

        $match = [
            'tipoReferencia' => $tipoReferencia,
            'referencia_id' => $referenciaId,
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
            $reacciones = Reaccion::where('tipoReferencia', $tipoReferencia)
                ->where('referencia_id', $referenciaId)
                ->get(['tipo']);

            foreach ($reacciones as $reaccion) {
                $tipo = $reaccion->tipo;
                if (isset($counts[$tipo])) {
                    $counts[$tipo]++;
                }
            }
        }

        $userReaction = null;
        $normalizedUserId = $usuarioId ? $this->normalizeId($usuarioId) : null;

        if ($normalizedUserId) {
            $userReaction = Reaccion::where('tipoReferencia', $tipoReferencia)
                ->where('referencia_id', $referenciaId)
                ->where('usuario_id', $normalizedUserId)
                ->value('tipo');

            if (!is_string($userReaction) || !in_array($userReaction, $this->reactionTypes, true)) {
                $userReaction = null;
            }
        }

        return [
            'counts' => $counts,
            'total' => array_sum($counts),
            'userReaction' => $userReaction,
        ];
    }
    public function summaryForNoticia(Request $request, string $noticiaId)
    {
        $normalizedId = $this->normalizeId($noticiaId) ?? $noticiaId;
        $noticia = $this->findNoticia($normalizedId);

        if (!$noticia) {
            return $this->error('Noticia no encontrada', 404);
        }

        $usuarioId = $request->query('usuario_id') ?? $request->query('usuarioId');
        $usuarioId = $this->normalizeId($usuarioId);

        $summary = $this->buildSummary('noticia', $this->normalizeId($noticia->_id ?? $noticia->id ?? $normalizedId) ?? $normalizedId, $usuarioId);

        return $this->success($summary, 'Resumen de reacciones de la noticia');
    }

    public function summaryForComentario(Request $request, string $comentarioId)
    {
        $normalizedId = $this->normalizeId($comentarioId) ?? $comentarioId;
        $comentario = Comentario::find($normalizedId);

        if (!$comentario) {
            return $this->error('Comentario no encontrado', 404);
        }

        $usuarioId = $request->query('usuario_id') ?? $request->query('usuarioId');
        $usuarioId = $this->normalizeId($usuarioId);

        $summary = $this->buildSummary('comentario', $this->normalizeId($comentario->_id ?? $comentario->id ?? $normalizedId) ?? $normalizedId, $usuarioId);

        return $this->success($summary, 'Resumen de reacciones del comentario');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => 'required|string|in:' . implode(',', $this->reactionTypes),
            'tipoReferencia' => 'required|string|in:noticia,comentario',
            'referencia_id' => 'required',
            'usuario_id' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validacion', 422, $validator->errors());
        }

        $tipo = $request->input('tipo');
        $tipoReferencia = $request->input('tipoReferencia');
        $referenciaId = $this->normalizeId($request->input('referencia_id'));
        $usuarioId = $this->normalizeId($request->input('usuario_id'));

        if (!$referenciaId) {
            return $this->error('El identificador de la referencia es invalido', 422);
        }

        if (!$usuarioId) {
            return $this->error('El identificador del usuario es invalido', 422);
        }

        $usuario = $this->findUsuario($usuarioId);
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        $referencia = $this->findReferencia($tipoReferencia, $referenciaId);
        if (!$referencia) {
            $entity = $tipoReferencia === 'noticia' ? 'Noticia' : 'Comentario';
            return $this->error($entity . ' no encontrado', 404);
        }

        $existing = Reaccion::where('tipoReferencia', $tipoReferencia)
            ->where('referencia_id', $referenciaId)
            ->where('usuario_id', $usuarioId)
            ->first();

        $status = 200;
        $message = 'Reaccion aplicada';
        $currentReaction = $tipo;

        if ($existing && $existing->tipo === $tipo) {
            $existing->delete();
            $currentReaction = null;
            $message = 'Reaccion eliminada';
        } elseif ($existing) {
            $existing->tipo = $tipo;
            $existing->save();
            $message = 'Reaccion actualizada';
        } else {
            Reaccion::create([
                'tipo' => $tipo,
                'tipoReferencia' => $tipoReferencia,
                'referencia_id' => $referenciaId,
                'usuario_id' => $usuarioId,
            ]);
            $status = 201;
            $message = 'Reaccion registrada';
        }

        $summary = $this->buildSummary($tipoReferencia, $referenciaId, $usuarioId);

        return $this->success(
            [
                'summary' => $summary,
                'userReaction' => $summary['userReaction'],
            ],
            $message,
            $status
        );
    }
}
