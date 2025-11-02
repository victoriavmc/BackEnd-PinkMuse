<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\User;
use Illuminate\Console\Scheduling\Event;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            RedSocialSeeder::class,
            EventoSeeder::class,
            ProductoSeeder::class,
            NoticiaSeeder::class,
        ]);
    }
}