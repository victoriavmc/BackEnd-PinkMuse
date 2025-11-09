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
        //
        $comprobantes = Comprobante::all();
        if ($comprobantes->isEmpty()) {
            return $this->error("No se encontraron comprobantes", 404);
        }
        return $this->success($comprobantes, 'Comprobantes encontrados con exito', 200);
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

            'datosPago' => 'required|array|min:1',
            'datosPago.nombre' => 'required|string',
            'datosPago.apellido' => 'required|string',
            'datosPago.email' => 'required|email',
            'datosPago.dni' => 'required|digits:8',
            'datosPago.telefono' => 'required|digits:13',
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
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Verificar que el usuario exista
        $usuario = Usuario::find($request->usuario_id);
        if (!$usuario) {
            return $this->error('Usuario no encontrado', 404);
        }

        // Los datos de pago se validan como array, no es necesario decodificar, pero el idTransaccion debe ser único en toda la colección de comprobantes
        $idTransaccion = $request->datosPago['idTransaccion'];
        if (Comprobante::where('datosPago->idTransaccion', $idTransaccion)->exists()) {
            return $this->error("El ID de transacción {$idTransaccion} ya existe", 409);
        }

        // Validar y procesar cada producto
        if (!is_array($request->productos) || empty($request->productos)) {
            return $this->error("El campo productos debe ser un array no vacío", 400);
        }

        // Recibo productos como array de objetos
        if (!is_array($request->productos)) {
            return $this->error("El campo productos debe ser un array", 400);
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
                    return $this->error("El producto con ID {$producto['referencia_id']} no existe", 404);
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
                    return $this->error("El producto con ID {$producto['referencia_id']} no existe", 404);
                }

                // Aca dice que tipo de entrada selecciono
                $tipoEntrada = $producto['tipoEntrada'] ?? null;

                //Verifica que exista esa entrada para, ese evento.
                $entradaSeleccionada = collect($referencia->entradas)->firstWhere('tipo', $tipoEntrada);

                // No existe
                if (!$entradaSeleccionada) {
                    return $this->error("No existe entrada '{$tipoEntrada}' para el evento {$referencia->nombreEvento}", 404);
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
                return $this->error("Tipo de referencia inválido: {$producto['tipoReferencia']}", 400);
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
            return $this->error('Error al crear el comprobante', 500);
        }

        return $this->success($comprobante, 'Comprobante creado exitosamente', 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(string $numeroComprobante)
    {
        $comprobante = Comprobante::where('numeroComprobante', $numeroComprobante)->first();
        if (!$comprobante) {
            return $this->error('Comprobante no encontrado', 404);
        }
        return $this->success($comprobante, 'Comprobante encontrado', 200);
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
