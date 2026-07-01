<?php

namespace App\Models\Blitzvideo;

use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    protected $connection = 'blitzvideo';

    protected $table = 'notificacion';
    
    protected $fillable = [
        'mensaje',
        'referencia_id',
        'referencia_tipo',
    ];

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'notifica', 'notificacion_id', 'usuario_id')
                    ->withPivot('leido')
                    ->withTimestamps();
    }
}
