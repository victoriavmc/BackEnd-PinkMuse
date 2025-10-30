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
    private string $disk;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
        $this->disk = config('filesystems.image_disk', config('filesystems.default', 'public'));
    }

    /**
     * Guarda las im?genes en el storage seg?n el tipo especificado
     *
     * @param UploadFile|array $file Archivo(s) subido(s) (UploadedFile o array de UploadedFile)
     * @param string $tipo Tipo (producto, usuario, etc.)
     * @param string $nombreBase Nombre base para el archivo (sin slug)
     * @param bool $multiple Indica si $file es un array de archivos
     * @param int|null $principalIndex ?ndice del archivo que es principal
     * @return array Rutas de las im?genes procesadas (PNG y WebP)
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
     * @param string $path Ruta sin extensi?n
     * @param int|null $index ?ndice para m?ltiples im?genes
     * @param int|null $principalIndex ?ndice que marca la imagen principal
     * @return array Rutas PNG, WebP y si es principal
     */
    private function procesarImagen($file, string $path, ?int $index = null, ?int $principalIndex = null): array
    {
        $img = $this->manager->read($file);

        // Opcional: Redimensionar si es necesario
        // $img->scaleDown(width: 1080); // Por ejemplo

        // Guardar en PNG
        $disk = Storage::disk($this->disk);
        $pngPath = "{$path}.png";
        $disk->put($pngPath, $img->toPng());

        // Guardar en WebP
        $webpPath = "{$path}.webp";
        $disk->put($webpPath, $img->toWebp(70));

        return [
            'png' => $pngPath,
            'webp' => $webpPath,
            'principal' => ($principalIndex !== null && $principalIndex === $index),
        ];
    }

    /**
     * Elimina las im?genes del storage (versi?n implementada)
     *
     * @param mixed $entrada Rutas a eliminar (cadena o arreglo con rutas generadas)
     * @return bool True si se eliminaron, false si hubo un error o no se proporcionaron rutas.
     */
    public function eliminar($entrada): bool
    {
        $paths = $this->extractPaths($entrada);

        if (empty($paths)) {
            return false;
        }

        $disk = Storage::disk($this->disk);
        $objetivos = [];

        foreach ($paths as $path) {
            $objetivos[] = $path;
            $alterno = $this->buildAlternatePath($path);
            if ($alterno) {
                $objetivos[] = $alterno;
            }
        }

        $deleted = false;
        foreach (array_unique($objetivos) as $objetivo) {
            if ($disk->exists($objetivo) && $disk->delete($objetivo)) {
                $deleted = true;
            }
        }

        return $deleted;
    }

    private function extractPaths($entrada): array
    {
        if (is_string($entrada)) {
            $normalized = $this->normalizePath($entrada);
            if ($normalized && !str_starts_with($normalized, 'http')) {
                return [$normalized];
            }
            return [];
        }

        if (is_array($entrada)) {
            $paths = [];
            foreach ($entrada as $value) {
                if (is_string($value)) {
                    $normalized = $this->normalizePath($value);
                    if ($normalized && !str_starts_with($normalized, 'http')) {
                        $paths[] = $normalized;
                    }
                } elseif (is_array($value)) {
                    $paths = array_merge($paths, $this->extractPaths($value));
                }
            }
            return array_filter($paths);
        }

        return [];
    }

    private function buildAlternatePath(string $path): ?string
    {
        $info = pathinfo($path);
        if (empty($info['extension'])) {
            return null;
        }

        $extension = strtolower($info['extension']);
        if (!in_array($extension, ['png', 'webp'], true)) {
            return null;
        }

        $alternateExtension = $extension === 'png' ? 'webp' : 'png';
        $dirname = $info['dirname'] ?? '';
        $dirname = ($dirname === '.' || $dirname === '') ? '' : $dirname . '/';

        return $dirname . $info['filename'] . '.' . $alternateExtension;
    }
    private function normalizePath(string $path): string
    {
        $trimmed = ltrim($path, '/');

        if (str_starts_with($trimmed, 'storage/')) {
            $trimmed = substr($trimmed, strlen('storage/'));
        }

        return $trimmed;
    }
}



