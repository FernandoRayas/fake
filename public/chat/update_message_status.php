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

// Verificar si el usuario tiene el chat abierto
$sql = "SELECT * FROM chat_sessions WHERE user_id = ? AND receiver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $receiver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "No tienes el chat abierto con este usuario"]);
    exit();
}

// Marcar mensajes como "visto"
$sql = "UPDATE chats SET status = 'visto' WHERE sender_id = ? AND receiver_id = ? AND status = 'no_leido'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $receiver_id, $user_id);
$stmt->execute();

echo json_encode(["status" => "success"]);
?>
