<?php

namespace App\Models\Blitzvideo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etiqueta extends Model
{
    use HasFactory;
    protected $connection = 'blitzvideo';

    protected $fillable = [
        'nombre',
    ];

    public function videos()
    {
        return $this->belongsToMany(Video::class);
    }
}