<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                '_id' => '6907bc2212642a84a100afc2',
                'rol' => 'administrador',
                'permisos' => [
                    [
                        'modulo' => 'noticias',
                        'acciones' => ['crear', 'editar', 'eliminar', 'publicar', 'ver', 'accionesLike', 'accionesDislike']
                    ],
                    [
                        'modulo' => 'eventos',
                        'acciones' => ['crear', 'editar', 'eliminar', 'ver']
                    ],
                    [
                        'modulo' => 'album',
                        'acciones' => ['crear', 'editar', 'eliminar', 'ver']
                    ],
                    [
                        'modulo' => 'productos',
                        'acciones' => ['crear', 'editar', 'eliminar', 'ver']
                    ],
                    [
                        'modulo' => 'usuarios',
                        'acciones' => ['ver', 'modificar', 'eliminar']
                    ],
                    [
                        'modulo' => 'comprobantes',
                        'acciones' => ['ver']
                    ]
                ]
            ],
            [
                '_id' => '6907bc2212642a84a100afc3',
                'rol' => 'fan',
                'permisos' => [
                    [
                        'modulo' => 'contenidos',
                        'acciones' => ['ver', 'comentar', 'accionesLike', 'accionesDislike']
                    ],
                    [
                        'modulo' => 'album',
                        'acciones' => ['ver']
                    ],
                    [
                        'modulo' => 'eventos',
                        'acciones' => ['ver', 'comprar']
                    ],
                    [
                        'modulo' => 'productos',
                        'acciones' => ['ver', 'comprar', 'comentar']
                    ],
                    [
                        'modulo' => 'perfil',
                        'acciones' => ['ver', 'editar', 'eliminar']
                    ]
                ]
            ]
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['rol' => $rol['rol']], $rol);
        }
    }
}