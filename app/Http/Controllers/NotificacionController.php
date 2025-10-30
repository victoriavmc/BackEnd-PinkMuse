<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use MongoDB\BSON\ObjectId;

class NotificacionController
{
    use ApiResponse;

    public function __construct()
    {
        // El constructor puede estar vacío si no hay dependencias a nivel de controlador.
    }

    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para acceder a las notificaciones', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        $limit = (int) ($request->query('limit') ?? 20);
        $limit = max(1, min($limit, 100));

        $soloNoLeidas = $this->isTruthy(
            $request->query('solo_no_leidas') ?? $request->query('onlyUnread') ?? false
        );

        $query = Notificacion::where('usuario_id', $usuarioId)
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc');

        if ($soloNoLeidas) {
            $query->where(function ($inner) {
                $inner->where('leida', false)->orWhereNull('leida');
            });
        }

        $items = $query->limit($limit)->get();

        $unreadCount = Notificacion::where('usuario_id', $usuarioId)
            ->where(function ($inner) {
                $inner->where('leida', false)->orWhereNull('leida');
            })
            ->count();

        return $this->success([
            'items' => $items,
            'unread' => $unreadCount,
        ], 'Notificaciones obtenidas exitosamente', 200);
    }

    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para realizar esta acciA3n', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        $notification = Notificacion::where('_id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notification) {
            return $this->error('NotificaciA3n no encontrada', 404);
        }

        $notification->leida = true;
        $notification->save();

        return $this->success($notification, 'NotificaciA3n marcada como leida', 200);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para realizar esta acciA3n', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        Notificacion::where('usuario_id', $usuarioId)
            ->where(function ($inner) {
                $inner->where('leida', false)->orWhereNull('leida');
            })
            ->update(['leida' => true, 'fecha' => Carbon::now()]);

        return $this->success(null, 'Todas las notificaciones fueron marcadas como leidas', 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'usuario_id' => 'required|string',
            'tipo' => 'required|string|in:evento,producto,noticia',
            'titulo' => 'required|string|max:150',
            'mensaje' => 'required|string|max:500',
            'referencia_tipo' => 'nullable|string|in:evento,producto,noticia',
            'referencia_id' => 'nullable|string',
            'datos' => 'nullable|array',
            'fecha' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validaciA3n', 400, $validator->errors());
        }

        $payload = $validator->validated();
        $payload['leida'] = false;

        if (isset($payload['fecha'])) {
            $payload['fecha'] = Carbon::parse($payload['fecha']);
        } else {
            $payload['fecha'] = Carbon::now();
        }

        $notificacion = Notificacion::create($payload);

        if (!$notificacion) {
            return $this->error('No se pudo crear la notificaciA3n', 500);
        }

        return $this->success($notificacion, 'NotificaciA3n creada exitosamente', 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para acceder a esta notificaciA3n', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        $notificacion = Notificacion::where('_id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notificacion) {
            return $this->error('NotificaciA3n no encontrada', 404);
        }

        return $this->success($notificacion, 'NotificaciA3n obtenida correctamente', 200);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para actualizar una notificaciA3n', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        $notificacion = Notificacion::where('_id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notificacion) {
            return $this->error('NotificaciA3n no encontrada', 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|string|max:150',
            'mensaje' => 'sometimes|string|max:500',
            'datos' => 'sometimes|array',
            'leida' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validaciA3n', 400, $validator->errors());
        }

        $notificacion->fill($validator->validated());
        $notificacion->save();

        return $this->success($notificacion, 'NotificaciA3n actualizada exitosamente', 200);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();

        if (!$user) {
            return $this->error('Debe autenticarse para eliminar una notificaciA3n', 401);
        }

        $usuarioId = $this->normalizeId($user->_id ?? $user->id ?? null);
        if (!$usuarioId) {
            return $this->error('No se pudo identificar al usuario autenticado', 500);
        }

        $notificacion = Notificacion::where('_id', $id)
            ->where('usuario_id', $usuarioId)
            ->first();

        if (!$notificacion) {
            return $this->error('NotificaciA3n no encontrada', 404);
        }

        $notificacion->delete();

        return $this->success(null, 'NotificaciA3n eliminada exitosamente', 200);
    }

    protected function normalizeId($value): ?string
    {
        if ($value instanceof ObjectId) {
            return (string) $value;
        }

        if (is_array($value) && isset($value['$oid'])) {
            $stringValue = (string) $value['$oid'];
            return $stringValue !== '' ? $stringValue : null;
        }

        if (is_object($value) && isset($value->{'$oid'})) {
            $stringValue = (string) $value->{'$oid'};
            return $stringValue !== '' ? $stringValue : null;
        }

        if (is_string($value) || is_numeric($value)) {
            $stringValue = trim((string) $value);
            return $stringValue !== '' ? $stringValue : null;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            $stringValue = trim((string) $value);
            return $stringValue !== '' ? $stringValue : null;
        }

        return null;
    }

    protected function isTruthy($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
    }
}
