<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsuarioSuspendido extends Mailable
{
    use Queueable, SerializesModels;

    public $usuario;
    public $motivo;
    public $detalles;


    public function __construct($usuario, $motivo, $detalles)
    {
        $this->usuario = $usuario;
        $this->motivo = $motivo;
        $this->detalles = $detalles;

    }

    public function build()
    {
        return $this->subject('👤 Tu cuenta ha sido suspendida en Blitzvideo')
                    ->view('emails.usuario-suspendido')
                    ->with([
                        'nombre' => $this->usuario->name,
                        'motivo' => $this->motivo,
                        'detalles' => $this->detalles,
                        'fecha' => now()->format('d/m/Y H:i')
                    ]);
    }
}
