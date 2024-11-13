<?php

namespace Tests\Feature;


use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Reporta;
use App\Models\Video;
use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;


class ReportaTest extends TestCase
{
    use WithoutMiddleware;

    protected function baseUrl()
    {
        return env('BLITZVIDEO_MODERADORES_BASE_URL');
    }
    /** @test */

    public function testPuedeListarTodosLosReportes()
    {
        $response = $this->getJson($this->baseUrl() . "video/reporte");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                '*' => [ 
                    'id',
                    'user_id',
                    'video_id',
                    'detalle',
                    'contenido_inapropiado',
                    'spam',
                    'contenido_enganoso',
                    'violacion_derechos_autor',
                    'incitacion_al_odio',
                    'violencia_grafica',
                    'otros',
                    'estado',
                    'revisado_en',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'video' => [
                        'id',
                        'canal_id',
                        'titulo',
                        'descripcion',
                        'miniatura',
                        'duracion',
                        'bloqueado',
                        'acceso',
                        'link',
                    ],
                ],
            ],
        ]);
    }
        /** @test */

    public function testPuedeModificarReporte()
    {
        $reporte = Reporta::first(); 

        $data = [
            'detalle' => 'Detalle actualizado desde test',
        ];

        $response = $this->putJson($this->baseUrl() . "video/reporte/{$reporte->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Reporte modificado exitosamente.',
                     'reporte' => $data
                 ]);
        $this->assertDatabaseHas('reporta', $data);
    }
    /** @test */


    public function testSePuedeEliminarUnReporte()
    {
        $reporteId = 5;

        $response = $this->deleteJson($this->baseUrl() . "video/reporte/{$reporteId}");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Reporte de video eliminado correctamente.'
        ]);

        $this->assertNull(Reporta::find($reporteId));
    }
    /** @test */

    public function testSePuedeBloquearODesbloquearUnVideo()
    {
        $video = Video::first();
    
        $response = $this->putJson($this->baseUrl() . "video/{$video->id}/bloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "El video {$video->id} fue bloqueado correctamente."
        ]);
    
        $video->refresh();
        $this->assertEquals(1, $video->bloqueado);  
    
        $response = $this->putJson($this->baseUrl() . "video/{$video->id}/desbloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "El video {$video->id} fue desbloqueado correctamente."
        ]);
    
        $video->refresh();
        $this->assertEquals(0, $video->bloqueado); 
    }
    /** @test */

    public function testSePuedeObtenerDetalleDeUnReporte()
    {
        $reporta = Reporta::first();

        $response = $this->getJson($this->baseUrl() . "video/reporte/{$reporta->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                'id',
                'user_id',
                'video_id',
                'detalle',
                'contenido_inapropiado',
                'spam',
                'contenido_enganoso',
                'violacion_derechos_autor',
                'incitacion_al_odio',
                'violencia_grafica',
                'otros',
                'estado',
                'revisado_en',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'video' => [
                    'id',
                    'canal_id',
                    'titulo',
                    'descripcion',
                    'miniatura',
                    'duracion',
                    'bloqueado',
                    'acceso',
                    'link',
                ],
            ],
        ]);
    }

    /** @test */
    public function testSePuedeResolverUnReporte()
    {
        $reporta = Reporta::first();

        $response = $this->putJson($this->baseUrl() . "video/reporte/{$reporta->id}/resolver");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Reporte de video resuelto exitosamente.'
        ]);

        $reporta->refresh();
        $this->assertEquals('resuelto', $reporta->estado);
    }

    /** @test */
    public function testSePuedeListarReportesResueltos()
{
    $reporta = new Reporta([
        'user_id' => 3, 
        'video_id' => 3, 
        'detalle' => 'Detalle de prueba',
        'contenido_inapropiado' => true,
        'spam' => false,
        'contenido_enganoso' => false,
        'violacion_derechos_autor' => true,
        'incitacion_al_odio' => false,
        'violencia_grafica' => false,
        'estado' => 'resuelto',
        'otros' => false,
    ]);
    
    $reporta->save();

    $response = $this->getJson($this->baseUrl() . "video/reporte/resueltos");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'reportes' => [
            '*' => [ 
                'id',
                'user_id',
                'video_id',
                'detalle',
                'contenido_inapropiado',
                'spam',
                'contenido_enganoso',
                'violacion_derechos_autor',
                'incitacion_al_odio',
                'violencia_grafica',
                'otros',
                'estado',
                'revisado_en',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'video' => [
                    'id',
                    'canal_id',
                    'titulo',
                    'descripcion',
                    'miniatura',
                    'duracion',
                    'bloqueado',
                    'acceso',
                    'link',
                ],
            ],
        ],
    ]);
}


    /** @test */
    public function testSePuedeListarReportesNoResueltos()
    {
        $reporta = Reporta::where('estado', 'pendiente')->first(); 

        $this->assertNotNull($reporta, "No se encontraron reportes pendientes.");
    
        $response = $this->getJson($this->baseUrl() . "video/reporte/no-resueltos");
    
        $response->assertStatus(200);
        $response->assertJson([
            'reportes' => [[
                'id' => $reporta->id,
                'estado' => 'pendiente',
            ]]
        ]);
    }
     /** @test */
     public function puede_obtener_conteo_de_comentarios()
     {
         $response = $this->getJson($this->baseUrl() . 'video/conteo');
 
         $response->assertStatus(200)
                  ->assertJsonStructure([
                      'pendientes',
                      'resueltos'
                  ]);
     }

    
}
