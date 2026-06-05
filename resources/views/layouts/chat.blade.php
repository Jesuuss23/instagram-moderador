<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MultiChat IG - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .chat-container {
            height: 100vh;
            background: white;
        }
        .conversaciones-list {
            height: calc(100vh - 120px);
            overflow-y: auto;
        }
        .mensajes-area {
            height: calc(100vh - 180px);
            overflow-y: auto;
        }
        .mensaje-cliente {
            background-color: #f1f3f5;
            border-radius: 15px 15px 15px 5px;
        }
        .mensaje-cuenta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 5px 15px;
        }
        .hover-conversacion:hover {
            background-color: #e9ecef;
            cursor: pointer;
        }
        .activa {
            background-color: #e3f2fd;
            border-left: 4px solid #667eea;
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('content')
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>