@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">💬</div>
        <h1 style="color: #e74c3c; margin: 0;">Comentario Bloqueado</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu comentario ha sido bloqueado en Blitzvideo</p>
    </div>

    <p>Hola,</p>
    
    <p>Te informamos que tu comentario en el video "<strong>{{ $video_titulo ?? 'Video sin título' }}</strong>" ha sido <span style="color: #e74c3c; font-weight: bold;">bloqueado</span> en Blitzvideo.</p>

    <div class="details-box" style="background: #fde8e8; border-left-color: #e74c3c;">
        <p><strong>📹 Video:</strong> {{ $video_titulo ?? 'Video sin título' }}</p>
        <p><strong>💬 Comentario bloqueado:</strong></p>
        <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 5px; font-style: italic; border: 1px solid #ddd; color: #333;">
            "{{ $texto ?? 'Comentario sin texto' }}"
        </div>
        <p style="margin-top: 15px;"><strong>📝 Motivo:</strong> <span style="color: #e74c3c; font-weight: bold;">{{ $motivo ?? 'Violación de las normas de la comunidad' }}</span></p>
        @if(!empty($detalles))
            <p><strong>📋 Detalles adicionales:</strong> {{ $detalles }}</p>
        @endif
        <p><strong>📅 Fecha del bloqueo:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
        <p><strong>🆔 ID del comentario:</strong> #{{ $comentario_id ?? 'N/A' }}</p>
    </div>

    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f39c12;">
        <p style="margin: 0; color: #856404;">
            <strong>💡 Importante:</strong> Revisa las normas de la comunidad para futuras participaciones.
        </p>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        @if(!empty($video_id))
            <a href="{{ config('app.visualizer_host') }}video/{{ $video_id }}" class="btn">Ver Video</a>
        @else
            <a href="{{ config('app.visualizer_host') }}" class="btn">Ir a Blitzvideo</a>
        @endif
    </div>

    <p style="margin-top: 20px;">
        Si consideras que esto es un error, por favor contáctanos.
    </p>
    
    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection