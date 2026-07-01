<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VideoBloqueado extends Mailable
{
    use Queueable, SerializesModels;

    public $video;
    public $motivo;
    public $detalles;
    public $fecha;

    public function __construct($video, $motivo, $detalles)
    {
        $this->video = $video;
        $this->motivo = $motivo;
        $this->detalles = $detalles;
        $this->fecha = now()->format('d/m/Y H:i');
    }

    public function build()
    {
        return $this->subject('🚫 Tu video ha sido bloqueado en Blitzvideo')
                    ->view('emails.video-bloqueado')
                    ->with([
                        'titulo' => $this->video->titulo,
                        'motivo' => $this->motivo,
                        'detalles' => $this->detalles,
                        'fecha' => $this->fecha,
                        'video_id' => $this->video->id
                    ]);
    }
}
