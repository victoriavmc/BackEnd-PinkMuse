<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producto;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productos = [
            [
                "nombre" => "FRENO DE MANO",
                "imagenPrincipal" => "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/refs/heads/master/img/img_instagram_1.jpg",
                "descripcion" => "Este nadie lo utilizó (todavía). Usalo para tu auto o para romper el hielo.",
                "precio" => 23.42,
                "estado" => "Activo",
                "stock" => [
                    "total" => 12,
                    "colores" => [
                        "Único" => [
                            "imagenes" => [
                                "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/refs/heads/master/img/img_instagram_1.jpg",
                                "https://raw.githubusercontent.com/LaboratoriaChile/portafolio-sass/refs/heads/master/img/img_instagram_2.jpg"
                            ],
                            "talles" => [
                                ["talle" => "Único", "cantidad" => 12]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => true
            ],
            [
                "nombre" => "REMERAS",
                "imagenPrincipal" => "remera.jpg",
                "descripcion" => "Remeras estampadas, 100% algodón. Ideales para toda ocasión.",
                "precio" => 15.99,
                "estado" => "Activo",
                "stock" => [
                    "total" => 71,
                    "colores" => [
                        "Negro" => [
                            "imagenes" => ["remera_negro1.jpg", "remera_negro2.jpg"],
                            "talles" => [
                                ["talle" => "S", "cantidad" => 10],
                                ["talle" => "M", "cantidad" => 12],
                                ["talle" => "L", "cantidad" => 8],
                                ["talle" => "XL", "cantidad" => 5]
                            ]
                        ],
                        "Blanco" => [
                            "imagenes" => ["remera_blanco1.jpg", "remera_blanco2.jpg"],
                            "talles" => [
                                ["talle" => "S", "cantidad" => 9],
                                ["talle" => "M", "cantidad" => 14],
                                ["talle" => "L", "cantidad" => 7],
                                ["talle" => "XL", "cantidad" => 6]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => true
            ],
            [
                "nombre" => "BUZOS",
                "imagenPrincipal" => "buzo.jpg",
                "descripcion" => "Buzos oversize con capucha. Para el frío o para facha.",
                "precio" => 29.99,
                "estado" => "Activo",
                "stock" => [
                    "total" => 53,
                    "colores" => [
                        "Negro" => [
                            "imagenes" => ["buzo_negro1.jpg", "buzo_negro2.jpg"],
                            "talles" => [
                                ["talle" => "S", "cantidad" => 6],
                                ["talle" => "M", "cantidad" => 10],
                                ["talle" => "L", "cantidad" => 7],
                                ["talle" => "XL", "cantidad" => 4]
                            ]
                        ],
                        "Blanco" => [
                            "imagenes" => ["buzo_blanco1.jpg", "buzo_blanco2.jpg"],
                            "talles" => [
                                ["talle" => "S", "cantidad" => 8],
                                ["talle" => "M", "cantidad" => 9],
                                ["talle" => "L", "cantidad" => 6],
                                ["talle" => "XL", "cantidad" => 3]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => true
            ],
            [
                "nombre" => "GORRAS",
                "imagenPrincipal" => "gorra.jpg",
                "descripcion" => "Gorras con visera curva y estilo callejero. Protegé tu flow del sol.",
                "precio" => 12.0,
                "estado" => "Activo",
                "stock" => [
                    "total" => 20,
                    "colores" => [
                        "Negro" => [
                            "imagenes" => ["gorra1.jpg", "gorra2.jpg"],
                            "talles" => [
                                ["talle" => "Único", "cantidad" => 10]
                            ]
                        ],
                        "Gris" => [
                            "imagenes" => ["gorra3.jpg", "gorra4.jpg"],
                            "talles" => [
                                ["talle" => "Único", "cantidad" => 10]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => true
            ],
            [
                "nombre" => "CARTAS DE POKÉMON",
                "imagenPrincipal" => "pokemon.jpg",
                "descripcion" => "Cartas originales, manoseadas por el Rubius, te hacen sentir poderoso. (Según Gaspi.)",
                "precio" => 19.99,
                "estado" => "Activo",
                "stock" => [
                    "total" => 60,
                    "colores" => [
                        "Único" => [
                            "imagenes" => ["pokemon1.jpg", "pokemon2.jpg"],
                            "talles" => [
                                ["talle" => "Único", "cantidad" => 60]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => true
            ],
            [
                "nombre" => "DISCO DE UNA DIRECCION + PILETA DE REGALO",
                "imagenPrincipal" => "onedirection.jpg",
                "descripcion" => "Incluye disco de 'Una Direccion' y una pileta inflable firmada por Charly García (junto a la frase 'Si sos inglés no saltes').",
                "precio" => 49.99,
                "estado" => "Activo",
                "stock" => [
                    "total" => 5,
                    "colores" => [
                        "Único" => [
                            "imagenes" => ["oned1.jpg", "pileta1.jpg"],
                            "talles" => [
                                ["talle" => "Único", "cantidad" => 5]
                            ]
                        ]
                    ]
                ],
                "habilitarAcciones" => "si",
                "habilitarComentarios" => false
            ]
        ];

        foreach ($productos as $data) {
            Producto::updateOrCreate(
                ['nombre' => strtoupper($data['nombre'])],
                $data
            );
        }
    }
}