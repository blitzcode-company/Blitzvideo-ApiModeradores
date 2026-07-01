<?php

namespace App\Models\Blitzvideo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $connection = 'blitzvideo';
    protected $table = 'users';

    
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'fecha_de_nacimiento',
        'bloqueado',
        'premium',
        'foto',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function canales()
    {
        return $this->hasOne(Canal::class);
    }

    public function visitas()
    {
        return $this->hasMany(Visita::class);
    }

    public function playlists()
    {
        return $this->hasMany(Playlist::class);
    }

    public function reportaComentarios()
    {
        return $this->hasMany(ReportaComentario::class);
    }

    public function canalesSuscritos()
    {
        return $this->belongsToMany(Canal::class, 'suscribe')->withTimestamps()->withTrashed();
    }

    public function notificaciones()
    {
        return $this->belongsToMany(Notificacion::class, 'notifica', 'usuario_id', 'notificacion_id')
            ->withPivot('leido')
            ->withTimestamps();
    }
}
