<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Evento;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ComprobanteController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $comprobantes = Comprobante::all();
        if ($comprobantes->isEmpty()) {
            $data = [
                'message' => 'No se encontraron comprobantes',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'comprobantes' => $comprobantes,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'numeroComprobante' => 'required|string|unique:comprobantes,numeroComprobante',
            'fecha' => 'required|date',
            'usuario_id' => 'required|string|exists:usuarios,_id',

            'datosPago' => 'required|array',
            'datosPago.nombre' => 'required|string',
            'datosPago.apellido' => 'required|string',
            'datosPago.email' => 'required|email',
            'datosPago.dni' => 'required|string',
            'datosPago.telefono' => 'required|string',
            'datosPago.idTransaccion' => 'required|string',
            'datosPago.metodoPago' => 'required|string|in:tarjeta,transferencia,efectivo',

            'productos' => 'required|array|min:1',
            'productos.*.tipoReferencia' => 'required|string|in:producto,evento',
            'productos.*.referencia_id' => 'required|string',
            'productos.*.cantidad' => 'required|integer|min:1',

            'productos.*.tipoEntrada' => 'required_if:productos.*.tipoReferencia,evento|string',

            'productos.*.atributos' => 'nullable|array',
            'productos.*.atributos.tamaño' => 'nullable|string',
            'productos.*.atributos.color' => 'nullable|string',

            'estado' => 'required|string|in:emitida,pendiente,cancelada'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Verificar que el usuario exista
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => 404
            ], 404);
        }

        // Los datos de pago se validan como array, no es necesario decodificar, pero el idTransaccion debe ser único en toda la colección de comprobantes
        $idTransaccion = $request->datosPago['idTransaccion'];
        if (Comprobante::where('datosPago->idTransaccion', $idTransaccion)->exists()) {
            return response()->json([
                'message' => "El ID de transacción {$idTransaccion} ya existe",
                'status' => 409
            ], 409);
        }

        // Validar y procesar cada producto
        if (!is_array($request->productos) || empty($request->productos)) {
            return response()->json([
                'message' => 'El campo productos debe ser un array no vacío',
                'status' => 400
            ], 400);
        }

        // Recibo productos como array de objetos
        if (!is_array($request->productos)) {
            return response()->json([
                'message' => 'El campo productos debe ser un array',
                'status' => 400
            ], 400);
        }

        // Procesar productos y calcular total
        $productosProcesados = [];
        $total = 0;

        // Recorrer cada producto para validar y calcular subtotal
        foreach ($request->productos as $producto) {
            // Validar si es producto o evento
            if ($producto['tipoReferencia'] === 'producto') {
                $referencia = Producto::find($producto['referencia_id']);

                // Verificar que el producto exista
                if (!$referencia) {
                    return response()->json([
                        'message' => "El producto con ID {$producto['referencia_id']} no existe",
                        'status' => 404
                    ], 404);
                }

                // Sabiendo que existe el producto, voy a guardar sus datos especificos
                $precioUnitario = $referencia->precio ?? $producto->precioUnitario;
                $subtotal = $producto['cantidad'] * $precioUnitario;
                $total += $subtotal;

                // Los atributos que presenta el producto deben de c

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
                // Verificar que el evento exista
                if (!$referencia) {
                    return response()->json([
                        'message' => "El evento con ID {$producto['referencia_id']} no existe",
                        'status' => 404
                    ], 404);
                }

                // Aca dice que tipo de entrada selecciono
                $tipoEntrada = $producto['tipoEntrada'] ?? null;

                //Verifica que exista esa entrada para, ese evento.
                $entradaSeleccionada = collect($referencia->entradas)->firstWhere('tipo', $tipoEntrada);

                // No existe
                if (!$entradaSeleccionada) {
                    return response()->json([
                        'message' => "No existe entrada '{$tipoEntrada}' para el evento {$referencia->nombreEvento}",
                        'status' => 404
                    ], 404);
                }

                // En caso que exista, traigo el precio de la entrada seleccionada
                $precioUnitario = $entradaSeleccionada['precio']['$numberInt'] ?? $entradaSeleccionada['precio'];
                $subtotal = $producto['cantidad'] * $precioUnitario;
                $total += $subtotal;

                //
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
            } else {
                return response()->json([
                    'message' => "Tipo de referencia inválido: {$producto['tipoReferencia']}",
                    'status' => 400
                ], 400);
            }
        }

        // Guardar arrays
        $comprobanteData = $request->all();
        $comprobanteData['datosPago'] = $request->datosPago;
        $comprobanteData['productos'] = $productosProcesados;
        $comprobanteData['total'] = $total;
        $comprobanteData['estado'] = 'emitida';

        $comprobante = Comprobante::create($comprobanteData);

        // Error al crear
        if (!$comprobante) {
            return response()->json([
                'message' => 'Error al crear el comprobante',
                'status' => 500
            ], 500);
        }

        // Creado con exito
        return response()->json([
            'message' => 'Comprobante creado exitosamente',
            'comprobante' => $comprobante,
            'status' => 201
        ], 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $numeroComprobante)
    {
        //
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            $data = [
                'message' => 'Comprobante no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Comprobante encontrado',
            'comprobante' => $comprobante,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $numeroComprobante)
    {
        //
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            $data = [
                'message' => 'Comprobante no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
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
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }
        $productosProcesados = [];
        $total = 0;

        if ($request->has('productos')) {
            foreach ($request->productos as $producto) {
                if ($producto['tipoReferencia'] === 'producto') {
                    $referencia = Producto::find($producto['referencia_id']);
                    if (!$referencia) {
                        return response()->json([
                            'message' => "El producto con ID {$producto['referencia_id']} no existe",
                            'status' => 404
                        ], 404);
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
                        return response()->json([
                            'message' => "El evento con ID {$producto['referencia_id']} no existe",
                            'status' => 404
                        ], 404);
                    }

                    $tipoEntrada = $producto['tipoEntrada'] ?? null;
                    $entradaSeleccionada = collect($referencia->entradas)->firstWhere('tipo', $tipoEntrada);

                    if (!$entradaSeleccionada) {
                        return response()->json([
                            'message' => "No existe entrada '{$tipoEntrada}' para el evento {$referencia->nombreEvento}",
                            'status' => 404
                        ], 404);
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

        return response()->json([
            'message' => 'Comprobante actualizado exitosamente',
            'comprobante' => $comprobante,
            'status' => 200
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $numeroComprobante)
    {
        //
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            $data = [
                'message' => 'Comprobante no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        try {
            // Cambiar el estado a 'eliminado'
            $comprobante->estado = "eliminado";
            $comprobante->save();

            return response()->json([
                'message' => 'Comprobante eliminado correctamente',
                'comprobante' => $comprobante,
                'status' => 200
            ], 200);
        } catch (\Exception $e) {

            // Manejar cualquier error al guardar
            return response()->json([
                'message' => 'Error al eliminar el comprobante',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }
}
