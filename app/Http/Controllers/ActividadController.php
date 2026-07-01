<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActividadModeracion;


class ActividadController extends Controller
{
    public function ListarActividad()
    {
        $actividad = ActividadModeracion::get();

        return response()->json([
            'actividad' => $actividad
        ], 200);
    }

}
