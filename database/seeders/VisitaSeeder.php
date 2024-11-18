<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UsuarioSitio;
use App\Models\Video;
use App\Models\Visita;

class VisitaSeeder extends Seeder
{
    public function run()
    {
        $usuarios = UsuarioSitio::all();
        $videos = Video::all();

        foreach ($usuarios as $usuario) {
            foreach ($videos as $video) {
                for ($i = 0; $i < 2; $i++) {
                    Visita::create([
                        'user_id' => $usuario->id,
                        'video_id' => $video->id,
                    ]);
                }
            }
        }
    }
}
