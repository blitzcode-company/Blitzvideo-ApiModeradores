<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f5f7fa; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 2px solid #ecf0f1; }
        .logo { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .content { padding: 20px 0; }
        .video-details { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .motivo { color: #e74c3c; font-weight: bold; }
        .footer { text-align: center; padding-top: 20px; border-top: 2px solid #ecf0f1; color: #95a5a6; font-size: 12px; }
        .btn { display: inline-block; padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="container">
    <h2>✅ Cuenta Reactivada</h2>
    
    <p>Hola <strong>{{ $nombre }}</strong>,</p>
    
    <p>Nos complace informarte que tu cuenta en <strong>Blitzvideo</strong> ha sido <span style="color: #27ae60; font-weight: bold;">reactivada</span>.</p>

    <div class="details-box">
        <p><strong>Fecha de reactivación:</strong> {{ $fecha }}</p>
        @if($motivo)
            <p><strong>Comentario del moderador:</strong> {{ $motivo }}</p>
        @endif
    </div>

    <p>Ya puedes acceder a tu cuenta y disfrutar de todas las funcionalidades de Blitzvideo:</p>
    
    <ul style="padding-left: 20px; line-height: 1.8;">
        <li>📹 Subir y compartir videos</li>
        <li>💬 Comentar e interactuar con la comunidad</li>
        <li>👍 Valorar a tus videos favoritos</li>
        <li>📊 Gestionar tu canal</li>
    </ul>



    <p>Si tienes alguna pregunta o necesitas ayuda, no dudes en contactarnos.</p>
    
    <p>¡Bienvenido de nuevo!</p>
    
    <p style="margin-top: 20px;">
        Saludos,<br>
        <strong>El equipo de moderación de Blitzvideo</strong>
    </p>
    </div>
    </body>
</html>