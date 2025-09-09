# 🚀 Guía para Comenzar el BACK

Antes de iniciar el desarrollo, es fundamental contar con un entorno bien configurado. Esta guía te acompaña paso a paso en la instalación de herramientas clave, la conexión con MongoDB Atlas y la creación de índices únicos.

---

## 🛠️ 1. Configuración del Entorno

### 🔧 Git

Git permite llevar control de versiones y colaborar en equipo.

```bash
git config --global user.name "VictoriaVMC"
git config --global user.email "victoriavmc@gmail"
git --version
```

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

```bash
sudo dnf install -y php
```

Composer:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', '...') === '...') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
php composer-setup.php
php -r "unlink('composer-setup.php');"
composer -V
```

---

## 🌐 3. Laravel

```bash
composer global require laravel/installer
laravel new nombre-proyecto
php artisan serve
```

---

## 4. MongoDB + Laravel

### 🔌 Instalación de la extensión MongoDB para PHP

Verificar versión:

```bash
php -v
```

Instalar paquetes:

```bash
sudo dnf install php-devel php-pear
yes '' | sudo pecl install mongodb
echo "extension=mongodb.so" | sudo tee /etc/php.d/40-mongodb.ini
php -m | grep mongodb
```

Instalar drivers en Laravel:

```bash
composer require mongodb/laravel-mongodb
```

---

## ☁️ 5. MongoDB Atlas

1. Crear cuenta y clúster.
2. Configurar usuario, contraseña y base de datos.
3. Obtener URI de conexión.

### 🔧 Configuración en Laravel

**.env**

```ini
DB_CONNECTION=mongodb
DB_HOST='info'
DB_PORT=27017
DB_DATABASE=nombre_de_tu_bd
DB_USERNAME='creas usuario'
DB_PASSWORD='creas contrasenia'
```

**config/database.php**

```php
'mongodb' => [
'driver' => 'mongodb',
'dsn' => 'mongodb+srv://prueba:prueba@nombreBd.shm2uul.mongodb.net/nombreBd?retryWrites=true&w=majority&appName=nombreBd',
'database' => env('DB_DATABASE', 'nombreBd'),
'options' => [
'ssl' => true, //escencial para atlas
],
],
```

### Probar conexión

```bash
php artisan tinker
DB::connection('mongodb')->getClient()->listDatabases();
```

---

## 🧱 6. Estructura de Modelos y Migraciones

### Convenciones

| Concepto    | Convención                 |
| ----------- | -------------------------- |
| Modelo      | Singular (Usuario)         |
| Colección   | Plural (usuarios)          |
| Controlador | Plural (UsuarioController) |

### Ejemplo de modelo MongoDB

```php

<?php
use Jenssegers\Mongodb\Eloquent\Model;

class Rol extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'rols'; //definis el nuevo nombre
    protected $fillable = ['rol', 'permisos'];
}
```

### Crear modelo + migración + controlador

```bash
php artisan make:model Student -m --api
```

⚠️ Aunque las migraciones no se usan directamente con MongoDB, es recomendable ejecutarlas para mantener consistencia:

```bash
php artisan migrate
```

---

## 🧠 7. Comando para Crear Índices Únicos

### Crear el comando

```bash
php artisan make:command CrearAtributosUnicosOIndicesMongo
```

### Ejecutar el comando

```bash
php artisan app:CrearAtributosUnicosOIndicesMongo
```

### ✅ Ventajas del comando de índices

-   Evita crear índices en cada inserción.
-   Mantiene los modelos limpios.
-   Fácil de mantener y extender.
-   Compatible con MongoDB Atlas sin depender de migraciones SQL.

### Ruta

```bash
php artisan install:api
```

Es un instalador rápido que transforma tu proyecto en un backend API con autenticación lista para usar, en lugar de un proyecto Laravel tradicional con vistas Blade.

1. Instala Sanctum (o el sistema de autenticación elegido) → por eso viste que se descargó laravel/sanctum.
2. Configura el auth para tokens → te deja lista la base para que tu aplicación emita y valide tokens de acceso.
3. Configura rutas por defecto en routes/api.php.
4. Ajusta providers y middlewares necesarios para que el proyecto funcione como API.
5. Deja el entorno limpio, sin plantillas de Blade ni cosas de frontend, ya que el frontend lo harías con React, Vue, Angular, etc.

# Rutas API en Laravel

Con APIs en Laravel (ejemplo: después de correr `php artisan install:api`), lo más común es definir las rutas en el archivo:

```bash
routes/api.php
```

Laravel ofrece una forma rápida de generar automáticamente todas las rutas necesarias para un **CRUD básico** usando:

```bash
Route::apiResource('posts', PostController::class);
```

### Rutas generadas automáticamente

Con apiResource, se crean las siguientes rutas:

-   GET /posts → index (listar recursos)
-   POST /posts → store (crear recurso)
-   GET /posts/{id} → show (mostrar recurso específico)
-   PUT/PATCH /posts/{id} → update (actualizar recurso)
-   DELETE /posts/{id} → destroy (eliminar recurso)

# Diferencia con Route::resource

-   Route::resource crea las 7 rutas: index, create, store, show, edit, update, destroy.
-   Route::apiResource crea solo las 5 necesarias para APIs (sin create ni edit, porque esas vistas las maneja el frontend).

### Probando la API

Levantas el servidor:

```bash
php artisan serve
```

### CREACION CORS

```bash
php artisan config:publish cors
```
