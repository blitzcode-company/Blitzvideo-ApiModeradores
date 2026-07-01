<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteRecibido extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;

    public function __construct($reporte)
    {
        $this->reporte = $reporte;
    }

    public function build()
    {
        return $this->subject('📩 Reporte recibido en Blitzvideo')
                    ->view('emails.reporte-recibido')
                    ->with([
                        'reporte_id' => $this->reporte->id,
                        'tipo' => $this->reporte->tipo,
                        'fecha' => now()->format('d/m/Y H:i')
                    ]);
    }
}
