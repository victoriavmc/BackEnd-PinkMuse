<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Evento;

class EventoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $eventos = [
            [
                "nombreEvento" => "Amanecer Musical",
                "nombreLugar" => "Teatro Aurora",
                "lugar" => "Buenos Aires",
                "fecha" => "2015-03-12",
                "hora" => "20:00",
                "maps" => "https://goo.gl/maps/teatroaurora",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_block_1.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>35, "precio" => 6000],
                    ["tipo" => "VIP","cantidad"=>30, "precio" => 9000]
                ],
                "artistasExtras" => ["Luna Nova"],
                "estado" => "Finalizado"
            ],
            [
                "nombreEvento" => "Noche de Estrellas",
                "nombreLugar" => "Centro Cultural Rivera",
                "lugar" => "Rosario",
                "fecha" => "2016-06-25",
                "hora" => "21:30",
                "maps" => "https://goo.gl/maps/ccrivera",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_block_2.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>15, "precio" => 7000],
                    ["tipo" => "VIP","cantidad"=>25, "precio" => 12000]
                ],
                "artistasExtras" => ["Ecos del Sur"],
                "estado" => "Finalizado"
            ],
            [
                "nombreEvento" => "Festival Aurora",
                "nombreLugar" => "Parque del Sol",
                "lugar" => "Córdoba",
                "fecha" => "2017-02-10",
                "hora" => "18:00",
                "maps" => "https://goo.gl/maps/parquedelsol",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_block_3.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>55, "precio" => 8000],
                    ["tipo" => "VIP","cantidad"=>65, "precio" => 15000]
                ],
                "artistasExtras" => ["Sideral", "Nova"],
                "estado" => "Cancelado"
            ],
            [
                "nombreEvento" => "Tardes de Otoño",
                "nombreLugar" => "Auditorio del Valle",
                "lugar" => "Mendoza",
                "fecha" => "2018-05-19",
                "hora" => "19:00",
                "maps" => "https://goo.gl/maps/auditoriovalle",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_1.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>75, "precio" => 5000],
                    ["tipo" => "VIP","cantidad"=>85, "precio" => 9000]
                ],
                "artistasExtras" => ["Grupo Horizonte"],
                "estado" => "Cancelado"
            ],
            [
                "nombreEvento" => "Verano Infinito",
                "nombreLugar" => "Costanera Arena",
                "lugar" => "Posadas",
                "fecha" => "2019-01-23",
                "hora" => "20:30",
                "maps" => "https://goo.gl/maps/costaneraarena",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_2.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>995, "precio" => 9000],
                    ["tipo" => "VIP","cantidad"=>5, "precio" => 13000]
                ],
                "artistasExtras" => ["Solar Beat"],
                "estado" => "Finalizado"
            ],
            [
                "nombreEvento" => "Cielos Abiertos",
                "nombreLugar" => "Anfiteatro del Lago",
                "lugar" => "Bariloche",
                "fecha" => "2020-02-14",
                "hora" => "21:00",
                "maps" => "https://goo.gl/maps/anfiteatrolago",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_3.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>15, "precio" => 8500],
                    ["tipo" => "VIP","cantidad"=>25, "precio" => 11000]
                ],
                "artistasExtras" => ["Ecos de Agua"],
                "estado" => "Suspendido"
            ],
            [
                "nombreEvento" => "Sonidos del Norte",
                "nombreLugar" => "Teatro Salta",
                "lugar" => "Salta",
                "fecha" => "2020-11-20",
                "hora" => "20:00",
                "maps" => "https://goo.gl/maps/teatrosalta",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_4.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>25, "precio" => 7000],
                    ["tipo" => "VIP","cantidad"=>25, "precio" => 9500]
                ],
                "artistasExtras" => ["Viento del Alba"],
                "estado" => "Suspendido"
            ],
            [
                "nombreEvento" => "Ecos de Primavera",
                "nombreLugar" => "Espacio Verde",
                "lugar" => "Tucumán",
                "fecha" => "2026-09-21",
                "hora" => "17:00",
                "maps" => "https://goo.gl/maps/espacioverde",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_5.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>15, "precio" => 8000],
                    ["tipo" => "VIP","cantidad"=>35, "precio" => 12000]
                ],
                "artistasExtras" => ["Marea", "Flor del Viento"],
                "estado" => "Activo"
            ],
            [
                "nombreEvento" => "Ritmo del Río",
                "nombreLugar" => "Puerto Cultural",
                "lugar" => "Corrientes",
                "fecha" => "2021-12-12",
                "hora" => "19:00",
                "maps" => "https://goo.gl/maps/puertocultural",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_6.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>33, "precio" => 9500],
                    ["tipo" => "VIP","cantidad"=>34, "precio" => 14000]
                ],
                "artistasExtras" => ["Río Azul"],
                "estado" => "Finalizado"
            ],
            [
                "nombreEvento" => "Festival de las Luces",
                "nombreLugar" => "Predio El Sol",
                "lugar" => "San Luis",
                "fecha" => "2026-01-10",
                "hora" => "22:00",
                "maps" => "https://goo.gl/maps/predioelsol",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/blob/master/img/img_instagram_7.jpg",
                "entradas" => [
                    ["tipo" => "General","cantidad"=>35, "precio" => 10000],
                    ["tipo" => "VIP","cantidad"=>36, "precio" => 15000]
                ],
                "artistasExtras" => ["Nébula"],
                "estado" => "Activo"
            ]
        ];

        foreach ($eventos as $data) {
           Evento::updateOrCreate(
                ['nombreEvento' => ucwords(strtolower($data['nombreEvento']))],
                $data
            );
        }
    }
}