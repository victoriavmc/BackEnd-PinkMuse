<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Evento;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use App\Services\ImageService; // Importar el servicio
use PhpParser\Node\Expr\Empty_;

use function PHPUnit\Framework\isEmpty;

class EventoController
{
    use ApiResponse;

    protected NotificationService $notificationService;
    protected ImageService $imageService;

    public function __construct(NotificationService $notificationService, ImageService $imageService)
    {
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
        return $this->success($eventos, "Eventos obtenidos exitosamente", 200);
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
                'fecha' => 'sometimes|required|date',
                'hora' => 'sometimes|required|string|max:10',
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
                'fecha' => 'required|date',
                'hora' => 'required|string|max:10',
                'entradas' => 'required|array',
                'entradas.*.tipo' => 'required|string|max:100',
                'entradas.*.precio' => 'required|numeric|min:0',
                'entradas.*.cantidad' => 'required|integer|min:0',
                'entradas.*.estado' => 'required|string|max:50',
                'coordenadas' => 'nullable|array',
                'coordenadas.lat' => 'nullable|numeric|between:-90,90',
                'coordenadas.lng' => 'nullable|numeric|between:-180,180',
                'artistasExtras' => 'nullable|array',
                'artistasExtras.*' => 'string|max:255',
                'estado' => 'required|string|max:50',
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

        if (Evento::where('nombreEvento', $request->nombreEvento)->exists()) {
            return $this->error('El evento ya existe', 409,);
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

        $evento = new Evento();
        $evento->nombreEvento = $request->nombreEvento;
        $evento->nombreLugar = $request->nombreLugar;
        $evento->direccion = $request->direccion ?? null;
        $evento->fecha = $request->fecha;
        $evento->hora = $request->hora;
        $evento->entradas = $request->entradas;
        $evento->estado = $request->estado;
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
                'Se agregA3 el evento "%s" para el %s.',
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

        // Lógica de actualización de entradas (existente)
        if ($request->has('entradas')) {
            $entradasActuales = $evento->entradas ?? [];
            $tiposActuales = array_column($entradasActuales, 'tipo');
            $tiposNuevos = [];

            foreach ($request->entradas as $entradaNueva) {
                $tipo = $entradaNueva['tipo'];

                // Verificar duplicado dentro del request
                if (in_array($tipo, $tiposNuevos)) {
                    return $this->error("El tipo de entrada '{$tipo}' se repite en la solicitud", 400,);
                }
                $tiposNuevos[] = $tipo;

                if (in_array($tipo, $tiposActuales)) {
                    // Actualizar entrada existente
                    foreach ($entradasActuales as &$entradaActual) {
                        if ($entradaActual['tipo'] === $tipo) {
                            $entradaActual['precio'] = $entradaNueva['precio'] ?? $entradaActual['precio'];
                            $entradaActual['cantidad'] = $entradaNueva['cantidad'] ?? $entradaActual['cantidad'];
                            $entradaActual['estado'] = $entradaNueva['estado'] ?? $entradaActual['estado'];
                        }
                    }
                } else {
                    // Agregar nueva entrada
                    $entradasActuales[] = $entradaNueva;
                }
            }
            // Asignar las entradas actualizadas a los datos
            $data['entradas'] = $entradasActuales;
        }

        // Actualizar campos del evento
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
    
    public function guardarComprobanteEvento(Request $request, string $nombreEvento)
    {
        // Saber el evento
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();

        if (!$evento) {
            return $this->error('Evento no encontrado', 404);
        }

        // Solo los eventos Activo o Suspendido permiten compras
        if ($evento->estado !== 'Activo' && $evento->estado !== 'Suspendido') {
            return $this->error('El evento no está disponible para compras', 400);
        }

        // Saber el usuario que compra
        $usuario = $request->user();
        if (!$usuario) {
            return $this->error('Usuario no autenticado', 401);
        }

        // Validar tipo de entrada
        if (!$request->has('tipo')) {
            return $this->error('Debe especificar el tipo de entrada', 400);
        }

        // Obtener tipo y cantidad
        $tipo = $request->input('tipo');
        $cantidadSolicitada = (int) $request->input('cantidad', 1);

        // Buscar la entrada correspondiente
        $entradaSeleccionada = collect($evento->entradas)->firstWhere('tipo', $tipo);
        if (!$entradaSeleccionada) {
            return $this->error("La entrada '{$tipo}' no existe en este evento", 404);
        }

        // Verificar stock disponible
        if ($entradaSeleccionada['cantidad'] < $cantidadSolicitada) {
            return $this->error("No hay suficientes entradas disponibles para '{$tipo}'", 400);
        }

        // MP
        

        // Calcular total
        $total = $entradaSeleccionada['precio'] * $cantidadSolicitada;

        // Crear número único de comprobante
        $numeroComprobante = strtoupper(uniqid('CMP-'));

        // Registrar el comprobante
        $comprobante = Comprobante::create([
            'numeroComprobante' => $numeroComprobante,
            'fecha' => now(),
            'usuario_id' => $usuario->id,
            'datosPago' => [
                'metodo' => $request->input('metodo', 'tarjeta'),
                'estado' => 'pagado'
            ],
            'productos' => [
                [
                    'evento' => $evento->nombreEvento,
                    'tipoEntrada' => $tipo,
                    'cantidad' => $cantidadSolicitada,
                    'precioUnitario' => $entradaSeleccionada['precio']
                ]
            ],
            'total' => $total,
            'estado' => 'activo' 
        ]);

        // Actualizar stock de entradas
        foreach ($evento->entradas as &$entrada) {
            if ($entrada['tipo'] === $tipo) {
                $entrada['cantidad'] -= $cantidadSolicitada;
            }
        }
        $evento->save();

        return $this->success($comprobante, "Comprobante generado correctamente para la entrada '{$tipo}'", 200);
    }

}