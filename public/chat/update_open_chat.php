<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$chat_with = $_POST['chat_with'] ?? null;

if (!$chat_with) {
    echo json_encode(["error" => "Falta el ID del usuario con el que se chatea"]);
    exit();
}

// Guardamos en la sesión que este usuario tiene abierta la conversación
$_SESSION['chat_open'] = $chat_with;

echo json_encode(["status" => "success"]);
?>
