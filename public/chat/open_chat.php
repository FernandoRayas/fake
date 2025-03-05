<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$receiver_id = $data['receiver_id'] ?? null;

if (!$receiver_id) {
    echo json_encode(["error" => "Falta el ID del receptor"]);
    exit();
}

// Limpiar sesiones anteriores del usuario
$conn->query("DELETE FROM chat_sessions WHERE user_id = $user_id");

// Registrar la nueva sesión de chat abierta
$sql = "INSERT INTO chat_sessions (user_id, receiver_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $receiver_id);
$stmt->execute();
$stmt->close();

echo json_encode(["status" => "chat abierto"]);
?>
