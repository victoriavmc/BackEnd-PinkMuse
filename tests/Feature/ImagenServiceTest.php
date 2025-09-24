<?php

use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('puede guardar imagen individual', function () {
    $file = UploadedFile::fake()->image('test.jpg', 800, 600);
    
    $service = new ImageService($file, 'usuario', 'test_user');
    $rutas = $service->guardar();
    
    expect($rutas)->toHaveCount(1);
    expect($rutas[0])->toHaveKey('png');
    expect($rutas[0])->toHaveKey('webp');
    expect($rutas[0])->toHaveKey('principal');
    
    Storage::assertExists($rutas[0]['png']);
    Storage::assertExists($rutas[0]['webp']);
});

test('puede guardar múltiples imágenes', function () {
    $files = [
        UploadedFile::fake()->image('test1.jpg'),
        UploadedFile::fake()->image('test2.jpg')
    ];
    
    $service = new ImageService($files, 'evento', 'mi_evento', true);
    $rutas = $service->guardar();
    
    expect($rutas)->toHaveCount(2);
    
    foreach ($rutas as $ruta) {
        Storage::assertExists($ruta['png']);
        Storage::assertExists($ruta['webp']);
    }
});

test('lanza excepción con tipo inválido', function () {
    $file = UploadedFile::fake()->image('test.jpg');
    
    $service = new ImageService($file, 'tipo_inexistente', 'test');
    
    expect(fn() => $service->guardar())
        ->toThrow(Exception::class, 'Tipo de imagen no soportado: tipo_inexistente');
});

test('genera rutas correctas por tipo', function () {
    $file = UploadedFile::fake()->image('test.jpg');
    
    $service = new ImageService($file, 'producto', 'mi_producto');
    $rutas = $service->guardar();
    
    // Ajustar expectativas a lo que realmente genera tu service
    expect($rutas[0]['png'])->toContain('imagenes/producto/mi-producto');
    expect($rutas[0]['webp'])->toContain('imagenes/producto/mi-producto');
});

test('procesa diferentes tipos de contenido', function () {
    $tipos = ['usuario', 'evento', 'album', 'noticia', 'producto'];
    $tiposEsperados = [
        'usuario' => 'usuarios',    // Se pluraliza
        'evento' => 'eventos',      // Se pluraliza
        'album' => 'album',         // Singular
        'noticia' => 'noticia',     // Singular
        'producto' => 'producto'    // Singular
    ];
    
    foreach ($tipos as $tipo) {
        $file = UploadedFile::fake()->image("test_{$tipo}.jpg");
        $service = new ImageService($file, $tipo, "test_{$tipo}");
        $rutas = $service->guardar();
        
        $tipoEsperado = $tiposEsperados[$tipo];
        $nombreEsperado = str_replace('_', '-', "test_{$tipo}"); // _ se convierte en -
        
        expect($rutas[0]['png'])->toContain("imagenes/{$tipoEsperado}/{$nombreEsperado}");
        expect($rutas[0]['webp'])->toContain("imagenes/{$tipoEsperado}/{$nombreEsperado}");
    }
});

test('marca imagen principal correctamente en múltiples archivos', function () {
    $files = [
        UploadedFile::fake()->image('test1.jpg'),
        UploadedFile::fake()->image('test2.jpg'),
        UploadedFile::fake()->image('test3.jpg')
    ];
    
    $service = new ImageService($files, 'album', 'mi_album', true, 1); // principal = índice 1
    $rutas = $service->guardar();
    
    expect($rutas[0]['principal'])->toBeFalse();
    expect($rutas[1]['principal'])->toBeTrue();  // Esta debería ser principal
    expect($rutas[2]['principal'])->toBeFalse();
});