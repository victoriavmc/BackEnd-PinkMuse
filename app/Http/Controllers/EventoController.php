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
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        //   "nombreEvento": "Festival922",
        //   "nombreLugar": "Teatro Colón",
        //   "lugar": "Buenos Aires",
        //   "fecha": "2025-09-10",
        //   "hora": "20:00",
        //   "entradas": [
        //     { "tipo": "VIP", "precio": 15000, "cantidad": 700, "estado": "activo" },
        //     { "tipo": "General", "precio": 8000, "cantidad": 200, "estado": "agotado" }
        //   ],
        //   "coordenadas": { "lat": -34.6, "lng": -58.38 },
        //   "artistasExtras": ["Banda Invitada"],
        //   "estado": "activo",
        //   "imagenPrincipal": "url_evento"
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
