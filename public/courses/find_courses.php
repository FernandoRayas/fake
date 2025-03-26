<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $searchTerm = '%' . $_GET['cname'] . '%';
    $sql = "SELECT * FROM courses WHERE created_by = ? AND course_name LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $_SESSION['user_id'], $searchTerm);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $courses = $result->fetch_all(MYSQLI_ASSOC);
        $response = [
            "courses" => $courses
        ];
    } else {
        $response = [
            "msg" => "Error al obtener los cursos"
        ];
    }

    echo json_encode($response);
}
