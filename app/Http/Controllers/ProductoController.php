<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductoController
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $productos = Producto::all();
        if ($productos->isEmpty()) {
            return $this->error('No se encontraron Productos', 400);
        }
        return $this->success($productos, 'Productos obtenidos exitosamente', 200);
    }

    // Validator
    public function validatorProductos(Request $request, $isUpdate = false)
    {
        if ($isUpdate) {
            $validator = Validator::make($request->all(), [
                'nombre' => 'prohibited',
                'imagenPrincipal' => 'sometimes|string',
                'descripcion' => 'sometimes|string',
                'precio' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|string|in:activo,inactivo',
                'stock' => 'sometimes|array',
                'stock.detalles' => 'sometimes|array|min:1',
                'stock.detalles.*.atributos' => 'sometimes|array',
                'stock.detalles.*.atributos.*.colores' => 'sometimes|array',
                'stock.detalles.*.cantidad' => 'sometimes|integer|min:0',
                'stock.detalles.*.imagenes' => 'nullable|array',
                'stock.detalles.*.imagenes.*' => 'string',
                'habilitarComentarios' => 'sometimes|boolean',
                'habilitarAcciones' => 'sometimes|string|in:si,no',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:productos,nombre',
                'imagenPrincipal' => 'required|string',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|array',
                'stock.detalles' => 'required|array|min:1',
                'stock.detalles.*.atributos' => 'required|array',
                'stock.detalles.*.cantidad' => 'required|integer|min:0',
                'stock.detalles.*.imagenes' => 'nullable|array',
                'stock.detalles.*.imagenes.*' => 'string',
                'habilitarComentarios' => 'required|boolean',
                'habilitarAcciones' => 'required|string|in:si,no',
            ]);
        }

        return $validator;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = $this->validatorProductos($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        // Verificar duplicado de nombre
        if (Producto::where('nombre', $request->nombre)->exists()) {
            return $this->error('Ya existe un producto con el mismo nombre', 409);
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
            'estado' => 'Activo',
            'stock' => $stock,
            'habilitarComentarios' => $request->habilitarComentarios,
            'habilitarAcciones' => $request->habilitarAcciones
        ]);

        if (!$producto) {
            return $this->error('Error al crear el producto', 500);
        }
        return $this->success($producto, 'Producto creado correctamente', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $nombre)
    {
        //
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            return $this->error('Producto no encontrado', 404);
        }
        return $this->success($producto, 'Producto encontrado', 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $nombre)
    {
        //
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            return $this->error('Producto no encontrado', 404);
        }

        $validator = $this->validatorProductos($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400);
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
            return $this->error('Error al modificar producto', 500);
        }
        return $this->success($producto, 'Producto modificado correctamente', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $nombre)
    {
        //
        $producto = Producto::where('nombre', $nombre)->first();
        if (!$producto) {
            return $this->error('Producto no encontrado', 404);
        }
        // $producto->delete();
        $producto->estado = 'Inactivo';
        return $this->success($nombre, 'Producto eliminado con éxito', 200);
    }
}
