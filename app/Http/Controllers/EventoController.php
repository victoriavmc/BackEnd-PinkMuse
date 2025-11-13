<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Evento;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\ImageService;
use Illuminate\Support\Facades\Auth;
// Importar el servicio

use function PHPUnit\Framework\isEmpty;

class EventoController
{
    use ApiResponse;
    public $usuario;

    protected NotificationService $notificationService;
    protected ImageService $imageService;

    public function __construct(NotificationService $notificationService, ImageService $imageService)
    {
        $this->usuario = Auth::user();
        $this->notificationService = $notificationService;
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $eventos = Evento::all();

        if ($eventos->isEmpty()) {
            return $this->error("No se encontraron eventos", 404);
        }

        $hoy = now(); // fecha actual (Carbon)

        foreach ($eventos as $evento) {
            $fechaEvento = \Carbon\Carbon::parse($evento->fecha);
            $estadoEventoOriginal = $evento->estado;

            // --- 1. Cambiar estado del evento si ya pasó ---
            if ($fechaEvento->isPast() && $evento->estado !== 'Finalizado') {
                $evento->estado = 'Finalizado';
            }

            // --- 2. Revisar cada entrada ---
            $entradasActualizadas = [];
            if (!empty($evento->entradas) && is_iterable($evento->entradas)) {
                foreach ($evento->entradas as $entrada) {
                    // Si no existe clave 'estado', aseguramos que exista
                    $entrada['estado'] = $entrada['estado'] ?? 'Disponible';

                    if (
                        ($entrada['cantidad'] ?? 0) <= 0 ||
                        $fechaEvento->isPast() ||
                        !in_array($evento->estado, ['Activo', 'Suspendido'])
                    ) {
                        $entrada['estado'] = 'No Disponible';
                    }

                    $entradasActualizadas[] = $entrada;
                }

                // Asignamos el array completo ya modificado
                $evento->entradas = $entradasActualizadas;
            }

            // Guarda solo si hubo cambios
            if ($evento->isDirty(['estado', 'entradas'])) {
                $evento->save();
            }
        }

        return $this->success($eventos, "Eventos actualizados y obtenidos exitosamente", 200);
    }

    public function validatorEvento(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
            $validator = Validator::make($request->all(), [
                'nombreEvento' => 'prohibited',
                'nombreLugar' => 'sometimes|required|string|max:255',
                'direccion' => 'nullable|array',
                'direccion.calle' => 'nullable|string|max:255',
                'direccion.ciudad' => 'nullable|string|max:100',
                'direccion.numero' => 'nullable|integer|min:1',
                'fecha' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        try {
                            // Combinar fecha y hora
                            $hora = $request->hora ?? '00:00';
                            $fechaHoraEvento = \Carbon\Carbon::parse("{$value} {$hora}");

                            if ($fechaHoraEvento->isPast()) {
                                $fail('No se puede crear un evento con fecha y hora anteriores al momento actual.');
                            }
                        } catch (\Exception $e) {
                            $fail('El formato de fecha u hora no es válido.');
                        }
                    },
                ],
                'hora' => 'required|string|max:10',

                'entradas' => 'nullable|array',
                'entradas.*.tipo' => 'required_with:entradas|string|max:100',
                'entradas.*.precio' => 'required_with:entradas|numeric|min:0',
                'entradas.*.cantidad' => 'required_with:entradas|integer|min:0',
                'entradas.*.estado' => 'required_with:entradas|string|max:50',

                'coordenadas' => 'nullable|array',
                'coordenadas.lat' => 'nullable|numeric|between:-90,90',
                'coordenadas.lng' => 'nullable|numeric|between:-180,180',

                'artistasExtras' => 'nullable|array',
                'artistasExtras.*' => 'string|max:255',

                'estado' => 'sometimes|required|string|max:50',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'nombreEvento' => 'required|string|max:255|unique:eventos,nombreEvento',
                'nombreLugar' => 'required|string|max:255',
                'direccion' => 'nullable|array',
                'direccion.calle' => 'nullable|string|max:255',
                'direccion.ciudad' => 'nullable|string|max:100',
                'direccion.numero' => 'nullable|integer|min:1',

                // Fecha + hora de CREADO en adelante
                'fecha' => [
                    'required',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        try {
                            // Combinar fecha y hora
                            $hora = $request->hora ?? '00:00';
                            $fechaHoraEvento = \Carbon\Carbon::parse("{$value} {$hora}");

                            if ($fechaHoraEvento->isPast()) {
                                $fail('No se puede crear un evento con fecha y hora anteriores al momento actual.');
                            }
                        } catch (\Exception $e) {
                            $fail('El formato de fecha u hora no es válido.');
                        }
                    },
                ],
                'hora' => 'required|string|max:10',

