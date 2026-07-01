<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UsuarioReactivado extends Mailable
{
     use Queueable, SerializesModels;

    public $usuario;
    public $motivo;
    public $fecha_reactivacion;

    public function __construct($usuario, $motivo = null)
    {
        $this->usuario = $usuario;
        $this->motivo = $motivo;
        $this->fecha_reactivacion = now()->format('d/m/Y H:i');
    }

    public function build()
    {
        return $this->subject('✅ Tu cuenta ha sido reactivada en Blitzvideo')
                    ->view('emails.usuario-reactivado')
                    ->with([
                        'nombre' => $this->usuario->name,
                        'motivo' => $this->motivo,
                        'fecha' => $this->fecha_reactivacion,
                        'email' => $this->usuario->email
                    ]);
    }
}
