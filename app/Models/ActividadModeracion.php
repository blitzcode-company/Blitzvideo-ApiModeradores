<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadModeracion extends Model
{
    use HasFactory;

    protected $fillable = [
        'moderador_id',
        'moderador_nombre',
        'accion',
        'reporte_id',
        'comentario_id',
        'user_id',
        'video_id',
        'detalles',
    ];
}
