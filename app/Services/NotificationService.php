<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use MongoDB\BSON\ObjectId;

class NotificationService
{
    public const SUPPORTED_TYPES = ['evento', 'producto', 'noticia'];
    public const DEFAULT_PREFERENCES = ['evento', 'producto', 'noticia'];

    /**
     * Genera una notificación para todos los usuarios habilitados según sus preferencias.
     *
     * @param string $type Tipo de notificación (evento, producto, noticia)
     * @param array{
     *     titulo?: string,
     *     mensaje?: string,
     *     referencia_tipo?: string,
     *     referencia_id?: mixed,
     *     datos?: array<mixed>,
     *     fecha?: \DateTimeInterface|string|null
     * } $payload
     */
    public function notifyUsers(string $type, array $payload): void
    {
        $normalizedType = $this->normalizeType($type);
        if (!$normalizedType) {
            return;
        }

        $titulo = $this->stringify($payload['titulo'] ?? '');
        $mensaje = $this->stringify($payload['mensaje'] ?? '');
        $referenciaTipo = $this->normalizeType($payload['referencia_tipo'] ?? $normalizedType) ?? $normalizedType;
        $referenciaId = $this->normalizeId($payload['referencia_id'] ?? null);
        $datos = $this->normalizeArray($payload['datos'] ?? []);

        $fecha = $payload['fecha'] ?? null;
        $fechaNotificacion = $this->normalizeDate($fecha) ?? Carbon::now();

        $usuarios = $this->eligibleUsersForType($normalizedType);

        /** @var \App\Models\Usuario $usuario */
        foreach ($usuarios as $usuario) {
            $usuarioId = $this->normalizeId($usuario->_id ?? $usuario->id ?? null);
            if (!$usuarioId) {
                continue;
            }

            $attributes = [
                'usuario_id' => $usuarioId,
                'tipo' => $normalizedType,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
                'datos' => json_encode($datos),
                'leida' => false,
                'fecha' => $fechaNotificacion,
            ];

            try {
                $existing = null;
                if ($referenciaId !== null) {
                    $existing = Notificacion::where('usuario_id', $usuarioId)
                        ->where('tipo', $normalizedType)
                        ->where('referencia_id', $referenciaId)
                        ->first();
                }

                if ($existing) {
                    $existing->fill([
                        'titulo' => $titulo ?: $existing->titulo,
                        'mensaje' => $mensaje ?: $existing->mensaje,
                        'datos' => $datos,
                        'leida' => false,
                        'fecha' => $fechaNotificacion,
                    ]);
                    $existing->save();
                } else {
                    Notificacion::create($attributes);
                }
            } catch (\Throwable $exception) {
                Log::warning('No se pudo crear notificación para usuario', [
                    'usuario_id' => $usuarioId,
                    'tipo' => $normalizedType,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }

    /**
     * Devuelve las preferencias normalizadas para un usuario.
     */
    protected function eligibleUsersForType(string $type): Collection
    {
        return Usuario::query()
            ->where(function ($query) {
                $query->whereNull('estado')
                    ->orWhereIn('estado', ['Activo', 'activo']);
            })
            ->get();
    }

    protected function normalizeType($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $normalized = strtolower(trim($value));

        $aliases = [
            'eventos' => 'evento',
            'productos' => 'producto',
            'noticias' => 'noticia',
        ];

        if (array_key_exists($normalized, $aliases)) {
            $normalized = $aliases[$normalized];
        }

        if (!in_array($normalized, self::SUPPORTED_TYPES, true)) {
            return null;
        }

        return $normalized;
    }

    protected function stringify($value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_numeric($value)) {
            return trim((string) $value);
        }

        return '';
    }

    protected function normalizeArray($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return $value;
    }

    protected function normalizeDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value);
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value);
            } catch (\Throwable $exception) {
                return null;
            }
        }

        return null;
    }

    protected function normalizeId($value): ?string
    {
        if ($value instanceof ObjectId) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $stringValue = trim((string) $value);
            return $stringValue === '' ? null : $stringValue;
        }

        if (is_array($value) && isset($value['$oid'])) {
            $stringValue = trim((string) $value['$oid']);
            return $stringValue === '' ? null : $stringValue;
        }

        if (is_string($value) || is_numeric($value)) {
            $stringValue = trim((string) $value);
            return $stringValue === '' ? null : $stringValue;
        }

        return null;
    }
}
