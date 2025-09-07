<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventoController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $eventos = Evento::all();
        if ($eventos->isEmpty()) {
            $data = [
                'message' => 'No se encontraron eventos',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'eventos' => $eventos,
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
            'imagenPrincipal' => 'nullable|string|max:255',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        //Verificar que El mismo tipo de entrada, EN un mismo evento no se repita
        if ($request->has('entradas')) {
            $tipos = array_column($request->entradas, 'tipo');
            if (count($tipos) !== count(array_unique($tipos))) {
                return response()->json([
                    'message' => 'Error de validación',
                    'errors' => [
                        'entradas' => ['No puede haber tipos de entrada repetidos en el mismo evento']
                    ],
                    'status' => 400
                ], 400);
            }
        }

        if (Evento::where('nombreEvento', $request->nombreEvento)->exists()) {
            return response()->json([
                'message' => 'El evento ya existe',
                'status' => 409
            ], 409);
        }
        $evento = new Evento();
        $evento->nombreEvento = $request->nombreEvento;
        $evento->nombreLugar = $request->nombreLugar;
        $evento->direccion = $request->direccion ?? null;
        $evento->fecha = $request->fecha;
        $evento->hora = $request->hora;
        $evento->entradas = $request->entradas;
        $evento->estado = $request->estado;
        $evento->imagenPrincipal = $request->imagenPrincipal ?? null;

        $evento->save();

        if (!$evento) {
            return response()->json([
                'message' => 'Error al crear el evento',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Evento creado exitosamente',
            'evento' => $evento,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombreEvento)
    {
        //
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();
        if (!$evento) {
            return response()->json([
                'message' => 'Evento no encontrado',
                'status' => 404
            ], 404);
        }
        return response()->json([
            'evento' => $evento,
            'status' => 200
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nombreEvento)
    {
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();
        if (!$evento) {
            return response()->json([
                'message' => 'Evento no encontrado',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombreEvento' => 'prohibited', // no se puede modificar
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
            'imagenPrincipal' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        if ($request->has('entradas')) {
            $entradasActuales = $evento->entradas ?? [];
            $tiposActuales = array_column($entradasActuales, 'tipo');
            $tiposNuevos = [];

            foreach ($request->entradas as $entradaNueva) {
                $tipo = $entradaNueva['tipo'];

                // Verificar duplicado dentro del request
                if (in_array($tipo, $tiposNuevos)) {
                    return response()->json([
                        'message' => 'Error de validación',
                        'errors' => [
                            'entradas' => ["El tipo de entrada '{$tipo}' se repite en la solicitud"]
                        ],
                        'status' => 400
                    ], 400);
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

            $evento->entradas = $entradasActuales;
        }
        // Si viene dirección, garantizamos que sea objeto
        if ($request->has('direccion')) {
            $evento->direccion = $request->direccion;
        }

        // Actualizar campos que vienen en la request
        foreach ($request->except('nombreEvento') as $key => $value) {
            $evento->{$key} = $value;
        }

        $evento->save();

        return response()->json([
            'message' => 'Evento actualizado exitosamente',
            'evento' => $evento,
            'status' => 200
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $nombreEvento)
    {
        $evento = Evento::where('nombreEvento', $nombreEvento)->first();

        if (!$evento) {
            return response()->json([
                'message' => 'Evento no encontrado',
                'status' => 404
            ], 404);
        }

        // Si se envía 'tipo', borramos solo esa entrada
        if ($request->has('tipo')) {
            $tipo = $request->input('tipo');
            $entradasActuales = $evento->entradas ?? [];

            $entradasFiltradas = array_filter($entradasActuales, function ($entrada) use ($tipo) {
                return $entrada['tipo'] !== $tipo;
            });

            // Si no se encontró la entrada
            if (count($entradasFiltradas) === count($entradasActuales)) {
                return response()->json([
                    'message' => "La entrada '{$tipo}' no existe en este evento",
                    'status' => 404
                ], 404);
            }

            $evento->entradas = array_values($entradasFiltradas); // reindexar
            $evento->save();

            return response()->json([
                'message' => "Entrada '{$tipo}' eliminada exitosamente",
                'evento' => $evento,
                'status' => 200
            ], 200);
        }

        // Si no se envía 'tipoEntrada', borramos todo el evento
        $evento->delete();

        return response()->json([
            'message' => 'Evento eliminado exitosamente',
            'status' => 200
        ], 200);
    }
}
