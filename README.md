# 🚀 Guía para Comenzar

Antes de iniciar el desarrollo, es fundamental contar con un entorno bien configurado. Esta guía te acompaña paso a paso en la instalación de herramientas clave, la conexión con MongoDB Atlas y la creación de índices únicos.

---

## 🛠️ 1. Configuración del Entorno

### 🔧 Git

Git permite llevar control de versiones y colaborar en equipo.

\`\`\`bash
git config --global user.name "VictoriaVMC"
git config --global user.email "victoriavmc@gmail"
git --version
\`\`\`

### 🖥️ Visual Studio Code

Editor recomendado por su flexibilidad y extensiones útiles.

**Extensiones sugeridas:**

-   GitHub Pull Requests and Issues
-   PHP Intelephense
-   Laravel Blade Snippets
-   React Developer Tools

---

## 2. Instalación de PHP y Composer

En Fedora:

\`\`\`bash
sudo dnf install -y php
\`\`\`

Composer:

\`\`\`bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', '...') === '...') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
composer -V
\`\`\`

---

## 🌐 3. Laravel

\`\`\`bash
composer global require laravel/installer
laravel new nombre-proyecto
php artisan serve
\`\`\`

---

## 4. MongoDB + Laravel

### 🔌 Instalación de la extensión MongoDB para PHP

Verificar versión:

\`\`\`bash
php -v
\`\`\`

Instalar paquetes:

\`\`\`bash
sudo dnf install php-devel php-pear
yes '' | sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee /etc/php.d/40-mongodb.ini
php -m | grep mongodb
\`\`\`

Instalar drivers en Laravel:

\`\`\`bash
composer require mongodb/laravel-mongodb
\`\`\`

---

## ☁️ 5. MongoDB Atlas

1. Crear cuenta y clúster.
2. Configurar usuario, contraseña y base de datos.
3. Obtener URI de conexión.

### 🔧 Configuración en Laravel

**.env**

\`\`\`ini
DB_CONNECTION=mongodb
DB_HOST='info'
DB_PORT=27017
DB_DATABASE=nombre_de_tu_bd
DB_USERNAME='creas usuario'
DB_PASSWORD='creas contrasenia'
\`\`\`

**config/database.php**

\`\`\`php
'mongodb' => [
'driver' => 'mongodb',
'dsn' => 'mongodb+srv://prueba:prueba@nombreBd.shm2uul.mongodb.net/nombreBd?retryWrites=true&w=majority&appName=nombreBd',
'database' => env('DB_DATABASE', 'nombreBd'),
'options' => [
'ssl' => true, //escencial para atlas
],
],
\`\`\`

### Probar conexión

\`\`\`bash
php artisan tinker
DB::connection('mongodb')->getClient()->listDatabases();
\`\`\`

---

## 🧱 6. Estructura de Modelos y Migraciones

### Convenciones

| Concepto    | Convención                 |
| ----------- | -------------------------- |
| Modelo      | Singular (Usuario)         |
| Colección   | Plural (usuarios)          |
| Controlador | Plural (UsuarioController) |

### Ejemplo de modelo MongoDB

\`\`\`php

<?php
use Jenssegers\Mongodb\Eloquent\Model;

class Rol extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'rols'; //definis el nuevo nombre
    protected $fillable = ['rol', 'permisos'];
}
\`\`\`

### Crear modelo + migración + controlador

\`\`\`bash
php artisan make:model Student -m --resource
\`\`\`

⚠️ Aunque las migraciones no se usan directamente con MongoDB, es recomendable ejecutarlas para mantener consistencia:

\`\`\`bash
php artisan migrate
\`\`\`

---

## 🧠 7. Comando para Crear Índices Únicos

### Crear el comando

\`\`\`bash
php artisan make:command CrearAtributosUnicosOIndicesMongo
\`\`\`

### Contenido del comando

\`\`\`php
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
        Usuario::raw(function($collection){
            $collection->createIndex(['correo' => 1], ['unique' => true, 'name' => 'correo']);
            $collection->createIndex(['perfil.username' => 1], ['unique' => true, 'name' => 'perfil_username']);
        });

        Evento::raw(function($collection){
            $collection->createIndex(['nombreEvento' => 1], ['unique' => true, 'name' => 'nombreEvento']);
        });

        Album::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'album_nombre']);
            $collection->createIndex(['canciones.titulo' => 1], ['unique' => true, 'sparse' => true, 'name' => 'canciones_titulo']);
        });

        Noticia::raw(function($collection){
            $collection->createIndex(['titulo' => 1], ['unique' => true, 'name' => 'noticia_titulo']);
        });

        Producto::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'producto_nombre']);
        });

        Rol::raw(function($collection){
            $collection->createIndex(['rol' => 1], ['unique' => true, 'name' => 'rol']);
        });

        Comprobante::raw(function($collection){
            $collection->createIndex(['numeroComprobante' => 1], ['unique' => true, 'name' => 'numeroComprobante']);
        });

        RedSocial::raw(function($collection){
            $collection->createIndex(['nombre' => 1], ['unique' => true, 'name' => 'redSocial_nombre']);
        });

        $this->info("✅ Índices únicos creados correctamente en MongoDB Atlas.");
    }
}
\`\`\`

### Ejecutar el comando

\`\`\`bash
php artisan app:CrearAtributosUnicosOIndicesMongo
\`\`\`

### ✅ Ventajas del comando de índices

- Evita crear índices en cada inserción.  
- Mantiene los modelos limpios.  
- Fácil de mantener y extender.  
- Compatible con MongoDB Atlas sin depender de migraciones SQL.
