<?php

namespace Tests\Feature;

use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\ReportaComentario;
use App\Models\Comentario;
use App\Models\UsuarioSitio;
use Illuminate\Foundation\Testing\WithoutMiddleware;


class ReportaComentarioTest extends TestCase
{
    use WithoutMiddleware;

    protected function baseUrl()
    {
        return env('BLITZVIDEO_MODERADORES_BASE_URL');
    }
    /** @test */

    public function testPuedeListarTodosLosReportes()
    {
        $response = $this->getJson($this->baseUrl() . "comentario/reporte");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                '*' => [ 
                    'id',
                    'user_id',
                    'comentario_id',
                    'detalle',
                    'lenguaje_ofensivo',
                    'spam',
                    'contenido_enganoso',
                    'incitacion_al_odio',
                    'acoso',
                    'contenido_sexual',
                    'otros',
                    'estado',
                    'revisado_en',
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'comentario' => [
                        'id',
                        'usuario_id',
                        'video_id',
                        'respuesta_id',
                        'mensaje',
                        'bloqueado',
                    ],
                ],
            ],
        ]);
    }
        /** @test */

    public function testPuedeModificarReporte()
    {
        $reporte = ReportaComentario::first(); 

        $data = [
            'detalle' => 'Detalle actualizado desde test',
        ];

        $response = $this->putJson($this->baseUrl() . "comentario/reporte/{$reporte->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                    'message' => 'Reporte de comentario modificado exitosamente.',
                    'reportes' => $data
                 ]);
        $this->assertDatabaseHas('reporta_comentario', $data);
    }

    /** @test */

    public function testSePuedeEliminarUnReporte()
    {
        $reporteId = 5;

        $response = $this->deleteJson($this->baseUrl() . "comentario/reporte/{$reporteId}");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Reporte de comentario borrado exitosamente.'
        ]);

        $this->assertNull(ReportaComentario::find($reporteId));
    }
    /** @test */

    public function testSePuedeBloquearODesbloquearUnComentario()
    {
        $comentario = Comentario::first();
        
        $response = $this->putJson($this->baseUrl() . "comentario/{$comentario->id}/bloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "Comentario bloqueado correctamente."
        ]);
    
        $comentario->refresh();
        $this->assertEquals(1, $comentario->bloqueado);  
    
        $response = $this->putJson($this->baseUrl() . "comentario/{$comentario->id}/desbloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "Comentario desbloqueado correctamente."
        ]);
    
        $comentario->refresh();
        $this->assertEquals(0, $comentario->bloqueado); 
    }
    /** @test */

    public function testSePuedeObtenerDetalleDeUnReporte()
    {
        $reporta = ReportaComentario::first();

        $response = $this->getJson($this->baseUrl() . "comentario/reporte/{$reporta->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                'id',
                'user_id',
                'comentario_id',
                'detalle',
                'lenguaje_ofensivo',
                'spam',
                'contenido_enganoso',
                'incitacion_al_odio',
                'acoso',
                'contenido_sexual',
                'otros',
                'estado',
                'revisado_en',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'comentario' => [
                    'id',
                    'usuario_id',
                    'video_id',
                    'respuesta_id',
                    'mensaje',
                    'bloqueado',
                ],
            ],
        ]);
    }

    /** @test */
    public function testSePuedeResolverUnReporte()
    {
        $reporta = ReportaComentario::first();

        $response = $this->putJson($this->baseUrl() . "comentario/reporte/{$reporta->id}/resolver");
        $response->assertStatus(200);
        $response->assertJson([
                'status' => 'success',
                'message' => 'Reporte de comentario resuelto exitosamente.'
        ]);

        $reporta->refresh();
        $this->assertEquals('resuelto', $reporta->estado);
    }

    /** @test */
    public function testSePuedeListarReportesResueltos()
{
    $reporta = new ReportaComentario([
        'user_id' => 3, 
        'comentario_id' => 3, 
        'detalle' => 'Detalle de prueba',
        'lenguaje_ofensivo' => true,
        'spam' => false,
        'contenido_enganoso' => false,
        'acoso' => true,
        'incitacion_al_odio' => false,
        'contenido_sexual' => false,
        'estado' => 'resuelto',
        'otros' => false,
    ]);
    
    $reporta->save();

    $response = $this->getJson($this->baseUrl() . "comentario/reporte/resueltos");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'reportes' => [
            '*' => [ 
               'id',
                'user_id',
                'comentario_id',
                'detalle',
                'lenguaje_ofensivo',
                'spam',
                'contenido_enganoso',
                'incitacion_al_odio',
                'acoso',
                'contenido_sexual',
                'otros',
                'estado',
                'revisado_en',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
                'comentario' => [
                    'id',
                    'usuario_id',
                    'video_id',
                    'respuesta_id',
                    'mensaje',
                    'bloqueado',
                ],
            ],
        ],
    ]);
}


    /** @test */
    public function testSePuedeListarReportesNoResueltos()
    {
        $reporta = ReportaComentario::where('estado', 'pendiente')->first(); 

        $this->assertNotNull($reporta, "No se encontraron reportes pendientes.");
    
        $response = $this->getJson($this->baseUrl() . "comentario/reporte/no-resueltos");
    
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
         $response = $this->getJson($this->baseUrl() . 'comentario/conteo');
 
         $response->assertStatus(200)
                  ->assertJsonStructure([
                      'pendientes',
                      'resueltos'
                  ]);
     }

}