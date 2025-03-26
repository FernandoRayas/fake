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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el objeto de la peticion en formato JSON
    $request = json_decode(file_get_contents("php://input"));

    $topicName = $request->topicName;
    $topicDescription = $request->topicDescription;
    $course = $request->course;

    $createAssignmentSql = "INSERT INTO topics (topic_name, topic_description, course) VALUES (?,?,?)";
    $createAssignmentStmt = $conn->prepare($createAssignmentSql);
    $createAssignmentStmt->bind_param('ssi', $topicName, $topicDescription, $course);

    if ($createAssignmentStmt->execute()) {
        $response = [
            "isTopicCreated" => true
        ];
    } else {
        $response = [
            "isTopicCreated" => false
        ];
    }

    echo json_encode($response);
}
