<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RedSocial;

class RedSocialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $redes = [
            [
                "redSocial" => "Youtube",
                "url" => "https://www.youtube.com/@victoriavmc"
            ],
            [
                "redSocial" => "Spotify",
                "url" => "https://open.spotify.com/user/11161396267?si=0JL1tU2FRF-vYQ9g0IYCmQ&nd=1&dlsi=ee41c2168a2244ca"
            ],
            [
                "redSocial" => "Instagram",
                "url" => "https://www.instagram.com/_victoriavmc_/"
            ],
            [
                "redSocial" => "Deezer",
                "url" => "https://www.deezer.com/es/"            
            ],
            [
                "redSocial" => "iTunes",
                "url" => "https://www.apple.com/es/itunes/"]
        ];

        foreach ($redes as $data) {
            RedSocial::updateOrCreate(
                ['redSocial' => $data['redSocial']],
                ['url' => $data['url']]
            );
        }
    }
}