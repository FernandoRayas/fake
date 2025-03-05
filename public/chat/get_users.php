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
    // Seleccionamos el id, nombre y rol de los usuarios, excluyendo al usuario actual
    $sql = "SELECT id, name, role FROM users WHERE id != ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $users = [];

    // Iteramos sobre los usuarios
    while ($row = $result->fetch_assoc()) {
        // Contamos los mensajes urgentes para este usuario
        $urgentCountSql = "SELECT COUNT(*) AS urgent_count FROM chats WHERE receiver_id = ? AND priority = 'urgente'";
        $urgentStmt = $conn->prepare($urgentCountSql);
        $urgentStmt->bind_param("i", $row['id']);
        $urgentStmt->execute();
        $urgentResult = $urgentStmt->get_result();
        $urgentCount = $urgentResult->fetch_assoc()['urgent_count'];

        // Agregamos el número de mensajes urgentes al usuario
        $users[] = [
            "id" => $row["id"],
            "username" => $row["name"],  // Nombre del usuario
            "role" => $row["role"],      // Rol del usuario
            "urgent_count" => $urgentCount // Número de mensajes urgentes
        ];
    }

    echo json_encode($users);
} catch (Exception $e) {
    error_log("Error en get_users.php: " . $e->getMessage());
    echo json_encode(["error" => $e->getMessage()]);
}
?>
