<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($_SESSION['user_id']) || !isset($data['receiver_id'])) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'];

try {
    // Actualizamos los mensajes a "visto" para el receptor actual
    $sql = "UPDATE chats SET status = 'visto' WHERE receiver_id = ? AND sender_id = ? AND status = 'no_leido'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $receiver_id, $user_id); // Cambiar solo los mensajes que fueron enviados al receptor
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "no_changes"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Error al actualizar el estado: " . $e->getMessage()]);
}
?>
