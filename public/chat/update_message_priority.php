<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json');  // Asegura que la respuesta sea JSON

// Verifica si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

// Obtener los datos JSON enviados
$data = json_decode(file_get_contents("php://input"), true);

// Depuración: Imprimir los datos recibidos
file_put_contents('php://stderr', print_r($data, TRUE));

if (!isset($data['message_id']) || !isset($data['priority'])) {
    echo json_encode(["error" => "Datos insuficientes"]);
    exit();
}

$message_id = $data['message_id'];
$priority = $data['priority'];

// Actualizamos el mensaje con la nueva prioridad
try {
    $sql = "UPDATE chats SET priority = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $priority, $message_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "no_changes"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Error al actualizar la urgencia: " . $e->getMessage()]);
}
?>
