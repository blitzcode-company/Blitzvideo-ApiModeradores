<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportaComentarioController;
use App\Http\Controllers\ReportaController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ReportaUsuarioController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function () {

    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/user', [LoginController::class, 'obtenerDatosUser']);


    Route::prefix('video')->group(function () {
        Route::get('/reporte', [ReportaController::class, 'ListarReportes']);
        Route::get('/reporte/resueltos', [ReportaController::class, 'listarReportesResueltosVideos']);
        Route::get('/reporte/no-resueltos', [ReportaController::class, 'listarReportesNoResueltosVideos']);
        Route::get('/reporte/{reporteId}', [ReportaController::class, 'obtenerDetalleReporteVideo']);
        
        Route::put('/reporte/{reporteId}/resolver', [ReportaController::class, 'resolverReporteVideo']);
        Route::put('/reporte/{reporteId}', [ReportaController::class, 'ModificarReporte']);
        Route::delete('/reporte/{reporteId}', [ReportaController::class, 'BorrarReporte']);


        Route::put('/{videoId}/{accion}', [ReportaController::class, 'bloquearDesbloquearVideo']);
        Route::get('/conteo', [ReportaController::class, 'conteoVideos']);

    });

    Route::prefix('comentario')->group(function () {
        Route::get('/reporte', [ReportaComentarioController::class, 'ListarReportes']);
        Route::get('/reporte/resueltos', [ReportaComentarioController::class, 'listarReportesResueltosComentarios']);
        Route::get('/reporte/no-resueltos', [ReportaComentarioController::class, 'listarReportesNoResueltosComentarios']);
        Route::get('/reporte/{reporteId}', [ReportaComentarioController::class, 'obtenerDetalleReporteComentario']);

        Route::put('/reporte/{reporteId}/resolver', [ReportaComentarioController::class, 'resolverReporteComentario']);
        Route::put('/reporte/{reporteId}', [ReportaComentarioController::class, 'ModificarReporte']);
        Route::delete('/reporte/{reporteId}', [ReportaComentarioController::class, 'BorrarReporte']);

 
        Route::put('/{comentarioId}/{accion}', [ReportaComentarioController::class, 'bloquearDesbloquearComentario']);
        Route::get('/conteo', [ReportaComentarioController::class, 'conteoComentarios']);

    });

    Route::prefix('usuario')->group(function () {
        Route::get('/reporte', [ReportaUsuarioController::class, 'ListarReportes']);
        Route::get('/reporte/resueltos', [ReportaUsuarioController::class, 'listarReportesResueltos']);
        Route::get('/reporte/no-resueltos', [ReportaUsuarioController::class, 'listarReportesNoResueltos']);
        Route::get('/reporte/{reporteId}', [ReportaUsuarioController::class, 'obtenerDetalleReporteUsuario']);

        Route::put('/reporte/{reporteId}/resolver', [ReportaUsuarioController::class, 'resolverReporteUsuario']);
        Route::put('/reporte/{reporteId}', [ReportaUsuarioController::class, 'ModificarReporte']);
        Route::delete('/reporte/{reporteId}', [ReportaUsuarioController::class, 'BorrarReporte']);

        
        Route::put('/{userId}/{accion}', [ReportaUsuarioController::class, 'bloquearDesbloquearUsuario']);
        Route::get('/conteo', [ReportaUsuarioController::class, 'conteoUsuarios']);

    });
});