<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UsuarioSitio;
use App\Models\ReportaUsuario;

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


    public function resolverReporteUsuario($reporteId)
    {
        $reporte = ReportaUsuario::find($reporteId);
    
        if ($reporte) {
            $reporte->estado = 'resuelto';  
            $reporte->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Reporte de usuario resuelto exitosamente.'
            ]);
        }
    
        return response()->json([
            'status' => 'error',
            'message' => 'Reporte no encontrado.'
        ], 404);
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


    public function bloquearDesbloquearUsuario($userId, $accion)
    {
        $user = UsuarioSitio::find($userId);

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

        $user->bloqueado = ($accion === 'bloquear');
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario ' . ($user->bloqueado ? 'bloqueado' : 'desbloqueado') . ' correctamente.'
        ]);
    }

    public function conteoUsuarios()
    {
        return response()->json([
            'pendientes' => ReportaUsuario::where('estado', ReportaUsuario::ESTADO_PENDIENTE)->count(),
            'resueltos' => ReportaUsuario::where('estado', ReportaUsuario::ESTADO_RESUELTO)->count(),
        ]);
    }
}
