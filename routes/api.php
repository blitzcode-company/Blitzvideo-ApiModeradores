<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportaComentarioController;
use App\Http\Controllers\ReportaController;


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

Route::prefix('v1')->middleware('auth.api')->group(function () {
    Route::prefix('reporte')->group(function () {
        Route::post('/', [ReportaController::class, 'CrearReporte']);
        Route::get('/', [ReportaController::class, 'ListarReportes']);
        Route::put('/{reporteId}/validar', [ReportaController::class, 'ValidarReporte']);
        Route::get('/video/{videoId}', [ReportaController::class, 'ListarReportesDeVideo']);
        Route::get('/usuario/{userId}', [ReportaController::class, 'ListarReportesDeUsuario']);
        Route::put('/{reporteId}', [ReportaController::class, 'ModificarReporte']);
        Route::delete('/{reporteId}', [ReportaController::class, 'BorrarReporte']);
        Route::delete('/video/{videoId}', [ReportaController::class, 'BorrarReportesDeVideo']);

        Route::post('/comentario', [ReportaComentarioController::class, 'CrearReporte']);
        Route::get('/comentario', [ReportaComentarioController::class, 'ListarReportes']);
        Route::get('/comentario/{comentarioId}', [ReportaComentarioController::class, 'ListarReportesDeComentario']);
        Route::get('/comentario/usuario/{userId}', [ReportaComentarioController::class, 'ListarReportesDeUsuario']);
        Route::put('/{reporteId}/comentario', [ReportaComentarioController::class, 'ModificarReporte']);
        Route::delete('/{reporteId}/comentario', [ReportaComentarioController::class, 'BorrarReporte']);
        Route::delete('/comentario/{comentarioId}', [ReportaComentarioController::class, 'BorrarReportesDeComentario']);
    });
});
