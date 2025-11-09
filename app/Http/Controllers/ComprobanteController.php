<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Evento;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ComprobanteController
{
    use ApiResponse;

    public $usuario;

    public function __construct()
    {
        $this->usuario = Auth::user();
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $comprobantes = Comprobante::all();
        if ($comprobantes->isEmpty()) {
            return $this->error("No se encontraron comprobantes", 404);
        }
        return $this->success($comprobantes, 'Comprobantes encontrados con exito', 200);
    }

    public function crearDesdePagoMP(Request $request)
    {
        Log::info('📥 Request recibido para crear comprobante desde pago MP:', $request->all());

        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $accessToken = env('MERCADOPAGO_ACCESS_TOKEN');
            $paymentId = $request->payment_id;

            // 🔹 Consultar información del pago en MercadoPago
            $response = Http::withToken($accessToken)
                ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

            if ($response->failed()) {
                Log::error("❌ No se pudo obtener el pago desde MercadoPago", [
                    'response' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener información del pago desde MercadoPago',
                ], 400);
            }

            $paymentData = $response->json();
            Log::info('✅ Datos obtenidos de MercadoPago:', $paymentData);

            // 🔹 Validar estado del pago
            if (($paymentData['status'] ?? null) !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'El pago no está aprobado',
                    'status' => $paymentData['status'] ?? 'desconocido',
                ], 400);
            }

            // 🔹 Extraer información del pagador
            $payer = $paymentData['payer'] ?? [];
            $identification = $payer['identification'] ?? [];

            // 🔹 Extraer método y tipo de pago
            $paymentMethod = $paymentData['payment_method']['id'] ?? $paymentData['payment_method_id'] ?? 'desconocido';
            $paymentType = $paymentData['payment_type_id'] ?? 'desconocido';

            // 🔹 Extraer los productos reales desde MercadoPago
            $productos = [];
            if (!empty($paymentData['additional_info']['items'])) {
                foreach ($paymentData['additional_info']['items'] as $item) {
                    $productos[] = [
                        'tipoReferencia' => 'evento',
                        'tipoEntrada' => $item['title'] ?? 'Sin título',
                        'cantidad' => $item['quantity'] ?? 1,
                        'precioUnitario' => $item['unit_price'] ?? 0,
                        'subtotal' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                    ];
                }
            }

            // ✅ Cambiado: obtener correctamente el nombre del evento
            $nombreEvento = $paymentData['description']
                ?? ($paymentData['additional_info']['title'] ?? 'Evento sin nombre');

            // 🔹 Preparar datos del pago
            $datosPago = [
                'nombre' => explode('@', $payer['email'] ?? 'usuario@test.com')[0],
                'apellido' => 'N/A',
                'email' => $payer['email'] ?? 'usuario@test.com',
                'dni' => str_pad((string)($identification['number'] ?? '00000000'), 8, '0', STR_PAD_LEFT),
                'telefono' => '0000000000000',
                'idTransaccion' => (string)($paymentData['id'] ?? ''),
                'metodoPago' => strtoupper($paymentMethod),
                'tipoPago' => strtoupper($paymentType),
                'descripcion' => $nombreEvento, // ✅ usa el nombre del evento real
                'moneda' => $paymentData['currency_id'] ?? 'ARS',
                'total_pagado' => $paymentData['transaction_details']['total_paid_amount'] ?? 0,
            ];

            // 🔹 Crear número de comprobante
            $numeroComprobante = 'CB' . now()->format('YmdHis') . rand(1000, 9999);

            // 🔹 Crear un nuevo request para store()
            $storeRequest = new Request([
                'numeroComprobante' => $numeroComprobante,
                'fecha' => now()->toDateString(),
                'datosPago' => $datosPago,
                'productos' => $productos,
                'estado' => 'emitida',
            ]);

            Log::info('📄 Enviando a store() con:', $storeRequest->all());

            return $this->store($storeRequest);
        } catch (\Exception $e) {
            Log::error('❌ Error en crearDesdePagoMP: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el pago',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $data = $request->all();

        // Si "productos" viene como objeto (no array), lo convertimos en un array
        if (isset($data['productos']) && !is_array($data['productos'])) {
            $data['productos'] = [$data['productos']];
        }

        // Si viene como colección vacía o string JSON, lo convertimos correctamente
        if (is_string($data['productos'])) {
            $decoded = json_decode($data['productos'], true);
            if ($decoded) {
                $data['productos'] = is_array($decoded) ? $decoded : [$decoded];
            }
        }

        $validator = Validator::make($request->all(), [
            'numeroComprobante' => 'required|string|unique:comprobantes,numeroComprobante',
            'fecha' => 'required|date',
            'datosPago' => 'required|array',
            'datosPago.nombre' => 'required|string',
            'datosPago.apellido' => 'nullable|string',
            'datosPago.email' => 'required|email',
            'datosPago.dni' => 'required|string|min:7|max:10',
            'datosPago.telefono' => 'nullable|string',
            'datosPago.idTransaccion' => 'required|string',
            'datosPago.metodoPago' => 'required|string|max:50',
            'datosPago.tipoPago' => 'nullable|string|max:50',
            'datosPago.moneda' => 'nullable|string|max:10',
            'datosPago.total_pagado' => 'nullable|numeric',
            'estado' => 'required|string|in:emitida,pendiente,cancelada',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            $idTransaccion = $request->datosPago['idTransaccion'];

            // 🔹 Evita duplicados (si ya se guardó este pago antes)
            if (Comprobante::where('datosPago->idTransaccion', $idTransaccion)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => "El ID de transacción {$idTransaccion} ya existe",
                ], 409);
            }

            // 🔹 Si MercadoPago no tiene productos en metadata, se guarda vacío
            $productos = $request->productos ?? [];

            // 🔹 Calcular total automáticamente si viene en datosPago
            $total = $request->datosPago['total_pagado'] ?? 0;

            $comprobanteData = [
                'numeroComprobante' => $request->numeroComprobante,
                'fecha' => $request->fecha,
                'datosPago' => $request->datosPago,
                'productos' => $productos,
                'total' => $total,
                'estado' => $request->estado ?? 'emitida',
            ];

            $comprobante = Comprobante::create($comprobanteData);

            if (!$comprobante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al crear el comprobante',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Comprobante creado exitosamente',
                'data' => $comprobante,
            ], 201);
        } catch (\Exception $e) {
            Log::error('❌ Error en store(): ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el comprobante',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $numeroComprobante)
    {
        //
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            return $this->error('Comprobante no encontrado', 404);
        }

        $validator = Validator::make($request->all(), [
            'numeroComprobante' => 'prohibited',
            'fecha' => 'required|date',
            'usuario_id' => 'prohibited',

            'datosPago' => 'prohibited',
            'datosPago.*' => 'prohibited',

            'productos' => 'sometimes|array|min:1',
            'productos.*.tipoReferencia' => 'sometimes|string|in:producto,evento',
            'productos.*.referencia_id' => 'sometimes|string',
            'productos.*.cantidad' => 'sometimes|integer|min:1',
            'productos.*.tipoEntrada' => 'required_if:productos.*.tipoReferencia,evento|string',

            'productos.*.atributos' => 'nullable|array',
            'productos.*.atributos.tamaño' => 'nullable|string',
            'productos.*.atributos.color' => 'nullable|string',

            'estado' => 'sometimes|string|in:emitida,pendiente,cancelada'
        ]);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $productosProcesados = [];
        $total = 0;

        if ($request->has('productos')) {
            foreach ($request->productos as $producto) {
                if ($producto['tipoReferencia'] === 'producto') {
                    $referencia = Producto::find($producto['referencia_id']);
                    if (!$referencia) {
                        return $this->error("El producto con ID {$producto['referencia_id']} no existe", 404);
                    }

                    $precioUnitario = $referencia->precio ?? 0;
                    $subtotal = $producto['cantidad'] * $precioUnitario;
                    $total += $subtotal;

                    $productosProcesados[] = [
                        'tipoReferencia' => 'producto',
                        'referencia_id' => $producto['referencia_id'],
                        'nombre' => $referencia->nombre,
                        'descripcion' => $referencia->descripcion,
                        'atributos' => $producto['atributos'] ?? null,
                        'cantidad' => $producto['cantidad'],
                        'precioUnitario' => $precioUnitario,
                        'subtotal' => $subtotal
                    ];
                } elseif ($producto['tipoReferencia'] === 'evento') {
                    $referencia = Evento::find($producto['referencia_id']);
                    if (!$referencia) {
                        return $this->error("El evento con ID {$producto['referencia_id']} no existe", 404);
                    }

                    $tipoEntrada = $producto['tipoEntrada'] ?? null;
                    $entradaSeleccionada = collect($referencia->entradas)->firstWhere('tipo', $tipoEntrada);

                    if (!$entradaSeleccionada) {
                        return $this->error("No existe entrada '{$tipoEntrada}' para el evento {$referencia->nombreEvento}", 404);
                    }

                    $precioUnitario = $entradaSeleccionada['precio']['$numberInt'] ?? $entradaSeleccionada['precio'];
                    $subtotal = $producto['cantidad'] * $precioUnitario;
                    $total += $subtotal;

                    $productosProcesados[] = [
                        'referencia_id' => $producto['referencia_id'],
                        'tipoReferencia' => 'evento',
                        'nombreEvento' => $referencia->nombreEvento,
                        'nombreLugar' => $referencia->nombreLugar,
                        'direccion' => $referencia->direccion ?? null,
                        'fechaEvento' => $referencia->fecha,
                        'horaEvento' => $referencia->hora,
                        'tipoEntrada' => $tipoEntrada,
                        'cantidad' => $producto['cantidad'],
                        'precioUnitario' => $precioUnitario,
                        'subtotal' => $subtotal
                    ];
                }
            }

            $comprobante->productos = $productosProcesados;
            $comprobante->total = $total;
        }

        // Actualizar fecha y estado si vienen
        if ($request->has('fecha')) {
            $comprobante->fecha = $request->fecha;
        }
        if ($request->has('estado')) {
            $comprobante->estado = $request->estado;
        }

        $comprobante->save();

        return $this->success($comprobante, 'Comprobante actualizado exitosamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $numeroComprobante)
    {
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            return $this->error('Comprobante no encontrado', 404);
        }

        try {
            $comprobante->estado = "Eliminado";
            $comprobante->save();
            return $this->success($comprobante, 'Comprobante eliminado correctamente', 200);
        } catch (\Exception $e) {
            return $this->error('Error al eliminar el comprobante', 500, ['error' => $e->getMessage()]);
        }
    }
}
