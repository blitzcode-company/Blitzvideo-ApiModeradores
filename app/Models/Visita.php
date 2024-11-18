<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visita extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
    ];

    public function user()
    {
        return $this->belongsTo(UsuarioSitio::class);
    }

    public function video()
    {
        return $this->belongsTo(UsuarioSitio::class);
    }
}
