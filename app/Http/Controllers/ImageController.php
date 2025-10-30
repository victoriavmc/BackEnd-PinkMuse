<?php

namespace App\Http\Controllers;

use App\Services\ImageService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ImageController
{
    use ApiResponse;

    private ImageService $imageService;

    private const TIPOS_PERMITIDOS = ['usuario', 'evento', 'album', 'noticia', 'producto'];

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo' => ['required', 'string', Rule::in(self::TIPOS_PERMITIDOS)],
            'nombre' => ['required', 'string', 'max:255'],
            'multiple' => ['sometimes', 'boolean'],
            'principalIndex' => ['nullable', 'integer', 'min:0'],
            'imagen' => ['required_without:imagenes', 'file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'imagenes' => ['required_without:imagen', 'array'],
            'imagenes.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validacion', 422, $validator->errors());
        }

        $multiple = $request->boolean('multiple', false);
        $imagenesAdjuntas = $request->file('imagenes');
        if (!$multiple && is_array($imagenesAdjuntas) && count($imagenesAdjuntas) > 1) {
            $multiple = true;
        }

        $tipo = (string) $request->input('tipo');
        $nombre = (string) $request->input('nombre');
        $principalIndex = $request->filled('principalIndex')
            ? (int) $request->input('principalIndex')
            : null;

        if ($principalIndex !== null && $principalIndex < 0) {
            $principalIndex = null;
        }

        $files = null;
        if ($multiple) {
            $files = $request->file('imagenes', []);
        } else {
            $files = $request->file('imagen');

            if (!$files && $request->hasFile('imagenes')) {
                $imagenes = $request->file('imagenes');
                $files = is_array($imagenes) ? reset($imagenes) : $imagenes;
            }
        }

        if (!$files) {
            return $this->error('No se proporciono ninguna imagen para subir', 400);
        }

        $imagenesProcesadas = $this->imageService->guardar(
            $files,
            $tipo,
            $nombre,
            $multiple,
            $multiple ? $principalIndex : 0
        );

        return $this->success($imagenesProcesadas, 'Imagenes subidas exitosamente', 201);
    }

    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ruta' => ['required_without:rutas', 'string', 'max:2048'],
            'rutas' => ['required_without:ruta', 'array', 'min:1'],
            'rutas.*' => ['string'],
        ]);

        if ($validator->fails()) {
            return $this->error('Error de validacion', 422, $validator->errors());
        }

        $entradas = [];

        if ($request->filled('ruta')) {
            $entradas[] = $request->input('ruta');
        }

        if ($request->has('rutas')) {
            $rutas = $request->input('rutas');
            if (!is_array($rutas)) {
                $rutas = [$rutas];
            }
            foreach ($rutas as $ruta) {
                $entradas[] = $ruta;
            }
        }

        if (empty($entradas)) {
            return $this->error('No se proporcionaron rutas para eliminar', 400);
        }

        $resultados = [];
        foreach ($entradas as $entrada) {
            $resultados[] = [
                'entrada' => $entrada,
                'eliminada' => $this->imageService->eliminar($entrada),
            ];
        }

        return $this->success($resultados, 'Imagenes eliminadas exitosamente', 200);
    }
}
