<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blitzvideo\User;
use App\Models\Blitzvideo\ReportaUsuario;
use App\Models\ActividadModeracion;
use App\Mail\ReporteResuelto;
use App\Mail\UsuarioSuspendido;
use App\Mail\UsuarioReactivado;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User as Moderador;


class ReportaUsuarioController extends Controller
{

    public function ListarReportes()
    {
    $reportes = ReportaUsuario::with(['reportante', 'reportado'])->get();

    return response()->json([
        'reportes' => $reportes
    ]);
    }

    public function BorrarReporte($reporteId)
    {
    $reporte = ReportaUsuario::find($reporteId);

    if ($reporte) {
        $reporte->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Reporte eliminado correctamente.'
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
            'ciberacoso' => 'boolean',
            'privacidad' => 'boolean',
            'suplantacion_identidad' => 'boolean',
            'incitacion_odio' => 'boolean',
            'amenazas' => 'boolean',
            'otros' => 'boolean',
        ]);

        $reporte = ReportaUsuario::findOrFail($reporteId);
        $reporte->update($validatedData);

        return response()->json([
            'message' => 'Reporte modificado exitosamente.',
            'reportes' => $reporte
        ], 200);
    }


    public function resolverReporteUsuario(Request $request, $reporteId)
    {
        try {
            $reporte = ReportaUsuario::with(['reportante', 'reportado'])->find($reporteId);

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

            $this->actividadDeReportes($moderador, $reporte, 'resolvió reporte');

            try {
                if ($reporte->reportante && $reporte->reportante->email) {
                    Mail::to($reporte->reportante->email)
                        ->queue(new ReporteResuelto(
                            (object) [
                                'id' => $reporte->id,
                                'tipo' => 'Usuario',
                                'estado' => 'resuelto'
                            ],
                            $request->resolucion ?? 'Aceptado',
                            $request->comentarios ?? 'El reporte ha sido revisado y resuelto.'
                        ));
                    
                    Log::info('Correo de reporte de usuario resuelto enviado', [
                        'reporte_id' => $reporte->id,
                        'email' => $reporte->reportante->email
                    ]);
                }

                if ($request->resolucion === 'Aceptado' && $request->bloquear_usuario === true) {
                    $usuarioReportado = $reporte->reportado;
                    
                    if ($usuarioReportado && $usuarioReportado->email) {
                        Mail::to($usuarioReportado->email)
                            ->queue(new UsuarioSuspendido(
                                $usuarioReportado,
                                $request->motivo ?? 'Reportes de la comunidad',
                                $request->detalles ?? 'Tu cuenta ha sido suspendida por múltiples reportes.',
                                $request->duracion ?? '7 días',
                                $request->fecha_fin ? now()->addDays((int)$request->duracion) : null
                            ));
                        
                        Log::info('Correo de suspensión de usuario enviado', [
                            'usuario_id' => $usuarioReportado->id,
                            'email' => $usuarioReportado->email
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de reporte de usuario: ' . $e->getMessage(), [
                    'reporte_id' => $reporte->id
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Reporte de usuario resuelto exitosamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en resolverReporteUsuario: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error al resolver el reporte: ' . $e->getMessage()
            ], 500);
        }
    }


    public function listarReportesResueltos()
    {
        $reportesResueltos = ReportaUsuario::where('estado', ReportaUsuario::ESTADO_RESUELTO)
                                        ->with(['reportante', 'reportado'])->get();

        return response()->json([
            'status' => 'success',
            'reportes' => $reportesResueltos
        ]);
    }

    public function listarReportesNoResueltos()
    {
        $reportesNoResueltos = ReportaUsuario::where('estado', ReportaUsuario::ESTADO_PENDIENTE)
                                            ->with(['reportante', 'reportado'])->get();

        return response()->json([
            'status' => 'success',
            'reportes' => $reportesNoResueltos
        ]);
    }

    public function obtenerDetalleReporteUsuario($reporteId)
    {
        $reporte = ReportaUsuario::with(['reportante', 'reportado'])->find($reporteId);

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


    public function bloquearDesbloquearUsuario(Request $request, $userId, $accion)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado.'
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

            $user->bloqueado = ($accion === 'bloquear');
            $user->save();

            $this->actividadDeBloqueo($moderador, $user, $accion);

            try {
                if ($user->email) {
                    if ($accion === 'bloquear') {

                        Mail::to($user->email)
                            ->queue(new UsuarioSuspendido(
                                $user,
                                $request->motivo ?? 'Violación de las normas de la comunidad',
                                $request->detalles ?? 'Tu cuenta ha sido suspendida por incumplir las normas.'
                            ));

                        Log::info('Correo de suspensión de usuario enviado', [
                            'usuario_id' => $user->id,
                            'email' => $user->email,
                        ]);
                    } else {
                        Mail::to($user->email)
                            ->queue(new UsuarioReactivado(
                                $user,
                                $request->motivo ?? 'Tu cuenta ha sido restaurada.'
                            ));

                        Log::info('Correo de reactivación de usuario enviado', [
                            'usuario_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } else {
                    Log::warning('Usuario sin email para enviar notificación', [
                        'usuario_id' => $user->id
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al enviar correo de usuario: ' . $e->getMessage(), [
                    'usuario_id' => $user->id,
                    'accion' => $accion
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario ' . ($user->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en bloquearDesbloquearUsuario: ' . $e->getMessage());
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

    private function actividadDeBloqueo($moderador, $user, $accion)
    {
        $accionTexto = $this->obtenerTextoAccionBloqueo($accion);

        ActividadModeracion::create([
            'moderador_id' => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => $accionTexto . ' usuario',
            'user_id' => $user->id,
            'detalles' => "El moderador {$moderador->name} {$accionTexto} el usuario {$user->name}",
        ]);
    }

    private function actividadDeReportes($moderador, $reporte, $accion)
    {
        ActividadModeracion::create([
            'moderador_id'     => $moderador->id,
            'moderador_nombre' => $moderador->name,
            'accion' => 'resolvió reporte',
            'reporte_id'       => $reporte->id,
            'user_id'          => $reporte->id_reportado,
            'detalles'         => "El moderador {$moderador->name} {$accion} {$reporte->id}.",
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

    public function conteoUsuarios()
    {
        return response()->json([
            'pendientes' => ReportaUsuario::where('estado', ReportaUsuario::ESTADO_PENDIENTE)->count(),
            'resueltos' => ReportaUsuario::where('estado', ReportaUsuario::ESTADO_RESUELTO)->count(),
        ]);
    }
}
