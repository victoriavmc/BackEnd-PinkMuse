<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                "rol" => "administrador",
                "permisos" => [
                    [
                        "modulo" => "noticias",
                        "acciones" => ["crear", "editar", "eliminar", "publicar", "ver", "accionesLike", "accionesDislike"]
                    ],
                    [
                        "modulo" => "eventos",
                        "acciones" => ["crear", "editar", "eliminar", "ver"]
                    ],
                    [
                        "modulo" => "album",
                        "acciones" => ["crear", "editar", "eliminar", "ver"]
                    ],
                    [
                        "modulo" => "productos",
                        "acciones" => ["crear", "editar", "eliminar", "ver"]
                    ],
                    [
                        "modulo" => "usuarios",
                        "acciones" => ["ver", "modificar", "eliminar"]
                    ],
                    [
                        "modulo" => "comprobantes",
                        "acciones" => ["ver"]
                    ]
                ]
            ],
            [
                "rol" => "fan",
                "permisos" => [
                    [
                        "modulo" => "contenidos",
                        "acciones" => ["ver", "comentar", "accionesLike", "accionesDislike"]
                    ],
                    [
                        "modulo" => "album",
                        "acciones" => ["ver"]
                    ],
                    [
                        "modulo" => "eventos",
                        "acciones" => ["ver", "comprar"]
                    ],
                    [
                        "modulo" => "productos",
                        "acciones" => ["ver", "comprar", "comentar"]
                    ],
                    [
                        "modulo" => "perfil",
                        "acciones" => ["ver", "editar", "eliminar"]
                    ]
                ]
            ]
        ];

        // Evita duplicados si ya existen roles
        foreach ($roles as $data) {
            Rol::updateOrCreate(
                ['rol' => $data['rol']],
                ['permisos' => $data['permisos']]
            );
        }
    }
}