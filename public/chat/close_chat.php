<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$conn->query("DELETE FROM chat_sessions WHERE user_id = $user_id");

echo json_encode(["status" => "chat cerrado"]);
?>
