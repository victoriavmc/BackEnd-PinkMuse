<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Services\ImageService;
use Illuminate\Support\Str;

class ProductoController
{
    use ApiResponse;

    protected NotificationService $notificationService;
    protected ImageService $imageService;

    public function __construct(NotificationService $notificationService, ImageService $imageService)
    {
        $this->notificationService = $notificationService;
        $this->imageService = $imageService;
    }

    /**
     * Mostrar todos los productos.
     */
    public function index()
    {
        $productos = Producto::all();
        if ($productos->isEmpty()) {
            return $this->error('No se encontraron productos', 404);
        }
        return $this->success($productos, 'Productos obtenidos exitosamente', 200);
    }

    /**
     * Validar los campos de producto.
     */
    public function validatorProductos(Request $request, $isUpdate = false)
    {
        $rules = [
            'descripcion' => 'sometimes|string',
            'precio' => 'sometimes|numeric|min:0',
            'estado' => 'sometimes|string|in:Activo,Inactivo',
            'stock' => 'sometimes|array',
            'stock.total' => 'sometimes|integer|min:0',
            'stock.colores' => 'sometimes|array',
            'stock.colores.*.imagenes' => 'nullable|array',
            'stock.colores.*.imagenes.*' => 'nullable|string',
            'stock.colores.*.talles' => 'sometimes|array|min:1',
            'stock.colores.*.talles.*.talle' => 'required|string',
            'stock.colores.*.talles.*.cantidad' => 'required|integer|min:0',
            'habilitarAcciones' => 'sometimes|string|in:si,no',
            'habilitarComentarios' => 'sometimes|boolean',
        ];

        if (!$isUpdate) {
            $rules = array_merge($rules, [
                'nombre' => 'required|string|max:255|unique:productos,nombre',
                'imagenPrincipal' => 'required|string',
            ]);
        }

        return Validator::make($request->all(), $rules);
    }

    /**
     * Crear un nuevo producto.
     */
    public function store(Request $request)
    {
        $validator = $this->validatorProductos($request);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $data = $validator->validated();

        // Cálculo de stock total si no viene explícito
        if (!isset($data['stock']['total']) && isset($data['stock']['colores'])) {
            $data['stock']['total'] = collect($data['stock']['colores'])
                ->flatMap(fn($color) => $color['talles'])
                ->sum('cantidad');
        }

        $producto = Producto::create($data);

        $this->notificationService->notifyUsers('producto', [
            'titulo' => 'Nuevo producto',
            'mensaje' => sprintf('Nuevo producto disponible: %s.', $producto->nombre),
            'referencia_tipo' => 'producto',
            'referencia_id' => $producto->_id ?? $producto->id ?? null,
            'datos' => [
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => $producto->precio,
                'imagen' => $producto->imagenPrincipal,
                'link' => '/merch',
            ],
            'fecha' => Carbon::now(),
        ]);

        return $this->success($producto, 'Producto creado correctamente', 201);
    }

    /**
     * Mostrar un producto específico.
     */
    public function show(string $nombre)
    {
        $producto = Producto::where('nombre', strtoupper($nombre))->first();
        if (!$producto) {
            return $this->error('Producto no encontrado', 404);
        }
        return $this->success($producto, 'Producto encontrado', 200);
    }

    /**
     * Actualizar producto.
     */
    public function update(Request $request, Producto $producto)
    {
        $validator = $this->validatorProductos($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $data = $validator->validated();

        // Recalcular stock total si hay cambios en talles
        if (isset($data['stock']['colores'])) {
            $data['stock']['total'] = collect($data['stock']['colores'])
                ->flatMap(fn($color) => $color['talles'])
                ->sum('cantidad');
        }
        $producto->update($data);
        return $this->success($producto, 'Producto actualizado correctamente', 200);
    }

    /**
     * Eliminar producto.
     */
    public function destroy(Producto $producto)
    {
        try {
            $producto->delete();
            return $this->success(null, 'Producto eliminado correctamente', 204);
        } catch (\Exception $e) {
            return $this->error('Error al eliminar el producto', 500, $e->getMessage());
        }
    }

    /**
     * Obtener stock detallado de un producto (por color, talle, cantidad)
     */
    public function stockDetalle(string $nombre)
    {
        $producto = Producto::where('nombre', strtoupper($nombre))->first();

        if (!$producto) {
            return $this->error('Producto no encontrado', 404);
        }

        $stock = $producto->stock['colores'] ?? [];
        $detalle = [];

        foreach ($stock as $color => $info) {
            foreach ($info['talles'] as $talleInfo) {
                $detalle[] = [
                    'color' => $color,
                    'talle' => $talleInfo['talle'],
                    'cantidad' => $talleInfo['cantidad'],
                    'imagenes' => $info['imagenes'] ?? []
                ];
            }
        }

        return $this->success($detalle, 'Stock detallado obtenido correctamente', 200);
    }
}