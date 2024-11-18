<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportaComentario;
use App\Models\Comentario;
use App\Models\UsuarioSitio;
use Illuminate\Support\Facades\Http;


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
                                              ->with(['user', 'comentario', 'comentario.video']) 
                                              ->get();



        return response()->json([
            'reportes' => $reportesResueltos
        ]);
    }

    public function listarReportesNoResueltosComentarios()
    {
        $reportesNoResueltos = ReportaComentario::where('estado', ReportaComentario::ESTADO_PENDIENTE)
                                                ->with(['user', 'comentario']) 
                                                ->get();

        return response()->json([
            'reportes' => $reportesNoResueltos
        ]);
    }

    public function obtenerDetalleReporteComentario($reporteId)
    {
        $reporte = ReportaComentario::with('user', 'comentario', 'comentario.video')->find($reporteId);
    
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
    
    public function resolverReporteComentario($reporteId)
    {
        $reporte = ReportaComentario::find($reporteId);
    
        if ($reporte) {
            $reporte->estado = 'resuelto';  
            $reporte->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Reporte de comentario resuelto exitosamente.'
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Reporte no encontrado.'
        ], 404);
    }
    
    public function bloquearDesbloquearComentario($comentarioId, $accion)
    {
        $comentario = Comentario::find($comentarioId);

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

        $comentario->bloqueado = ($accion === 'bloquear');
        $comentario->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Comentario ' . ($comentario->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
        ]);
    }
    public function conteoComentarios()
    {
        return response()->json([
            'pendientes' => ReportaComentario::where('estado', ReportaComentario::ESTADO_PENDIENTE)->count(),
            'resueltos' => ReportaComentario::where('estado', ReportaComentario::ESTADO_RESUELTO)->count(),
        ]);
    }

}
