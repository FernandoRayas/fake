<?php
session_start();
require '../modelo/conexion.php';

header('Content-Type: application/json'); // Asegurar respuesta JSON

if (!isset($_SESSION['user_id'])) {
    error_log("Error: Usuario no autenticado.");
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT id, name FROM users WHERE id != ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = [
            "id" => $row["id"],
            "username" => $row["name"]  // Corregido aquí
        ];
    }

    echo json_encode($users);
} catch (Exception $e) {
    error_log("Error en get_users.php: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
