@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">✅</div>
        <h1 style="color: #27ae60; margin: 0;">Reporte Resuelto</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu reporte ha sido resuelto</p>
    </div>

    <p>Hola,</p>
    
    <p>Te informamos que el reporte que realizaste en <strong>Blitzvideo</strong> ha sido <span style="color: #27ae60; font-weight: bold;">resuelto</span>.</p>

    <div class="details-box" style="background: #e8f8e8; border-left-color: #27ae60;">
        <p><strong>🔢 ID del reporte:</strong> {{ $reporte_id ?? 'N/A' }}</p>
        <p><strong>📂 Tipo:</strong> {{ $tipo ?? 'General' }}</p>
        <p><strong>📊 Resolución:</strong> <span style="color: #27ae60; font-weight: bold;">{{ $resolucion ?? 'Aceptado' }}</span></p>
        @if($comentarios)
            <p><strong>💬 Comentarios del moderador:</strong> {{ $comentarios }}</p>
        @endif
        <p><strong>📅 Fecha de resolución:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    <div style="background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0c5460;">
        <p style="margin: 0; color: #0c5460;">
            <strong>🙏 Gracias</strong> por ayudarnos a mantener la comunidad segura.
        </p>
    </div>


    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection