<?php

namespace App\Http\Controllers;

use App\Models\RedSocial;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RedSocialController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $redSociales = RedSocial::all();
        if ($redSociales->isEmpty()) {
            return $this->error('No hay redes sociales disponibles', 404);
        }
        return $this->success($redSociales, 200);
    }

    //Validator
    public function validateRequest(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
            return Validator::make($request->all(), [
                'nombre' => 'sometimes|required|string|max:255|unique:nombre,nombre',
                'url' => 'sometimes|required|url',
            ]);
        } else {
            return Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:nombre,nombre',
                'url' => 'required|url',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = $this->validateRequest($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Verifica que no se duplique el nombre de la red social
        if (RedSocial::where('nombre', $request->nombre)->exists()) {
            return $this->error('El nombre de la red social ya existe', 409);
        }

        $redSocial = RedSocial::create($request->all());
        return $this->success($redSocial, 'Red social creada con éxito', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            return $this->error('Red social no encontrada', 404);
        }
        return $this->success($redSocial, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            return $this->error('Red social no encontrada', 404);
        }

        $validator = $this->validateRequest($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $redSocial->update($request->all());

        return $this->success($redSocial, 'Red social actualizada con éxito', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            return $this->error('Red social no encontrada', 404);
        }
        $redSocial->delete();
        return $this->success(null, 'Red social eliminada con éxito', 200);
    }
}
