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
include "../course_codes/create_course_code.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el objeto de la peticion en formato JSON
    $request = json_decode(file_get_contents("php://input"));

    // Obtener los datos
    $courseName = trim($request->courseName);
    $courseDescription = trim($request->courseDescription);
    $teacherId = $_SESSION['user_id'];

    $sql = "INSERT INTO courses (course_name, course_description, created_by) VALUES (?,?,?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $courseName, $courseDescription, $teacherId);

    if ($stmt->execute()) {

        // Get last inserted index
        $maxSql = "SELECT MAX(course_id) as lastid FROM courses";
        $lastId = $conn->query($maxSql)->fetch_assoc()['lastid'];

        $courseCode = createCourseCode(8);
        $courseCodeSql = "INSERT INTO course_codes (code, course) VALUES (?,?)";
        $courseCodeStmt = $conn->prepare($courseCodeSql);
        $courseCodeStmt->bind_param('si', $courseCode, $lastId);
        $courseCodeStmt->execute();

        $defaultTopic = "General";
        $defaultTopicSql = "INSERT INTO topics (topic_name, course) VALUES (?,?)";
        $defaultTopicStmt = $conn->prepare($defaultTopicSql);
        $defaultTopicStmt->bind_param('si', $defaultTopic, $lastId);
        $defaultTopicStmt->execute();

        $enrollUserSql = "INSERT INTO user_courses (user, course, enrollment_role) VALUES (?,?,?)";
        $enrollRole = 'TEACHER';
        $enrollUserStmt = $conn->prepare($enrollUserSql);
        $enrollUserStmt->bind_param('iis', $teacherId, $lastId, $enrollRole);
        $enrollUserStmt->execute();

        $response = [
            "isCourseCreated" => true,
            "msg" => "Curso creado correctamente"
        ];
    } else {
        $response = [
            "isCourseCreated" => false,
            "msg" => "Error al crear el curso"
        ];
    }

    echo json_encode($response);
}
