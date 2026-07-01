<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ComentarioBloqueado extends Mailable
{
    use Queueable, SerializesModels;

    public $comentario;
    public $motivo;
    public $detalles;
    public $fecha;
    public $video_titulo;
    public $texto;
    public $video_id;
    public $comentario_id;

    public function __construct($comentario, $motivo, $detalles)
    {
        $this->comentario = $comentario;
        $this->motivo = $motivo;
        $this->detalles = $detalles;
        $this->fecha = now()->format('d/m/Y H:i');
        $this->comentario_id = $comentario->id;
        $this->video_id = $comentario->video_id ?? null;
        
        $this->texto = $comentario->mensaje ?? $comentario->texto ?? $comentario->contenido ?? 'Comentario sin texto';
        
        if ($comentario->video) {
            $this->video_titulo = $comentario->video->titulo ?? 'Video sin título';
        } else {
            $this->video_titulo = 'Video no disponible';
        }

        Log::info('ComentarioBloqueado construido', [
            'comentario_id' => $this->comentario_id,
            'texto' => $this->texto,
            'video_titulo' => $this->video_titulo,
            'video_id' => $this->video_id
        ]);
    }

    public function build()
    {
        return $this->subject('💬 Tu comentario ha sido bloqueado en Blitzvideo')
                    ->view('emails.comentario-bloqueado')
                    ->with([
                        'texto' => $this->texto,
                        'motivo' => $this->motivo,
                        'detalles' => $this->detalles,
                        'fecha' => $this->fecha,
                        'video_titulo' => $this->video_titulo,
                        'comentario_id' => $this->comentario_id,
                        'video_id' => $this->video_id
                    ]);
    }
}