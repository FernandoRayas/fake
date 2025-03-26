<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request = json_decode(file_get_contents("php://input"));

    $code = $request->code;

    $getCourseIdSql = "SELECT course_id FROM courses JOIN course_codes ON courses.course_id = course_codes.course WHERE code = ?";
    $checkUserEnrolledSql = "SELECT * FROM user_courses WHERE user = ? AND course = ?";

    // Obtener el ID del curso con el codigo recibido
    $getCourseIdStmt = $conn->prepare($getCourseIdSql);
    $getCourseIdStmt->bind_param('s', $code);
    $getCourseIdStmt->execute();
    $getCourseIdResult = $getCourseIdStmt->get_result();

    if ($getCourseIdResult->num_rows === 0) {
        $response = [
            "isCourseExistent" => false,
            "error" => true,
            "reason" => "Curso no Existente"
        ];
    } else {
        $courseId = $getCourseIdResult->fetch_assoc()['course_id'];

        // Verificar si el usuario ya esta inscrito en el curso
        $checkUserEnrolledStmt = $conn->prepare($checkUserEnrolledSql);
        $checkUserEnrolledStmt->bind_param('ii', $_SESSION['user_id'], $courseId);
        $checkUserEnrolledStmt->execute();
        $checkUserEnrolledResult = $checkUserEnrolledStmt->get_result();

        // Si no esta inscrito
        if ($checkUserEnrolledResult->num_rows == 0) {
            $enrollUserSql = "INSERT INTO user_courses (user, course, enrollment_role) VALUES (?,?,?)";
            $enrollRole = 'STUDENT';
            $enrollUserStmt = $conn->prepare($enrollUserSql);
            $enrollUserStmt->bind_param('iis', $_SESSION['user_id'], $courseId, $enrollRole);

            if ($enrollUserStmt->execute()) {
                $response = [
                    "isUserEnrolled" => true,
                ];
            } else {
                $response = [
                    "isUserEnrolled" => false
                ];
            }
        } else {
            $response = [
                "isUserAlreadyEnrolled" => true,
                "msg" => "Ya estas inscrito en el curso",
            ];
        }
    }


    echo json_encode($response);
}
