<?php


// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $sql = "SELECT courses.* FROM courses JOIN user_courses ON courses.course_id = user_courses.course WHERE user_courses.user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
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