                'entradas' => 'required|array',
                'entradas.*.tipo' => 'required|string|max:100',
                'entradas.*.precio' => 'required|numeric|min:0',
                'entradas.*.cantidad' => 'required|integer|min:0',
                'entradas.*.estado' => 'sometimes|string|max:50',

                'coordenadas' => 'nullable|array',
                'coordenadas.lat' => 'nullable|numeric|between:-90,90',
                'coordenadas.lng' => 'nullable|numeric|between:-180,180',

                'artistasExtras' => 'nullable|array',
                'artistasExtras.*' => 'string|max:255',

                'estado' => 'sometimes|string|max:50',
            ]);
        }

        $validator->sometimes('imagenPrincipal', 'image|mimes:jpeg,png,jpg,webp|max:2048', function () use ($request) {
            return $request->hasFile('imagenPrincipal');
        });

        return $validator;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validatorEvento($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        //Verificar que El mismo tipo de entrada, EN un mismo evento no se repita
        if ($request->has('entradas')) {
            $tipos = array_column($request->entradas, 'tipo');
            if (count($tipos) !== count(array_unique($tipos))) {
                return $this->error('Error de validación', 400, 'No puede haber tipos de entrada repetidos en el mismo evento');
            }
        }
        // Normalizar nombre de evento (minúsculas, sin espacios extra)
        $nombreEventoNormalizado = trim(mb_strtolower($request->nombreEvento, 'UTF-8'));

        // Verificar si ya existe un evento con el mismo nombre (ignorando mayúsculas/minúsculas)
        $eventoExistente = Evento::whereRaw([
            'nombreEvento' => [
                '$regex' => '^' . preg_quote($nombreEventoNormalizado) . '$',
                '$options' => 'i'
            ]
        ])->first();

        if ($eventoExistente) {
            return $this->error('Ya existe un evento con ese nombre (sin importar mayúsculas o minúsculas).', 409);
        }

        $imagenPrincipal = $this->sanitizeImagenPrincipal($request->input('imagenPrincipal'));
        if ($request->hasFile('imagenPrincipal')) {
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'evento',
                $request->nombreEvento,
                false,
                0
            );
            $imagenPrincipal = $rutas[0];
        }

        // Crear evento
        $evento = new Evento();
        $evento->nombreEvento = $request->nombreEvento;
        $evento->nombreLugar = $request->nombreLugar;
        $evento->direccion = $request->direccion ?? null;
        $evento->fecha = $request->fecha;
        $evento->hora = $request->hora;

        // Entradas: aseguramos estado según cantidad
        $entradas = collect($request->entradas)->map(function ($entrada) {
            $cantidad = $entrada['cantidad'] ?? 0;
            $entrada['estado'] = $cantidad > 0 ? 'Disponible' : 'No disponible';
            return $entrada;
        })->toArray();
        $evento->entradas = $entradas;

        // Estado del evento por defecto
        $evento->estado = 'Activo';
        $evento->imagenPrincipal = $imagenPrincipal;
        $evento->coordenadas = $request->coordenadas ?? null;
        $evento->artistasExtras = $request->artistasExtras ?? null;


        $evento->save();

        if (!$evento) {
            return $this->error('Error al crear el evento', 500);
        }

        $this->notificationService->notifyUsers('evento', [
            'titulo' => 'Nuevo evento',
            'mensaje' => sprintf(
                'Se agrego el evento "%s" para el %s.',
                $evento->nombreEvento,
                $this->formatEventDate($evento->fecha)
            ),
            'referencia_tipo' => 'evento',
            'referencia_id' => $evento->_id ?? $evento->id ?? null,
            'datos' => [
                'nombre' => $evento->nombreEvento,
                'fecha' => $evento->fecha,
                'hora' => $evento->hora,
                'imagen' => $evento->imagenPrincipal,
                'link' => '/eventos/' . rawurlencode((string) $evento->nombreEvento),
            ],
            'fecha' => Carbon::now(),
        ]);

        return $this->success($evento, 'Evento creado exitosamente', 201);
    }

    protected function formatEventDate($value): string
    {
        if (!$value) {
            return 'próximamente';
        }

        try {
            return Carbon::parse($value)->format('d/m/Y');
        } catch (\Throwable $exception) {
            return is_string($value) ? $value : 'próximamente';
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombreEvento)
    {
        //
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();
        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }
        return $this->success($evento, 'Evento encontrado exitosamente', 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nombreEvento)
    {
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();
        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        $validator = $this->validatorEvento($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors()->first());
        }

        $data = $validator->validated();
        $imagenPrincipalInput = $this->sanitizeImagenPrincipal($request->input('imagenPrincipal'));

        if ($request->hasFile('imagenPrincipal')) {
            // 1. Eliminar la imagen anterior
            $this->imageService->eliminar($evento->imagenPrincipal);

            // 2. Guardar la nueva
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'evento',
                $evento->nombreEvento,
                false,
                0
            );

            // 3. Asignar la nueva ruta a los datos validados
            $data['imagenPrincipal'] = $rutas[0];
        } elseif ($request->exists('imagenPrincipal')) {
            if ($imagenPrincipalInput === null && $evento->imagenPrincipal) {
                $this->imageService->eliminar($evento->imagenPrincipal);
            }
            $data['imagenPrincipal'] = $imagenPrincipalInput;
        }


        // === Actualizar entradas ===
        if ($request->has('entradas')) {

            $entradasNuevas = $request->input('entradas', []);
            $eventoEsActivo = isset($data['estado']) && $data['estado'] === 'Activo';

            // === 1. Evento NO activo → todo No Disponible ===
            if (!$eventoEsActivo) {
                $data['entradas'] = collect($entradasNuevas)->map(function ($entrada) {
                    return [
                        'tipo'     => $entrada['tipo'],
                        'precio'   => $entrada['precio'] ?? 0,
                        'cantidad' => $entrada['cantidad'] ?? 0,
                        'estado'   => 'No Disponible',
                    ];
                })->toArray();
            } else {
                // === Evento ACTIVO ===

                // Validar tipos duplicados
                $tipos = array_column($entradasNuevas, 'tipo');
                if (count($tipos) !== count(array_unique($tipos))) {
                    return $this->error('Error de validación', 400, 'No puede haber tipos de entrada repetidos en el mismo evento');
                }

                $entradasFinales = [];

                foreach ($entradasNuevas as $entrada) {

                    $cantidad = $entrada['cantidad'] ?? 0;
                    $estadoUsuario = strtolower($entrada['estado'] ?? 'Disponible');

                    // === Regla: cantidad = 0 → siempre No Disponible ===
                    if ($cantidad == 0) {
                        $estado = 'No Disponible';
                    } else {
                        // === cantidad > 0 ===
                        $estado = 'Disponible';
                    }

                    $entradasFinales[] = [
                        'tipo'     => $entrada['tipo'],
                        'precio'   => $entrada['precio'] ?? 0,
                        'cantidad' => $cantidad,
                        'estado'   => $estado,
                    ];
                }

                $data['entradas'] = $entradasFinales;
            }
        }


        // === Actualizar el evento ===
        $evento->update($data);

        return $this->success($evento, 'Evento actualizado exitosamente', 200);
    }

    public function subirImagen(Request $request, string $nombreEvento)
    {
        $validator = Validator::make($request->all(), [
            'imagen' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $evento = Evento::where('nombreEvento', $nombreEvento)->first();
        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        // Eliminar la imagen anterior si existe
        if ($evento->imagenPrincipal) {
            $this->imageService->eliminar($evento->imagenPrincipal);
        }

        // Guardar la nueva imagen
        $rutas = $this->imageService->guardar(
            $request->file('imagen'),
            'evento',
            $evento->nombreEvento, // Usar el nombre del evento como base para el nombre del archivo
            false,
            0
        );

        // Actualizar el campo en la base de datos
        $evento->imagenPrincipal = $rutas[0];
        $evento->save();

        return $this->success([
            'message' => 'Imagen subida exitosamente',
            'ruta_de_la_imagen' => $evento->imagenPrincipal
        ], 'Imagen subida', 200);
    }

    private function sanitizeImagenPrincipal($value)
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '' || strtolower($trimmed) === 'null') {
                return null;
            }
            return $trimmed;
        }

        if (is_array($value)) {
            $allowedKeys = ['png', 'webp', 'principal'];
            $filtered = array_intersect_key($value, array_flip($allowedKeys));
            return !empty($filtered) ? $filtered : null;
        }

        return null;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $nombreEvento)
    {
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();

        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        // Si se envía 'tipo', borramos solo esa entrada (lógica existente)
        if ($request->has('tipo')) {
            $tipo = $request->input('tipo');
            $entradasActuales = $evento->entradas ?? [];

            $entradasFiltradas = array_filter($entradasActuales, function ($entrada) use ($tipo) {
                return $entrada['tipo'] !== $tipo;
            });

            // Si no se encontró la entrada
            if (count($entradasFiltradas) === count($entradasActuales)) {
                return $this->error("La entrada '{$tipo}'  no existe en este evento", 404);
            }

            $evento->entradas = array_values($entradasFiltradas); // reindexar
            $evento->save();

            return $this->success(null, "Entrada '{$tipo}' eliminada exitosamente", 200);
        }

        // Si no se envía 'tipoEntrada', borramos todo el evento
        try {
            // 1. Eliminar la imagen principal (si existe)
            $this->imageService->eliminar($evento->imagenPrincipal);

            // 2. Borrar el evento de la BD
            $evento->delete();

            return $this->success(null, 'Evento eliminado exitosamente', 204); // 204 No Content

        } catch (\Exception $e) {
            return $this->error('Error al eliminar el evento', 500, $e->getMessage());
        }
    }

    // Generar solicitud de compra de entradas para un evento
    public function generarSolicitudCompra(Request $request, string $nombreEvento)
    {
        // Buscar evento
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();

        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        // Verificar estado (si no querés permitir "Suspendido", cambiá acá)
        if (!in_array($evento->estado, ['Activo', 'Suspendido'])) {
            return $this->error('El evento no está disponible para compras', 400);
        }

        // Validar usuario autenticado
        $usuario = $request->user();
        if (!$usuario) {
            return $this->error('Usuario no autenticado', 401);
        }

        // Validación de solicitud
        $validator = Validator::make($request->all(), [
            'entradas' => 'required|array|min:1',
            'entradas.*.tipo' => 'required|string',
            'entradas.*.cantidad' => 'required|integer|min:1',
            'metodo' => 'nullable|string|in:mercadopago,tarjeta,efectivo,transferencia',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 422, $validator->errors());
        }

        // Asegurar que entradas del evento estén bien formadas
        $entradasEvento = collect(is_array($evento->entradas) ? $evento->entradas : []);

        if ($entradasEvento->isEmpty()) {
            return $this->error('El evento no tiene entradas configuradas para la venta.', 500);
        }

        $entradasSolicitadas = $request->input('entradas');
        $productos = [];
        $total = 0;

        foreach ($entradasSolicitadas as $entradaReq) {
            $tipo = $entradaReq['tipo'];
            $cantidad = (int) $entradaReq['cantidad'];

            $entradaEvento = $entradasEvento->firstWhere('tipo', $tipo);
            if (!$entradaEvento) {
                return $this->error("No existe una entrada de tipo '{$tipo}' para este evento", 404);
            }

            if (($entradaEvento['cantidad'] ?? 0) < $cantidad) {
                return $this->error("No hay suficientes entradas disponibles para '{$tipo}'", 400);
            }

            $precio = (float) ($entradaEvento['precio'] ?? 0);
            $subtotal = $precio * $cantidad;
            $total += $subtotal;

            $productos[] = [
                'tipoReferencia'  => 'evento',
                'referencia_id'   => $evento->_id ?? $evento->id ?? null,
                'nombreEvento'    => $evento->nombreEvento,
                'tipoEntrada'     => $tipo,
                'cantidad'        => $cantidad,
                'precioUnitario'  => $precio,
                'subtotal'        => $subtotal,
            ];
        }

        // Devolver solicitud de compra generada
        return $this->success([
            'productos'        => $productos,
            'total'            => $total,
            'mensaje'          => 'Solicitud de compra generada exitosamente',
        ], 201);
    }

    // PAGO
    public function generarCompra(Request $request, string $nombreEvento)
    {
        // Buscar evento
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();

        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        if (!in_array($evento->estado, ['Activo', 'Suspendido'])) {
            return $this->error('El evento no está disponible para compras', 400);
        }

        // Validar usuario autenticado
        $usuario = $request->user();
        if (!$usuario) {
            return $this->error('Usuario no autenticado', 401);
        }

        // Normalizar posibles arrays en la entrada
        $input = $request->all();

        if (isset($input['productos']) && is_array($input['productos'])) {
            foreach ($input['productos'] as &$producto) {
                foreach (['tipoReferencia', 'referencia_id', 'tipoEntrada'] as $campo) {
                    if (isset($producto[$campo]) && is_array($producto[$campo])) {
                        $producto[$campo] = $producto[$campo][0] ?? null;
                    }
                }
            }
            unset($producto);
        }

        if (isset($input['datosPago']['metodo']) && is_array($input['datosPago']['metodo'])) {
            $input['datosPago']['metodo'] = $input['datosPago']['metodo'][0] ?? null;
        }

        $request->replace($input);

        // Validación
        $validator = Validator::make($request->all(), [
            'productos' => 'required|array|min:1',
            'productos.*.tipoReferencia' => 'required|string|in:evento,otro',
            'productos.*.referencia_id' => 'required|string|max:255',
            'productos.*.tipoEntrada' => [
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/productos\.(\d+)\./', $attribute, $matches);
                    $index = $matches[1] ?? null;
                    $tipoReferencia = $request->input("productos.$index.tipoReferencia");
                    if ($tipoReferencia === 'evento' && empty($value)) {
                        $fail("El campo tipoEntrada es obligatorio cuando tipoReferencia es 'evento'.");
                    }
                },
            ],
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precioUnitario' => 'required|numeric|min:0',
            'productos.*.subtotal' => 'required|numeric|min:0',

            'datosPago' => 'required|array',
            'datosPago.metodo' => 'required|string|in:mercadopago,tarjeta,efectivo,transferencia',
            'datosPago.idTransaccion' => 'sometimes|string|max:255',

            'datosPago.detalles' => 'nullable|array',
            'datosPago.detalles.nombre' => 'sometimes|string|max:255',
            'datosPago.detalles.apellido' => 'sometimes|string|max:255',
            'datosPago.detalles.email' => 'sometimes|email|max:255',
            'datosPago.detalles.dni' => 'sometimes|string|max:255',
            'datosPago.detalles.telefono' => 'sometimes|string|max:255',

            'estado' => 'required|string|in:Pendiente,Completado,Cancelado',
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validación', 422, $validator->errors());
        }

        $productos = $request->input('productos', []);
        $metodoPago = $request->input('datosPago.metodo');
        $estado = $request->input('estado');

        // Asegurar formato correcto de entradas del evento
        $entradasEvento = collect(is_array($evento->entradas) ? $evento->entradas : []);

        // Si hay productos de tipo evento pero el evento no tiene entradas configuradas
        if (
            $entradasEvento->isEmpty() &&
            collect($productos)->contains(fn($p) => $p['tipoReferencia'] === 'evento')
        ) {
            return $this->error('El evento no tiene entradas configuradas para la venta.', 500);
        }

        // Validar precios reales y disponibilidad
        $consumoEntradasPorTipo = [];
        foreach ($productos as $index => &$producto) {
            $tipoReferencia = $producto['tipoReferencia'];
            $cantidad = (int) $producto['cantidad'];

            if ($tipoReferencia === 'evento') {
                $tipoEntrada = $producto['tipoEntrada'] ?? null;

                $entradaEvento = $entradasEvento->firstWhere('tipo', $tipoEntrada);
                if (!$entradaEvento) {
                    return $this->error("No existe una entrada de tipo '{$tipoEntrada}' para este evento.", 404);
                }

                if (($entradaEvento['cantidad'] ?? 0) < $cantidad) {
                    return $this->error("No hay suficientes entradas disponibles para '{$tipoEntrada}'.", 400);
                }

                // Precio forzado desde el evento (evita manipulación del cliente)
                $precio = (float) ($entradaEvento['precio'] ?? 0);
                $producto['precioUnitario'] = $precio;
                $producto['subtotal'] = $precio * $cantidad;
                $producto['referencia_id'] = $producto['referencia_id']
                    ?? ($evento->_id ?? $evento->id ?? null);

                $consumoEntradasPorTipo[$tipoEntrada] = ($consumoEntradasPorTipo[$tipoEntrada] ?? 0) + $cantidad;
            } else {
                // Verificar coherencia precio * cantidad
                $precio = (float) $producto['precioUnitario'];
                $subtotalEsperado = $precio * $cantidad;
                $subtotalRecibido = (float) $producto['subtotal'];

                if (abs($subtotalEsperado - $subtotalRecibido) > 0.01) {
                    return $this->error(
                        "Inconsistencia en el subtotal del producto #{$index}: se esperaba {$subtotalEsperado} y se recibió {$subtotalRecibido}.",
                        422
                    );
                }
            }
        }
        unset($producto);

        // Calcular total final
        $total = collect($productos)->sum(fn($item) => (float) $item['subtotal']);

        // Crear comprobante
        $numeroComprobante = 'CMP-' . strtoupper(uniqid());

        $compra = new Comprobante();
        $compra->usuario_id = $usuario->_id ?? $usuario->id ?? null;
        $compra->numero = $numeroComprobante;
        $compra->productos = $productos;
        $compra->total = $total;
        $compra->datosPago = $request->input('datosPago');
        $compra->estado = $estado;
        $compra->fechaCreacion = now();
        $compra->save();

        // Actualizar stock
        $entradasActualizadas = $entradasEvento->map(function ($entrada) use ($consumoEntradasPorTipo) {
            $tipo = $entrada['tipo'] ?? null;
            if ($tipo && isset($consumoEntradasPorTipo[$tipo])) {
                $entrada['cantidad'] = max(0, ($entrada['cantidad'] ?? 0) - $consumoEntradasPorTipo[$tipo]);
                if ($entrada['cantidad'] === 0) {
                    $entrada['estado'] = 'No disponible';
                }
            }
            return $entrada;
        })->toArray();

        $evento->entradas = $entradasActualizadas;
        $evento->save();

        // Enviar notificación (sin interrumpir el flujo si falla)
        try {
            $this->notificationService->notifyUsers('compra', [
                'titulo' => 'Compra registrada',
                'mensaje' => sprintf(
                    'Tu compra #%s para el evento "%s" se registró correctamente. Total: $%s.',
                    $compra->numero,
                    $evento->nombreEvento,
                    number_format($compra->total, 2)
                ),
                'referencia_tipo' => 'compra',
                'referencia_id' => $compra->_id ?? $compra->id ?? null,
                'datos' => [
                    'numero' => $compra->numero,
                    'total' => $compra->total,
                    'metodo_pago' => $metodoPago,
                    'evento' => [
                        'nombre' => $evento->nombreEvento,
                        'fecha'  => $evento->fecha,
                        'hora'   => $evento->hora,
                        'imagen' => $evento->imagenPrincipal,
                        'link'   => '/eventos/' . rawurlencode((string) $evento->nombreEvento),
                    ],
                ],
                'usuario_id' => $usuario->_id ?? $usuario->id ?? null,
                'fecha' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Error al enviar notificación de compra: ' . $e->getMessage());
        }

        return $this->success($compra, 'Compra generada exitosamente', 201);
    }
}
