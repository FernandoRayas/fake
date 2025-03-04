<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id']) || !isset($data['receiver_id']) || !isset($data['message'])) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'];
$message = $data['message'];

$sql = "INSERT INTO chats (sender_id, receiver_id, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $user_id, $receiver_id, $message);
$stmt->execute();

echo json_encode(["status" => "success"]);
?>
