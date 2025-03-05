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

$sql = "SELECT id, sender_id, message, status, priority, category FROM chats WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $user_id, $receiver_id, $receiver_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = [
        "id" => $row["id"],
        "text" => $row["message"],
        "status" => $row["status"],  // Agregamos el estado del mensaje
        "sender_id" => $row["sender_id"],
        "priority" => $row["priority"],
        "category" => $row["category"],
        "receiver_id" => $receiver_id,
    ];
}

echo json_encode(["messages" => $messages]);
?>
