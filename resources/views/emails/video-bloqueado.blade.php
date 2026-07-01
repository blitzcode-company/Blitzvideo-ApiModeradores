@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">🚫</div>
        <h1 style="color: #e74c3c; margin: 0;">Video Bloqueado</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu video ha sido bloqueado en Blitzvideo</p>
    </div>

    <p>Hola,</p>
    
    <p>Te informamos que tu video "<strong>{{ $titulo ?? 'Video sin título' }}</strong>" ha sido <span style="color: #e74c3c; font-weight: bold;">bloqueado</span> en Blitzvideo.</p>

    <div class="details-box" style="background: #fde8e8; border-left-color: #e74c3c;">
        <p><strong>📹 Video:</strong> {{ $titulo ?? 'Video sin título' }}</p>
        <p><strong>📝 Motivo:</strong> <span style="color: #e74c3c;">{{ $motivo ?? 'Violación de las normas de la comunidad' }}</span></p>
        <p><strong>📋 Detalles adicionales:</strong> {{ $detalles ?? 'Ninguno' }}</p>
        <p><strong>📅 Fecha del bloqueo:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
    </div>


    <p style="margin-top: 20px;">
        Si consideras que esto es un error, por favor contáctanos.
    </p>
    
    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection