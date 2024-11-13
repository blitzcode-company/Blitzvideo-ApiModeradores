<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporta;
use App\Models\Video;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class ReportaController extends Controller
{
    public function bloquearDesbloquearVideo($videoId, $accion)
    {
        $video = Video::find($videoId);

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

        $video->bloqueado = ($accion === 'bloquear');
        $video->save();

        return response()->json([
            'status' => 'success',
            'message' => 'El video '. $video->id . ' fue ' . ($video->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
        ]);
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

    public function resolverReporteVideo($reporteId)
{
    $reporte = Reporta::find($reporteId);

    if ($reporte) {
        $reporte->estado = 'resuelto';  
        $reporte->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Reporte de video resuelto exitosamente.'
        ]);
    }

    return response()->json([
        'status' => 'error',
        'message' => 'Reporte no encontrado.'
    ], 404);
}

    public function ListarReportes()
    {
        $reportes = Reporta::with(['user', 'video'])->get();

        return response()->json([
            'reportes' => $reportes
        ], 200);
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
                                    ->with(['user', 'video'])->get();

        return response()->json([
            'reportes' => $reportesResueltos
        ]);
    }

    public function listarReportesNoResueltosVideos()
    {
        $reportesNoResueltos =Reporta::where('estado', Reporta::ESTADO_PENDIENTE)
                                       ->with(['user', 'video'])->get();

        return response()->json([
            'reportes' => $reportesNoResueltos
        ]);
    }


    public function conteoVideos()
    {
        return response()->json([
            'pendientes' => Reporta::where('estado', Reporta::ESTADO_PENDIENTE)->count(),
            'resueltos' => Reporta::where('estado', Reporta::ESTADO_RESUELTO)->count(),
        ]);
    }

}
