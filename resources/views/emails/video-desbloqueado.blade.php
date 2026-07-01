@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">✅</div>
        <h1 style="color: #27ae60; margin: 0;">Video Restaurado</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu video ha sido restaurado en Blitzvideo</p>
    </div>

    <p>Hola,</p>
    
    <p>Te informamos que tu video "<strong>{{ $titulo ?? 'Video sin título' }}</strong>" ha sido <span style="color: #27ae60; font-weight: bold;">restaurado</span> y ya está visible nuevamente en Blitzvideo.</p>

    <div class="details-box" style="background: #e8f8e8; border-left-color: #27ae60;">
        <p><strong>📹 Video:</strong> {{ $titulo ?? 'Video sin título' }}</p>

        <p><strong>📅 Fecha de restauración:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    <div style="background: #e8f8e8; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #27ae60;">
        <p style="margin: 0; color: #27ae60;">
            <strong>💡 Recuerda:</strong> Sigue las normas de la comunidad para evitar futuros bloqueos.
        </p>
    </div>

    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection