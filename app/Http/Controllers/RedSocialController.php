<?php

namespace App\Http\Controllers;

use App\Models\RedSocial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RedSocialController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $redSociales = RedSocial::all();
        if ($redSociales->isEmpty()) {
            $data = [
                'message' => 'No se encontraron redes sociales',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'redes_sociales' => $redSociales,
            'status' => 200,
        ];
        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255|unique:nombre,nombre',
            'url' => 'required|url',
        ]);
        if ($validator->fails()) {
            $data = [
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }

        // Verifica que no se duplique el nombre de la red social
        if (RedSocial::where('nombre', $request->nombre)->exists()) {
            return response()->json([
                'message' => 'El nombre ya existe',
                'status' => 409
            ], 409);
        }

        $redSocial = RedSocial::create($request->all());
        $data = [
            'message' => 'Red social creada con éxito',
            'red_social' => $redSocial,
            'status' => 201
        ];
        return response()->json($data, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            $data = [
                'message' => 'Red social no encontrada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'red_social' => $redSocial,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            $data = [
                'message' => 'Red social no encontrada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $validator = Validator::make($request->all(), [
            'nombre' => 'prohibited',
            'url' => 'sometimes|required|url|max:255',
        ]);
        if ($validator->fails()) {
            $data = [
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ];
            return response()->json($data, 400);
        }
        $redSocial->update($request->all());
        $data = [
            'message' => 'Red social actualizada con éxito',
            'red_social' => $redSocial,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nombre)
    {
        //
        $redSocial = RedSocial::where('nombre', $nombre)->first();
        if (!$redSocial) {
            $data = [
                'message' => 'Red social no encontrada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $redSocial->delete();
        $data = [
            'message' => 'Red social eliminada con éxito',
            'status' => 200
        ];
        return response()->json($data, 200);
    }
}