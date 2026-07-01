<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EstadisticasController extends Controller
{

    public function obtenerEstadisticasGenerales(Request $request)
    {
        try {
            $pendientesComentarios = DB::connection('blitzvideo')
                ->table('reporta_comentario')
                ->where('estado', 'pendiente')
                ->count();
            
            $pendientesVideos = DB::connection('blitzvideo')
                ->table('reporta')
                ->where('estado', 'pendiente')
                ->count();
            
            $pendientesUsuarios = DB::connection('blitzvideo')
                ->table('reporta_usuario')
                ->where('estado', 'pendiente')
                ->count();

            $resueltosComentarios = DB::connection('blitzvideo')
                ->table('reporta_comentario')
                ->where('estado', 'resuelto')
                ->count();
            
            $resueltosVideos = DB::connection('blitzvideo')
                ->table('reporta')
                ->where('estado', 'resuelto')
                ->count();
            
            $resueltosUsuarios = DB::connection('blitzvideo')
                ->table('reporta_usuario')
                ->where('estado', 'resuelto')
                ->count();

            $pendientesTotal = $pendientesComentarios + $pendientesVideos + $pendientesUsuarios;
            $resueltosTotal = $resueltosComentarios + $resueltosVideos + $resueltosUsuarios;
            $totalReportes = $pendientesTotal + $resueltosTotal;

            $tasaResolucion = $totalReportes > 0 ? round(($resueltosTotal / $totalReportes) * 100) : 0;

            $tiempoPromedio = DB::connection('blitzvideo')
                ->table('reporta_comentario')
                ->whereNotNull('revisado_en')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, revisado_en)) as promedio'))
                ->value('promedio') ?? 0;

            $usuariosBloqueados = DB::connection('blitzvideo')
                ->table('users')
                ->where('bloqueado', 1)
                ->count();

            return response()->json([
                'pendientes_total' => $pendientesTotal,
                'resueltos_total' => $resueltosTotal,
                'tasa_resolucion' => $tasaResolucion,
                'usuarios_bloqueados' => $usuariosBloqueados,
                'tiempo_promedio_horas' => ceil($tiempoPromedio),
                'detalles' => [
                    'comentarios' => [
                        'pendientes' => $pendientesComentarios,
                        'resueltos' => $resueltosComentarios
                    ],
                    'videos' => [
                        'pendientes' => $pendientesVideos,
                        'resueltos' => $resueltosVideos
                    ],
                    'usuarios' => [
                        'pendientes' => $pendientesUsuarios,
                        'resueltos' => $resueltosUsuarios
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener estadísticas'], 500);
        }
    }


 public function obtenerEstadisticasPorPeriodo($periodo = 'semana')
{
    try {
        $dias = match ($periodo) {
            'semana' => 7,
            'mes' => 30,
            'año' => 365,
            default => 7
        };

        $fechaInicio = now()->subDays($dias);

        $creados =
            DB::connection('blitzvideo')->table('reporta')->where('created_at', '>=', $fechaInicio)->count() +
            DB::connection('blitzvideo')->table('reporta_comentario')->where('created_at', '>=', $fechaInicio)->count() +
            DB::connection('blitzvideo')->table('reporta_usuario')->where('created_at', '>=', $fechaInicio)->count();

        $resueltos =
            DB::connection('blitzvideo')->table('reporta')->whereNotNull('revisado_en')->where('revisado_en', '>=', $fechaInicio)->count() +
            DB::connection('blitzvideo')->table('reporta_comentario')->whereNotNull('revisado_en')->where('revisado_en', '>=', $fechaInicio)->count() +
            DB::connection('blitzvideo')->table('reporta_usuario')->whereNotNull('revisado_en')->where('revisado_en', '>=', $fechaInicio)->count();

        $tasaResolucion = $creados > 0
            ? round(($resueltos / $creados) * 100)
            : 0;

        $promedioDiario = round($creados / $dias, 1);

        return response()->json([
            'periodo' => $periodo,
            'dias' => $dias,
            'creados' => $creados,
            'resueltos' => $resueltos,
            'tasa_resolucion' => $tasaResolucion,
            'promedio_diario' => $promedioDiario
        ]);

    } catch (\Exception $e) {
        \Log::error($e->getMessage());

        return response()->json([
            'message' => 'Error al obtener estadísticas por período'
        ], 500);
    }
}

    public function obtenerDistribucionReportes()
    {
        try {
            $distribucion = [
                'comentarios' => DB::connection('blitzvideo')
                    ->table('reporta_comentario')
                    ->count(),
                'videos' => DB::connection('blitzvideo')
                    ->table('reporta')
                    ->count(),
                'usuarios' => DB::connection('blitzvideo')
                    ->table('reporta_usuario')
                    ->count()
            ];

            return response()->json([
                'distribucion' => $distribucion,
                'total' => array_sum($distribucion)
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener distribución'], 500);
        }
    }



   public function obtenerUsuariosMasReportados(Request $request)
{
    try {
        $limite = $request->input('limite', 10);

        $usuariosMasReportados = DB::connection('blitzvideo')
            ->table('reporta_usuario')
            ->join('users', 'reporta_usuario.id_reportado', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
                'users.foto',
                DB::raw('COUNT(reporta_usuario.id) as total_reportes')
            )
            ->groupBy('users.id', 'users.name', 'users.foto')
            ->orderByDesc('total_reportes')
            ->limit($limite)
            ->get();

        return response()->json([
            'usuarios' => $usuariosMasReportados
        ], 200);

    } catch (\Exception $e) {
        \Log::error('Error al obtener usuarios más reportados: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error al obtener usuarios más reportados'
        ], 500);
    }
}
    public function obtenerContenidoMasReportado(Request $request)
    {
        try {
            $limite = $request->input('limite', 10);

            $videosMasReportados = DB::connection('blitzvideo')
                ->table('reporta')
                ->selectRaw('video_id, COUNT(*) as total_reportes')
                ->groupBy('video_id')
                ->orderByDesc('total_reportes')
                ->limit($limite)
                ->get();

            return response()->json([
                'videos' => $videosMasReportados
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener contenido más reportado'], 500);
        }
    }


    public function obtenerTiempoResolucion()
    {
        try {
            $tiempos = DB::connection('blitzvideo')
                ->table('reporta_comentario')
                ->whereNotNull('revisado_en')
                ->selectRaw('
                    CASE 
                        WHEN TIMESTAMPDIFF(HOUR, created_at, revisado_en) < 1 THEN "< 1 hora"
                        WHEN TIMESTAMPDIFF(HOUR, created_at, revisado_en) < 24 THEN "< 1 día"
                        WHEN TIMESTAMPDIFF(DAY, created_at, revisado_en) < 7 THEN "< 1 semana"
                        ELSE "> 1 semana"
                    END as rango,
                    COUNT(*) as cantidad
                ')
                ->groupBy('rango')
                ->get();

            return response()->json([
                'tiempos' => $tiempos
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al obtener tiempo de resolución'], 500);
        }
    }



}
