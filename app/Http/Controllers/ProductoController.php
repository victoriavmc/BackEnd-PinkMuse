<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $productos = Producto::all();
        if ($productos->isEmpty()) {
            $data = [
                'message' => 'No se encontraron productos',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $data = [
            'productos' => $productos,
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
            'nombre' => 'required|string|max:255|unique:productos,nombre',
            'imagenPrincipal' => 'required|string',
            'descripcion' => 'required|string',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|string|in:activo,inactivo',
            'stock' => 'required|array',
            'stock.detalles' => 'required|array|min:1',
            'stock.detalles.*.atributos' => 'required|array',
            'stock.detalles.*.cantidad' => 'required|integer|min:0',
            'stock.detalles.*.imagenes' => 'nullable|array',
            'stock.detalles.*.imagenes.*' => 'string',
            'habilitarComentarios' => 'required|boolean',
            'habilitarAcciones' => 'required|string|in:si,no'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Verificar duplicado de nombre
        if (Producto::where('nombre', $request->nombre)->exists()) {
            return response()->json([
                'message' => 'Ya existe un producto con el mismo nombre',
                'status' => 409
            ], 409);
        }

        // Calcular stock total
        $stockDetalles = $request->stock['detalles'];
        $stockTotal = collect($stockDetalles)->sum('cantidad');

        // Armar el objeto stock con total + detalles
        $stock = [
            'total' => $stockTotal,
            'detalles' => $stockDetalles
        ];

        // Crear producto
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'imagenPrincipal' => $request->imagenPrincipal,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'estado' => $request->estado,
            'stock' => $stock,
            'habilitarComentarios' => $request->habilitarComentarios,
            'habilitarAcciones' => $request->habilitarAcciones
        ]);

        if (!$producto) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Producto creado correctamente',
            'producto' => $producto,
            'status' => 201
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombre)
    {
        //
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            $data = [
                'message' => 'Producto no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Producto encontrado',
            'producto' => $producto,
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
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            $data = [
                'message' => 'Producto no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'prohibited',
            'imagenPrincipal' => 'sometimes|string',
            'descripcion' => 'sometimes|string',
            'precio' => 'sometimes|numeric|min:0',
            'estado' => 'sometimes|string|in:activo,inactivo',
            'stock' => 'sometimes|array',
            'stock.detalles' => 'sometimes|array|min:1',
            'stock.detalles.*.atributos' => 'sometimes|array',
            'stock.detalles.*.cantidad' => 'sometimes|integer|min:0',
            'stock.detalles.*.imagenes' => 'nullable|array',
            'stock.detalles.*.imagenes.*' => 'string',
            'habilitarComentarios' => 'sometimes|boolean',
            'habilitarAcciones' => 'sometimes|string|in:si,no'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => 400
            ], 400);
        }

        // Calcular stock total
        $stockDetalles = $request->stock['detalles'];
        $stockTotal = collect($stockDetalles)->sum('cantidad');

        // Armar el objeto stock con total + detalles
        $stock = [
            'total' => $stockTotal,
            'detalles' => $stockDetalles
        ];

        // Modificar producto
        $producto->imagenPrincipal = $request->imagenPrincipal;
        $producto->descripcion = $request->descripcion;
        $producto->precio = $request->precio;
        $producto->estado = $request->estado;
        $producto->stock = $stock;
        $producto->habilitarComentarios = $request->habilitarComentarios;
        $producto->habilitarAcciones = $request->habilitarAcciones;
        $producto->save();

        if (!$producto) {
            return response()->json([
                'message' => 'Error al crear el producto',
                'status' => 500
            ], 500);
        }

        return response()->json([
            'message' => 'Producto modificado correctamente',
            'producto' => $producto,
            'status' => 201
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nombre)
    {
        //
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            $data = [
                'message' => 'Producto no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }
        $producto->delete();
        $data = [
            'message' => 'Producto eliminado con éxito',
            'producto' => $producto,
            'status' => 200
        ];
        return response()->json($data, 200);
    }
}
