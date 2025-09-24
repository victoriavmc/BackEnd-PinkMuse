<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;

class ImageService
{
    private $file;
    private string $tipo;
    private string $nombreBase;
    private bool $multiple;
    private ?int $principalIndex;
    private ImageManager $manager;

    public function __construct($file, string $tipo, string $nombreBase, bool $multiple = false, ?int $principalIndex = null)
    {
        $this->file = $file;
        $this->tipo = $tipo;
        $this->nombreBase = Str::slug($nombreBase);
        $this->multiple = $multiple;
        $this->principalIndex = $principalIndex;
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Guarda las imágenes en el storage según el tipo especificado
     * 
     * @return array Rutas de las imágenes procesadas (PNG y WebP)
     * @throws \Exception Si el tipo de imagen no está soportado
     */
    public function guardar(): array
    {
        $path = match ($this->tipo) {
            'usuario' => "imagenes/usuarios/{$this->nombreBase}",
            'evento' => "imagenes/eventos/{$this->nombreBase}",
            'album' => "imagenes/album/{$this->nombreBase}",
            'noticia' => "imagenes/noticia/{$this->nombreBase}",
            'producto' => "imagenes/producto/{$this->nombreBase}",
            default => throw new \Exception("Tipo de imagen no soportado: {$this->tipo}")
        };

        if ($this->multiple && is_array($this->file)) {
            $rutas = [];
            foreach ($this->file as $index => $imagen) {
                $nombreFinal = "{$path}_{$index}";
                $rutas[] = $this->procesarImagen($imagen, $nombreFinal, $index);
            }
            return $rutas;
        }

        $nombreFinal = "{$path}";
        return [$this->procesarImagen($this->file, $nombreFinal)];
    }

    /**
     * Convierte la imagen a PNG y WebP y las almacena
     * 
     * @param mixed $file Archivo de imagen
     * @param string $path Ruta sin extensión
     * @param int|null $index Índice para múltiples imágenes
     * @return array Rutas PNG, WebP y si es principal
     */
    private function procesarImagen($file, string $path, ?int $index = null): array
    {
        $img = $this->manager->read($file);

        // Guardar en PNG
        $pngPath = "{$path}.png";
        Storage::put($pngPath, $img->toPng());

        // Guardar en WebP
        $webpPath = "{$path}.webp";
        Storage::put($webpPath, $img->toWebp(70));

        return [
            'png' => $pngPath,
            'webp' => $webpPath,
            'principal' => ($this->principalIndex !== null && $this->principalIndex === $index),
        ];
    }

    /**
     * Elimina las imágenes del storage
     * 
     * @return void
     */
    public function eliminar()
    {
        //
    }
}
