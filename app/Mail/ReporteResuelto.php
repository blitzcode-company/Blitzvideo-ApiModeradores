<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteResuelto extends Mailable
{
    use Queueable, SerializesModels;

    public $reporte;
    public $resolucion;
    public $comentarios;

    public function __construct($reporte, $resolucion, $comentarios = null)
    {
        $this->reporte = $reporte;
        $this->resolucion = $resolucion;
        $this->comentarios = $comentarios;
    }

    public function build()
    {
        return $this->subject('✅ Tu reporte ha sido resuelto en Blitzvideo')
                    ->view('emails.reporte-resuelto')
                    ->with([
                        'reporte_id' => $this->reporte->id,
                        'tipo' => $this->reporte->tipo,
                        'resolucion' => $this->resolucion,
                        'comentarios' => $this->comentarios,
                        'fecha' => now()->format('d/m/Y H:i')
                    ]);
    }
}
