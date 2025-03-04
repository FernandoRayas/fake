<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['receiver_id'])) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['receiver_id'];

$sql = "SELECT sender_id, message FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
        $messages[] = [
            "text" => $row["message"],
            "sent" => $row["sender_id"] == $user_id,
            "sender_id" => $row["sender_id"], // <-- Agregado para depuración
            "receiver_id" => $receiver_id    // <-- Agregado para depuración
        ];
    }
    
    echo json_encode(["messages" => $messages]);
    
?>