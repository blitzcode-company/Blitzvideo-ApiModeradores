<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blitzvideo\ReportaComentario;
use App\Models\Blitzvideo\Comentario;
use App\Models\Blitzvideo\User;
use App\Models\User as Moderador;

use App\Models\ActividadModeracion;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Mail\ComentarioBloqueado;
use App\Mail\ComentarioDesbloqueado;
use App\Mail\ReporteResuelto;



class ReportaComentarioController extends Controller
{
   
    public function ListarReportes()
    {
        $reportes = ReportaComentario::with(['user', 'comentario'])->get();

        return response()->json([
            'reportes' => $reportes
        ], 200);
    }

 
    public function ModificarReporte(Request $request, $reporteId)
    {
        $validatedData = $request->validate([
            'detalle' => 'nullable|string',
            'lenguaje_ofensivo' => 'boolean',
            'spam' => 'boolean',
            'contenido_enganoso' => 'boolean',
            'incitacion_al_odio' => 'boolean',
            'acoso' => 'boolean',
            'contenido_sexual' => 'boolean',
            'otros' => 'boolean',
        ]);

        $reporte = ReportaComentario::findOrFail($reporteId);
        $reporte->update($validatedData);

        return response()->json([
            'message' => 'Reporte de comentario modificado exitosamente.',
            'reportes' => $reporte
        ], 200);
    }

    public function BorrarReporte($reporteId)
    {
        $reporte = ReportaComentario::findOrFail($reporteId);
        $reporte->delete();

        return response()->json([
            'message' => 'Reporte de comentario borrado exitosamente.'
        ], 200);
    }

   

    public function listarReportesResueltosComentarios()
    {
        $reportesResueltos = ReportaComentario::where('estado', ReportaComentario::ESTADO_RESUELTO)
                                              ->with(['user', 'comentario.user', 'comentario.video']) 
                                              ->get();



        return response()->json([
            'reportes' => $reportesResueltos
        ]);
    }

    public function listarReportesNoResueltosComentarios()
    {
        $reportesNoResueltos = ReportaComentario::where('estado', ReportaComentario::ESTADO_PENDIENTE)
                                                ->with(['user', 'comentario.user', 'comentario.video']) 
                                                ->get();

        return response()->json([
            'reportes' => $reportesNoResueltos
        ]);
    }

    public function obtenerDetalleReporteComentario($reporteId)
    {
        $reporte = ReportaComentario::with('user', 'comentario.user', 'comentario.video')->find($reporteId);
    
        if ($reporte) {
            return response()->json([
                'status' => 'success',
                'reportes' => $reporte
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Reporte no encontrado.'
        ], 404);
    }
    
      public function resolverReporteComentario(Request $request, $reporteId)
    {
        try {
            $reporte = ReportaComentario::with(['user', 'comentario.user', 'comentario.video'])->find($reporteId);
        
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
        
            // Actualizar reporte
            $reporte->estado = 'resuelto';
            $reporte->revisado_en = now();
            $reporte->save();

            // Registrar actividad
            $this->actividadDeReportes($moderador, $reporte, 'resolvió reporte');

            try {
                if ($reporte->user && $reporte->user->email) {
                    Mail::to($reporte->user->email)
                        ->queue(new ReporteResuelto(
                            (object) [
                                'id' => $reporte->id,
                                'tipo' => 'Comentario',
                                'estado' => 'resuelto'
                            ],
                            $request->resolucion ?? 'Aceptado',
                            $request->comentarios ?? 'El reporte ha sido revisado y resuelto.'
                        ));
                    
                    Log::info('Correo de reporte de comentario resuelto enviado', [
                        'reporte_id' => $reporte->id,
                        'email' => $reporte->user->email
                    ]);
                }

                if ($request->resolucion === 'Aceptado' && $request->bloquear_comentario === true) {
                    $comentario = $reporte->comentario;
                    
                    if ($comentario && $comentario->user && $comentario->user->email) {
                        Mail::to($comentario->user->email)
                            ->queue(new ComentarioBloqueado(
                                $comentario,
                                $request->motivo ?? 'Reportes de la comunidad',
                                $request->detalles ?? 'Tu comentario ha sido bloqueado por incumplir las normas.'
                            ));
                        
                        Log::info('Correo de bloqueo de comentario por reporte enviado', [
                            'comentario_id' => $comentario->id,
                            'email' => $comentario->user->email
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de reporte de comentario: ' . $e->getMessage(), [
                    'reporte_id' => $reporte->id
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Reporte de comentario resuelto exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en resolverReporteComentario: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al resolver el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
    
     public function bloquearDesbloquearComentario(Request $request, $comentarioId, $accion)
    {
        try {
            $comentario = Comentario::with(['user', 'video'])->find($comentarioId);

            if (!$comentario) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Comentario no encontrado.'
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

            $comentario->bloqueado = ($accion === 'bloquear');
            $comentario->save();

            $this->actividadDeBloqueo($moderador, $comentario, $accion);

            try {
                if ($comentario->user && $comentario->user->email) {
                    if ($accion === 'bloquear') {
                        Mail::to($comentario->user->email)
                            ->queue(new ComentarioBloqueado(
                                $comentario,
                                $request->motivo ?? 'Violación de las normas de la comunidad',
                                $request->detalles ?? 'Tu comentario ha sido bloqueado por incumplir las normas.'
                            ));

                        Log::info('Correo de bloqueo de comentario enviado', [
                            'comentario_id' => $comentario->id,
                            'email' => $comentario->user->email
                        ]);
                    } else {
                        Mail::to($comentario->user->email)
                            ->queue(new ComentarioDesbloqueado(
                                $comentario,
                                $request->motivo ?? 'Tu comentario ha sido restaurado.'
                            ));

                        Log::info('Correo de desbloqueo de comentario enviado', [
                            'comentario_id' => $comentario->id,
                            'email' => $comentario->user->email
                        ]);
                    }
                } else {
                    Log::warning('Usuario sin email para notificación de comentario', [
                        'comentario_id' => $comentario->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de comentario: ' . $e->getMessage(), [
                    'comentario_id' => $comentario->id,
                    'accion' => $accion
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Comentario ' . ($comentario->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en bloquearDesbloquearComentario: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al procesar: ' . $e->getMessage()
            ], 500);
        }
    }
    private function validarYObtenerModerador(Request $request)
    {
        $validatedData = $request->validate([
            'moderador_id' => 'required|exists:moderadores,id',
        ]);
    
        return Moderador::find($validatedData['moderador_id']);
    }

    private function actividadDeBloqueo($moderador, $comentario, $accion) {
        $accionTexto = $this->obtenerTextoAccionBloqueo($accion);

        ActividadModeracion::create([
            'moderador_id' => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => $accionTexto . ' comentario',
            'comentario_id' => $comentario->id,
            'detalles' => "El moderador {$moderador->name} {$accionTexto} el comentario '{$comentario->mensaje}' del usuario {$comentario->user->name}.",
        ]);
    }

    private function actividadDeReportes($moderador, $reporte, $accion) {

        ActividadModeracion::create([
            'moderador_id' => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => 'resolvió reporte',
            'reporte_id' => $reporte->id,
            'comentario_id' => $reporte->comentario_id,
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

    public function conteoComentarios()
    {
        return response()->json([
            'pendientes' => ReportaComentario::where('estado', ReportaComentario::ESTADO_PENDIENTE)->count(),
            'resueltos' => ReportaComentario::where('estado', ReportaComentario::ESTADO_RESUELTO)->count(),
        ]);
    }

}
