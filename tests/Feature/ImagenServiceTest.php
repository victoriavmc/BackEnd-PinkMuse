<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('puede guardar imagen individual', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600);

    $service = app(ImageService::class);
    $rutas = $service->guardar($file, 'usuario', 'test_user');

    expect($rutas)->toHaveCount(1);
    expect($rutas[0])->toHaveKeys(['png', 'webp', 'principal']);

    Storage::disk('public')->assertExists($rutas[0]['png']);
    Storage::disk('public')->assertExists($rutas[0]['webp']);
});

test('puede guardar múltiples imágenes', function () {
    $files = [
        UploadedFile::fake()->image('test1.jpg'),
        UploadedFile::fake()->image('test2.jpg'),
    ];

    $service = app(ImageService::class);
    $rutas = $service->guardar($files, 'evento', 'mi_evento', true, 1);

    expect($rutas)->toHaveCount(2);

    foreach ($rutas as $index => $ruta) {
        Storage::disk('public')->assertExists($ruta['png']);
        Storage::disk('public')->assertExists($ruta['webp']);
        expect($ruta['principal'])->toBe($index === 1);
    }
});

test('lanza excepción con tipo inválido', function () {
    $file = UploadedFile::fake()->image('test.jpg');

    $service = app(ImageService::class);

    expect(fn () => $service->guardar($file, 'tipo_inexistente', 'test'))
        ->toThrow(\Exception::class, 'Tipo de imagen no soportado: tipo_inexistente');
});

test('genera rutas correctas por tipo', function () {
    $file = UploadedFile::fake()->image('test.jpg');

    $service = app(ImageService::class);
    $rutas = $service->guardar($file, 'producto', 'mi_producto');

    expect($rutas[0]['png'])->toContain('imagenes/producto/mi-producto');
    expect($rutas[0]['webp'])->toContain('imagenes/producto/mi-producto');
});

test('procesa diferentes tipos de contenido', function () {
    $tipos = ['usuario', 'evento', 'album', 'noticia', 'producto'];
    $tiposEsperados = [
        'usuario' => 'usuarios',
        'evento' => 'eventos',
        'album' => 'album',
        'noticia' => 'noticia',
        'producto' => 'producto',
    ];

    foreach ($tipos as $tipo) {
        $file = UploadedFile::fake()->image("test_{$tipo}.jpg");
        $service = app(ImageService::class);
        $rutas = $service->guardar($file, $tipo, "test_{$tipo}");

        $tipoEsperado = $tiposEsperados[$tipo];
        $nombreEsperado = str_replace('_', '-', "test_{$tipo}");

        expect($rutas[0]['png'])->toContain("imagenes/{$tipoEsperado}/{$nombreEsperado}");
        expect($rutas[0]['webp'])->toContain("imagenes/{$tipoEsperado}/{$nombreEsperado}");
    }
});

test('elimina rutas y variantes asociadas', function () {
    $file = UploadedFile::fake()->image('test.jpg', 600, 400);

    $service = app(ImageService::class);
    $rutas = $service->guardar($file, 'evento', 'evento_demo');
    $paths = $rutas[0];

    Storage::disk('public')->assertExists($paths['webp']);
    Storage::disk('public')->assertExists($paths['png']);

    $service->eliminar($paths['webp']);

    Storage::disk('public')->assertMissing($paths['webp']);
    Storage::disk('public')->assertMissing($paths['png']);
});

test('elimina rutas cuando se provee arreglo asociativo', function () {
    $file = UploadedFile::fake()->image('sample.jpg', 640, 480);

    $service = app(ImageService::class);
    $rutas = $service->guardar($file, 'evento', 'evento_prueba');
    $paths = $rutas[0];

    Storage::disk('public')->assertExists($paths['webp']);
    Storage::disk('public')->assertExists($paths['png']);

    $service->eliminar($paths);

    Storage::disk('public')->assertMissing($paths['webp']);
    Storage::disk('public')->assertMissing($paths['png']);
});
