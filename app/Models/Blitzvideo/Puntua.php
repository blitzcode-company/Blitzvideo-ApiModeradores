<?php

namespace App\Models\Blitzvideo;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Puntua extends Model
{
    protected $connection = 'blitzvideo';

    use SoftDeletes;

    protected $table = 'puntua';

    protected $fillable = [
        'user_id',
        'video_id',
        'valora',
    ];

    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
