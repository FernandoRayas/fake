<?php
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background: #4AC29A;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to top, #BDFFF3, #4AC29A);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to top, #BDFFF3, #4AC29A); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
            display: flex;
            flex-direction: column;
            align-items: center;
            height: 100vh;
            position: relative;
        }
        .logout-container {
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .chat-container {
            width: 100%;
            max-width: 600px;
            background: white;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 50px;
        }
        .chat-header {
            background: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .chat-body {
            height: 400px;
            overflow-y: auto;
            padding: 15px;
            background: #e9ecef;
        }
        .chat-message {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            max-width: 75%;
        }
        .message-sent {
            background: #007bff;
            color: white;
            align-self: flex-end;
            text-align: right;
        }
        .message-received {
            background: #ffffff;
            border: 1px solid #dee2e6;
        }
        .chat-footer {
            padding: 10px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
        }
        .chat-footer input {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
        .chat-footer button {
            margin-left: 10px;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
    </div>
    <div class="chat-container">
        <div class="chat-header">Chat en Vivo</div>
        <div class="chat-body d-flex flex-column">
            <div class="chat-message message-received">Hola, ¿cómo estás?</div>
            <div class="chat-message message-sent">¡Hola! Todo bien, ¿y tú?</div>
        </div>
        <div class="chat-footer">
            <input type="text" class="form-control" placeholder="Escribe un mensaje...">
            <button class="btn btn-primary">Enviar</button>
        </div>
    </div>
</body>
</html>