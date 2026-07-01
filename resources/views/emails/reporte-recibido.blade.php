@extends('emails.layouts.base')

@section('content')
    <div style="text-align: center; margin-bottom: 30px;">
        <div style="font-size: 60px; margin-bottom: 10px;">📩</div>
        <h1 style="color: #3498db; margin: 0;">Reporte Recibido</h1>
        <p style="color: #7f8c8d; font-size: 18px; margin-top: 5px;">Tu reporte ha sido recibido correctamente</p>
    </div>

    <p>Hola,</p>
    
    <p>Confirmamos que tu reporte en <strong>Blitzvideo</strong> ha sido <span style="color: #27ae60; font-weight: bold;">recibido</span> correctamente.</p>

    <div class="details-box" style="background: #d1ecf1; border-left-color: #0c5460;">
        <p><strong>🔢 ID del reporte:</strong> {{ $reporte_id ?? 'N/A' }}</p>
        <p><strong>📂 Tipo:</strong> {{ $tipo ?? 'General' }}</p>
        <p><strong>📅 Fecha de recepción:</strong> {{ $fecha ?? now()->format('d/m/Y H:i') }}</p>
    </div>

    <div style="background: #d1ecf1; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #0c5460;">
        <p style="margin: 0; color: #0c5460;">
            <strong>🙏 Gracias</strong> por tu reporte. Nuestro equipo lo revisará pronto.
        </p>
    </div>

    <p>
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
@endsection