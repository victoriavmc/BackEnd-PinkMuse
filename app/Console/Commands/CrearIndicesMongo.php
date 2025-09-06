<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Evento;
use App\Models\Album;
use App\Models\Noticia;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Comprobante;
use App\Models\RedSocial;

class CrearIndicesMongo extends Command
{
    protected $signature = 'app:crear-indices-mongo';
    protected $description = 'Crea los índices únicos en MongoDB Atlas para todos los modelos';

    public function handle()
    {
        // Usuarios
        Usuario::raw(function($collection){
            $collection->createIndex(['correo' => 1], ['unique' => true, 'name' => 'correo']);
            $collection->createIndex(['perfil.username' => 1], ['unique' => true, 'name' => 'perfil_username']);
        });

        // Eventos
        Evento::raw(function($collection){
            $collection->createIndex(['nombreEvento' => 1], ['unique' => true, 'name' => 'nombreEvento']);
        });

        // Albums
        Album::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'album_nombre']);
            $collection->createIndex(['canciones.titulo' => 1], ['unique' => true, 'sparse' => true, 'name' => 'canciones_titulo']);
        });

        // Noticias
        Noticia::raw(function($collection){
            $collection->createIndex(['titulo' => 1], ['unique' => true, 'name' => 'noticia_titulo']);
        });

        // Productos
        Producto::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'producto_nombre']);
        });

        // Roles
        Rol::raw(function($collection){
            $collection->createIndex(['rol' => 1], ['unique' => true, 'name' => 'nombreRol']);
        });

        // Comprobantes
        Comprobante::raw(function($collection){
            $collection->createIndex(['numeroComprobante' => 1], ['unique' => true, 'name' => 'numeroComprobante']);
        });

        // Redes Sociales
        RedSocial::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'redSocial_nombre']);
        });

        $this->info("✅ Índices únicos creados correctamente en MongoDB Atlas.");
    }
}