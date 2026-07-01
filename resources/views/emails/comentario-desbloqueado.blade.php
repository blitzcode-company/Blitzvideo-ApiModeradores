@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">✅</div>
        <h1 style="color: #27ae60; margin: 0;">Comentario Restaurado</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu comentario ha sido restaurado en Blitzvideo</p>
    </div>

    <p>Hola,</p>
    
    <p>Te informamos que tu comentario en el video "<strong>{{ $video_titulo ?? 'Video sin título' }}</strong>" ha sido <span style="color: #27ae60; font-weight: bold;">restaurado</span> y ya está visible nuevamente en Blitzvideo.</p>

    <div class="details-box" style="background: #e8f8e8; border-left-color: #27ae60;">
        <p><strong>📹 Video:</strong> {{ $video_titulo ?? 'Video sin título' }}</p>
        <p><strong>💬 Comentario restaurado:</strong></p>
        <div style="background: white; padding: 15px; border-radius: 6px; margin-top: 5px; font-style: italic; border: 1px solid #ddd; color: #333;">
            "{{ $texto ?? 'Comentario sin texto' }}"
        </div>
        @if(!empty($motivo) && $motivo !== 'Tu comentario ha sido restaurado.')
            <p style="margin-top: 15px;"><strong>💬 Comentario del moderador:</strong> {{ $motivo }}</p>
        @endif
        <p><strong>📅 Fecha de restauración:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
        <p><strong>🆔 ID del comentario:</strong> #{{ $comentario_id ?? 'N/A' }}</p>
    </div>

    <div style="background: #e8f8e8; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #27ae60;">
        <p style="margin: 0; color: #27ae60;">
            <strong>💡 Recuerda:</strong> Mantén un lenguaje respetuoso en tus comentarios.
        </p>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        @if(!empty($video_id))
            <a href="{{ config('app.visualizer_host') }}video/{{ $video_id }}" class="btn" style="background: #27ae60;">Ver Video</a>
        @else
            <a href="{{ config('app.visualizer_host') }}" class="btn" style="background: #27ae60;">Ir a Blitzvideo</a>
        @endif
    </div>

    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection