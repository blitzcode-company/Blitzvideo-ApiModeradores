<?php

namespace App\Models\Blitzvideo;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stream extends Model
{
    protected $connection = 'blitzvideo';

    use HasFactory;

    protected $fillable = [
        'titulo',
        'descripcion',
        'miniatura',
        'activo',
        'canal_id',
    ];

    public function canal()
    {
        return $this->belongsTo(Canal::class);
    }
}
