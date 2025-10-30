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
     * Display a listing of the resource.
     */
    public function index()
    {
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
                'imagenPrincipal' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
                'stock.detalles.*.imagenes.*' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
                'descripcion' => 'sometimes|string',
                'precio' => 'sometimes|numeric|min:0',
                'estado' => 'sometimes|string|in:activo,inactivo',
                'stock' => 'sometimes|array',
                'stock.detalles' => 'sometimes|array|min:1',
                'stock.detalles.*.atributos' => 'sometimes|array',
                'stock.detalles.*.atributos.*.colores' => 'sometimes|array',
                'stock.detalles.*.cantidad' => 'sometimes|integer|min:0',
                'stock.detalles.*.imagenes' => 'nullable|array',
                'habilitarComentarios' => 'sometimes|boolean',
                'habilitarAcciones' => 'sometimes|string|in:si,no',
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:255|unique:productos,nombre',
                'imagenPrincipal' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
                'stock.detalles.*.imagenes.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric|min:0',
                'stock' => 'required|array',
                'stock.detalles' => 'required|array|min:1',
                'stock.detalles.*.atributos' => 'required|array',
                'stock.detalles.*.cantidad' => 'required|integer|min:0',
                'stock.detalles.*.imagenes' => 'nullable|array',
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

        if (Producto::where('nombre', $request->nombre)->exists()) {
            return $this->error('Ya existe un producto con el mismo nombre', 409);
        }

        $nombreBaseProducto = $request->nombre;
        $rutasImagenPrincipal = null;

        // 1. Procesar imagenPrincipal
        if ($request->hasFile('imagenPrincipal')) {

            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'producto',
                $nombreBaseProducto . '_principal',
                false,
                0
            );

            $rutasImagenPrincipal = $rutas[0];
        }

        // 2. Procesar imágenes de stock
        $detallesInput = $request->input('stock.detalles', []);
        $filesInput = $request->file('stock.detalles', []);
        $stockDetallesProcesados = [];

        foreach ($detallesInput as $index => $detalle) {
            $rutasImagenesDetalle = [];

            if (isset($filesInput[$index]['imagenes']) && is_array($filesInput[$index]['imagenes'])) {

                $files = $filesInput[$index]['imagenes']; // Array de UploadedFile

                // Definir cuál es la principal
                $principalIndex = 0;

                // Creamos un nombre base único para este detalle
                $nombreBaseDetalle = $nombreBaseProducto . '_detalle_' . $index;

                $rutasImagenesDetalle = $this->imageService->guardar(
                    $files,
                    'producto',
                    $nombreBaseDetalle,
                    true, // multiple = true
                    $principalIndex
                );
            }

            $stockDetallesProcesados[] = [
                'atributos' => $detalle['atributos'],
                'cantidad' => $detalle['cantidad'],
                'imagenes' => $rutasImagenesDetalle, // Guardamos el array de rutas
            ];
        }

        // Calcular stock total
        $stockTotal = collect($stockDetallesProcesados)->sum('cantidad');

        // Armar el objeto stock con total + detalles procesados
        $stock = [
            'total' => $stockTotal,
            'detalles' => $stockDetallesProcesados
        ];

        // Crear producto
        $producto = Producto::create([
            'nombre' => $request->nombre,
            'imagenPrincipal' => $rutasImagenPrincipal, // Guardamos el array ['png'=>...]
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
    public function update(Request $request, Producto $producto)
    {
        $validator = $this->validatorProductos($request, true);
        if ($validator->fails()) {
            return $this->error('Error de validación', 400, $validator->errors());
        }

        $data = $validator->validated();

        $nombreBaseProducto = $producto->nombre;

        // 1. Procesar imagenPrincipal (si se envió una nueva)
        if ($request->hasFile('imagenPrincipal')) {
            // Eliminar la imagen anterior
            $this->imageService->eliminar($producto->imagenPrincipal);

            // Guardar la nueva imagen
            $rutas = $this->imageService->guardar(
                $request->file('imagenPrincipal'),
                'producto',
                $nombreBaseProducto . '_principal',
                false,
                0
            );
            $data['imagenPrincipal'] = $rutas[0];
        }

        // 2. Procesar imágenes de stock (si se envió stock)
        if ($request->has('stock')) {
            $detallesInput = $request->input('stock.detalles', []);
            $filesInput = $request->file('stock.detalles', []);
            $oldStockDetalles = $producto->stock['detalles'] ?? [];

            $stockDetallesProcesados = [];

            foreach ($detallesInput as $index => $detalle) {
                $rutasImagenesDetalle = $oldStockDetalles[$index]['imagenes'] ?? [];

                if (isset($filesInput[$index]['imagenes']) && is_array($filesInput[$index]['imagenes'])) {

                    foreach ($rutasImagenesDetalle as $oldImage) {
                        $this->imageService->eliminar($oldImage);
                    }

                    $files = $filesInput[$index]['imagenes'];
                    $nombreBaseDetalle = $nombreBaseProducto . '_detalle_' . $index;
                    $rutasImagenesDetalle = $this->imageService->guardar(
                        $files,
                        'producto',
                        $nombreBaseDetalle,
                        true,
                        0
                    );
                }

                $stockDetallesProcesados[] = [
                    'atributos' => $detalle['atributos'],
                    'cantidad' => $detalle['cantidad'],
                    'imagenes' => $rutasImagenesDetalle,
                ];
            }

            $oldDetailCount = count($oldStockDetalles);
            $newDetailCount = count($stockDetallesProcesados);
            if ($oldDetailCount > $newDetailCount) {
                for ($i = $newDetailCount; $i < $oldDetailCount; $i++) {
                    if (isset($oldStockDetalles[$i]['imagenes'])) {
                        foreach ($oldStockDetalles[$i]['imagenes'] as $imgToDelete) {
                            $this->imageService->eliminar($imgToDelete);
                        }
                    }
                }
            }

            $data['stock'] = [
                'total' => collect($stockDetallesProcesados)->sum('cantidad'),
                'detalles' => $stockDetallesProcesados
            ];
        }

        // 3. Actualizar el producto de la base de datos
        $producto->update($data);

        return $this->success($producto, 'Producto actualizado correctamente', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        try {
            // 1. Eliminar la imagen principal
            $this->imageService->eliminar($producto->imagenPrincipal);

            // 2. Eliminar todas las imágenes del stock
            if (isset($producto->stock['detalles']) && is_array($producto->stock['detalles'])) {
                foreach ($producto->stock['detalles'] as $detalle) {
                    if (isset($detalle['imagenes']) && is_array($detalle['imagenes'])) {
                        foreach ($detalle['imagenes'] as $imagen) {
                            $this->imageService->eliminar($imagen);
                        }
                    }
                }
            }

            // 3. Eliminar el producto de la base de datos
            $producto->delete();

            return $this->success(null, 'Producto eliminado con éxito', 204);

        } catch (\Exception $e) {
            return $this->error('Error al eliminar el producto', 500, $e->getMessage());
        }
    }
}