<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Blitzvideo' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f7fa;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        .logo span {
            color: #3498db;
        }
        .content {
            padding: 20px 0;
            line-height: 1.6;
            color: #333;
        }
        .details-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            border-left: 4px solid #3498db;
        }
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #3498db;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 15px 0;
            font-weight: bold;
        }
        .btn:hover {
            background: #2980b9;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 2px solid #ecf0f1;
            color: #95a5a6;
            font-size: 12px;
            margin-top: 20px;
        }
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🎬 <span>Blitz</span>video</div>
        </div>
        <div class="content">
            @yield('content')
        </div>
        <div class="footer">
            <p>© {{ date('Y') }} Blitzvideo. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>