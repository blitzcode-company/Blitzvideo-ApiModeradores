<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VideoDesbloqueado extends Mailable
{
  use Queueable, SerializesModels;

    public $video;
    public $motivo;

    public function __construct($video, $motivo = null)
    {
        $this->video = $video;
        $this->motivo = $motivo;
    }

    public function build()
    {
        return $this->subject('✅ Tu video ha sido restaurado en Blitzvideo')
                    ->view('emails.video-desbloqueado')
                    ->with([
                        'titulo' => $this->video->titulo,
                        'motivo' => $this->motivo,
                        'fecha' => now()->format('d/m/Y H:i')
                    ]);
    }
}
