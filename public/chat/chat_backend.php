<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id']) || !isset($data['receiver_id']) || !isset($data['message']) || !isset($data['category'])) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'];
$message = $data['message'];
$category = $data['category'];

// Estado inicial del mensaje cuando se envía
$status = 'no_leido';

$sql = "INSERT INTO chats (sender_id, receiver_id, message, status, category) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $user_id, $receiver_id, $message, $status, $category);
$stmt->execute();

echo json_encode(["status" => "success"]);
?>
