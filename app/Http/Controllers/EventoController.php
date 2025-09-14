<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponse;

class EventoController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $eventos = Evento::all();
        if ($eventos->isEmpty()) {
            return $this->error("No se encontraron eventos", 404);
        }
        return $this->success($eventos, "Eventos obtenidos exitosamente", 200);
    }

    // Validator
    public function validatorEvento(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
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
                'imagenPrincipal' => 'nullable|string|max:255',
            ]);
        }
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
            return $this->error('Error al crear el evento', 500);
        }

        return $this->success($evento, 'Evento creado exitosamente', 201);
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

        return $this->success($evento, 'Evento actualizado exitosamente', 200);
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

        // Si se envía 'tipo', borramos solo esa entrada
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
        $evento->delete();

        return $this->success(null, 'Evento eliminado exitosamente', 200);
    }
}