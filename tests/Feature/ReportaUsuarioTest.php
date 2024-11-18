<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\ReportaUsuario;
use App\Models\UsuarioSitio;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ReportaUsuarioTest extends TestCase
{
    use WithoutMiddleware;

    protected function baseUrl()
    {
        return env('BLITZVIDEO_MODERADORES_BASE_URL');
    }
    /** @test */

    public function testPuedeListarTodosLosReportes()
    {
        $response = $this->getJson($this->baseUrl() . "usuario/reporte");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                '*' => [ 
                    'id',
                    'id_reportante',
                    'id_reportado',
                    'ciberacoso',
                    'privacidad',
                    'suplantacion_identidad',
                    'amenazas',
                    'incitacion_odio',
                    'otros',
                    'detalle',
                    'estado',
                    'revisado_en',
                    'reportante' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'reportado' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);
    }
        /** @test */

    public function testPuedeModificarReporte()
    {
        $reporte = ReportaUsuario::first(); 

        $data = [
            'detalle' => 'Detalle actualizado desde test',
        ];

        $response = $this->putJson($this->baseUrl() . "usuario/reporte/{$reporte->id}", $data);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Reporte modificado exitosamente.',
                     'reportes' => $data
                 ]);
        $this->assertDatabaseHas('reporta_usuario', $data);
    }
    /** @test */


    public function testSePuedeEliminarUnReporte()
    {
        $reporteId = 5;

        $response = $this->deleteJson($this->baseUrl() . "usuario/reporte/{$reporteId}");
        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Reporte eliminado correctamente.'
        ]);

        $this->assertNull(ReportaUsuario::find($reporteId));
    }
    /** @test */

    public function testSePuedeBloquearODesbloquearUnUsuario()
    {
        $user = UsuarioSitio::first();
    
        $response = $this->putJson($this->baseUrl() . "usuario/{$user->id}/bloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "Usuario bloqueado correctamente."
        ]);
    
        $user->refresh();
        $this->assertEquals(1, $user->bloqueado);  
    
        $response = $this->putJson($this->baseUrl() . "usuario/{$user->id}/desbloquear");
        $response->assertStatus(200);
        $response->assertJson([
            'message' => "Usuario desbloqueado correctamente."
        ]);
    
        $user->refresh();
        $this->assertEquals(0, $user->bloqueado); 
    }
    /** @test */

    public function testSePuedeObtenerDetalleDeUnReporte()
    {
        $reporta = ReportaUsuario::first();

        $response = $this->getJson($this->baseUrl() . "usuario/reporte/{$reporta->id}");
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'reportes' => [
                'id',
                'id_reportante',
                'id_reportado',
                'ciberacoso',
                'privacidad',
                'suplantacion_identidad',
                'amenazas',
                'incitacion_odio',
                'otros',
                'detalle',
                'estado',
                'revisado_en',
                'reportante' => [
                    'id',
                    'name',
                    'email',
                ],
                'reportado' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
    }

    /** @test */
    public function testSePuedeResolverUnReporte()
    {
        $reporta = ReportaUsuario::first();

        $response = $this->putJson($this->baseUrl() . "usuario/reporte/{$reporta->id}/resolver");
        $response->assertStatus(200);
        $response->assertJson([
              'status' => 'success',
            'message' => 'Reporte de usuario resuelto exitosamente.'
        ]);

        $reporta->refresh();
        $this->assertEquals('resuelto', $reporta->estado);
    }

    /** @test */
    public function testSePuedeListarReportesResueltos()
{
    $reporta = new ReportaUsuario([
        'id_reportante' => 3, 
        'id_reportado' => 4, 
        'detalle' => 'Detalle de prueba',
        'ciberacoso' => true,
        'privacidad' => false,
        'suplantacion_identidad' => false,
        'amenazas' => true,
        'incitacion_odio' => false,
        'estado' => 'resuelto',
        'otros' => false,
    ]);
    
    $reporta->save();

    $response = $this->getJson($this->baseUrl() . "usuario/reporte/resueltos");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'reportes' => [
            '*' => [ 
                'id',
                'id_reportante',
                'id_reportado',
                'ciberacoso',
                'privacidad',
                'suplantacion_identidad',
                'amenazas',
                'incitacion_odio',
                'otros',
                'detalle',
                'estado',
                'revisado_en',
                'reportante' => [
                    'id',
                    'name',
                    'email',
                ],
                'reportado' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ],
    ]);
}


    /** @test */
    public function testSePuedeListarReportesNoResueltos()
    {
        $reporta = ReportaUsuario::where('estado', 'pendiente')->first(); 

        $this->assertNotNull($reporta, "No se encontraron reportes pendientes.");
    
        $response = $this->getJson($this->baseUrl() . "usuario/reporte/no-resueltos");
    
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
         $response = $this->getJson($this->baseUrl() . 'usuario/conteo');
 
         $response->assertStatus(200)
                  ->assertJsonStructure([
                      'pendientes',
                      'resueltos'
                  ]);
     }
}
