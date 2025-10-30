<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\UploadedFile; // Importante para el type-hinting

class ImageService
{
    /**
     * El manager de Intervention Image.
     */
    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Guarda las imágenes en el storage según el tipo especificado
     *
     * @param UploadFile|array $file Archivo(s) subido(s) (UploadedFile o array de UploadedFile)
     * @param string $tipo Tipo (producto, usuario, etc.)
     * @param string $nombreBase Nombre base para el archivo (sin slug)
     * @param bool $multiple Indica si $file es un array de archivos
     * @param int|null $principalIndex Índice del archivo que es principal
     * @return array Rutas de las imágenes procesadas (PNG y WebP)
     * @throws \Exception Si el tipo de imagen no es soportado
     */
    public function guardar($file, string $tipo, string $nombreBase, bool $multiple = false, ?int $principalIndex = null): array
    {
        $nombreBaseSlug = Str::slug($nombreBase);

        $path = match ($tipo) {
            'usuario' => "imagenes/usuarios/{$nombreBaseSlug}",
            'evento' => "imagenes/eventos/{$nombreBaseSlug}",
            'album' => "imagenes/album/{$nombreBaseSlug}",
            'noticia' => "imagenes/noticia/{$nombreBaseSlug}",
            'producto' => "imagenes/producto/{$nombreBaseSlug}",
            default => throw new \Exception("Tipo de imagen no soportado: {$tipo}")
        };

        if ($multiple && is_array($file)) {
            $rutas = [];
            foreach ($file as $index => $imagen) {
                $nombreFinal = "{$path}_{$index}";
                $rutas[] = $this->procesarImagen($imagen, $nombreFinal, $index, $principalIndex);
            }
            return $rutas;
        }

        $nombreFinal = "{$path}";
        return [$this->procesarImagen($file, $nombreFinal, 0, $principalIndex)];
    }

    /**
     * Convierte la imagen a PNG y WebP y las almacena
     *
     * @param mixed $file Archivo de imagen (UploadedFile)
     * @param string $path Ruta sin extensión
     * @param int|null $index Índice para múltiples imágenes
     * @param int|null $principalIndex Índice que marca la imagen principal
     * @return array Rutas PNG, WebP y si es principal
     */
    private function procesarImagen($file, string $path, ?int $index = null, ?int $principalIndex = null): array
    {
        $img = $this->manager->read($file);

        // Opcional: Redimensionar si es necesario
        // $img->scaleDown(width: 1080); // Por ejemplo

        // Guardar en PNG
        $pngPath = "{$path}.png";
        Storage::put($pngPath, $img->toPng());

        // Guardar en WebP
        $webpPath = "{$path}.webp";
        Storage::put($webpPath, $img->toWebp(70));

        return [
            'png' => $pngPath,
            'webp' => $webpPath,
            'principal' => ($principalIndex !== null && $principalIndex === $index),
        ];
    }

    /**
     * Elimina las imágenes del storage (versión implementada)
     *
     * @param array|null $rutasArray El array de rutas ['png' => 'path.png', 'webp' => 'path.webp']
     * @return bool True si se eliminaron, false si hubo un error o no se proporcionaron rutas.
     */
    public function eliminar(?array $rutasArray): bool
    {
        if (is_array($rutasArray) && !empty($rutasArray['png']) && !empty($rutasArray['webp'])) {
            // Elimina ambos archivos
            return Storage::delete([$rutasArray['png'], $rutasArray['webp']]);
        }

        if (is_string($rutasArray) && Storage::exists($rutasArray)) {
            return Storage::delete($rutasArray);
        }

        return false;
    }
}
