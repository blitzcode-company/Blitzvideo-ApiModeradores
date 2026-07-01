<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blitzvideo\Reporta;
use App\Models\Blitzvideo\Video;
use App\Models\Blitzvideo\User;
use App\Models\ActividadModeracion;

use App\Models\User as Moderador;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;


use App\Mail\VideoBloqueado;
use App\Mail\VideoDesbloqueado;
use App\Mail\ReporteResuelto;


class ReportaController extends Controller
{
     public function bloquearDesbloquearVideo(Request $request, $videoId, $accion)
    {
        try {
            $video = Video::with('canal.user')->find($videoId);

            if (!$video) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Video no encontrado.'
                ], 404);
            }

            if (!in_array($accion, ['bloquear', 'desbloquear'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acción no válida. Use "bloquear" o "desbloquear".'
                ], 400);
            }

            $moderador = $this->validarYObtenerModerador($request);
            if (!$moderador) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Moderador no encontrado.'
                ], 404);
            }

            $video->bloqueado = ($accion === 'bloquear');
            $video->save();

            $this->actividadDeBloqueo($moderador, $video, $accion);
            try {
                $propietario = $video->canal->user ?? null;
                
                if ($propietario && $propietario->email) {
                    if ($accion === 'bloquear') {
                        Mail::to($propietario->email)
                            ->queue(new VideoBloqueado(
                                $video,
                                $request->motivo ?? 'Violación de las normas de la comunidad',
                                $request->detalles ?? 'El video ha sido bloqueado por incumplir las normas.'
                            ));
                        
                        Log::info('Correo de bloqueo enviado', [
                            'video_id' => $video->id,
                            'usuario_id' => $propietario->id,
                            'email' => $propietario->email
                        ]);
                    } else {
                        Mail::to($propietario->email)
                            ->queue(new VideoDesbloqueado(
                                $video,
                                $request->motivo ?? 'Tu video ha sido restaurado'
                            ));
                        
                        Log::info('Correo de desbloqueo enviado', [
                            'video_id' => $video->id,
                            'usuario_id' => $propietario->id,
                            'email' => $propietario->email
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de video: ' . $e->getMessage(), [
                    'video_id' => $video->id,
                    'accion' => $accion
                ]);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'El video '. $video->id . ' fue ' . ($video->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en bloquearDesbloquearVideo: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resolverReporteVideo(Request $request, $reporteId)
    {
        $reporte = Reporta::find($reporteId);

        if (!$reporte) {
            return response()->json([
                'status' => 'error',
                'message' => 'Reporte no encontrado.'
            ], 404);
        }

        $moderador = $this->validarYObtenerModerador($request);

        if (!$moderador) {
            return response()->json([
                'status' => 'error',
                'message' => 'Moderador no encontrado.'
            ], 404);
        }

        $reporte->estado = 'resuelto';
        $reporte->revisado_en = now();
        $reporte->save();

        $this->actividadDeReportes($moderador, $reporte, 'resolvio reporte');

        return response()->json([
            'status' => 'success',
            'message' => 'Reporte de video resuelto exitosamente.'
        ]);
    }

    public function ListarReportes()
    {
        $reportes = Reporta::with(['user', 'video.canal.user' ])->get();

        return response()->json([
            'reportes' => $reportes
        ], 200);
    }

   public function obtenerDetalleReporteVideo($reporteId)
    {
        $reporte = Reporta::with('user', 'video')->find($reporteId);
    
        if ($reporte) {
            return response()->json([
                'reportes' => $reporte
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Reporte no encontrado.'
        ], 404);
    }
   

    public function ModificarReporte(Request $request, $reporteId)
    {
        $validatedData = $request->validate([
            'detalle' => 'nullable|string',
            'contenido_inapropiado' => 'boolean',
            'spam' => 'boolean',
            'contenido_enganoso' => 'boolean',
            'violacion_derechos_autor' => 'boolean',
            'incitacion_al_odio' => 'boolean',
            'violencia_grafica' => 'boolean',
            'otros' => 'boolean',
        ]);

        $reporte = Reporta::findOrFail($reporteId);
        $reporte->update($validatedData);

        return response()->json([
            'message' => 'Reporte modificado exitosamente.',
            'reporte' => $reporte
        ], 200);
    }

    public function BorrarReporte($reporteId)
    {
        $reporte = Reporta::find($reporteId);

        if ($reporte) {
            $reporte->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Reporte de video eliminado correctamente.'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Reporte no encontrado.'
        ], 404);
    }

  
    public function listarReportesResueltosVideos()
    {
        $reportesResueltos = Reporta::where('estado', Reporta::ESTADO_RESUELTO)
                                    ->with(['user', 'video.canal.user' ])->get();

        return response()->json([
            'reportes' => $reportesResueltos
        ]);
    }

    public function listarReportesNoResueltosVideos()
    {
        $reportesNoResueltos =Reporta::where('estado', Reporta::ESTADO_PENDIENTE)
                                       ->with(['user', 'video.canal.user' ])->get();

        return response()->json([
            'reportes' => $reportesNoResueltos
        ]);
    }

    private function validarYObtenerModerador(Request $request)
    {
        $validatedData = $request->validate([
            'moderador_id' => 'required|exists:moderadores,id',
        ]);
    
        return Moderador::find($validatedData['moderador_id']);
    }

    private function actividadDeBloqueo($moderador, $video, $accion) {
        $accionTexto = $this->obtenerTextoAccionBloqueo($accion);

        ActividadModeracion::create([
            'moderador_id' => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => $accionTexto . ' video',
            'video_id' => $video->id,
            'detalles' => "El moderador {$moderador->name} {$accionTexto} el video {$video->titulo} del canal '{$video->canal->nombre}'.",
        ]);
    }

    private function actividadDeReportes($moderador, $reporte, $accion) {
        ActividadModeracion::create([
            'moderador_id' => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => 'resolvió reporte',
            'reporte_id' => $reporte->id,
            'video_id' => $reporte->video_id,
            'detalles' => 'El moderador ' . $moderador->name . ' marcó el reporte ' . $reporte->id . ' como resuelto.',
        ]);

    }

    private function obtenerTextoAccionBloqueo($accion)
    {
        return match ($accion) {
            'bloquear' => 'bloqueó',
            'desbloquear' => 'desbloqueó',
            default => $accion,
        };
    }

    public function conteoVideos()
    {
        return response()->json([
            'pendientes' => Reporta::where('estado', Reporta::ESTADO_PENDIENTE)->count(),
            'resueltos' => Reporta::where('estado', Reporta::ESTADO_RESUELTO)->count(),
        ]);
    }

}
