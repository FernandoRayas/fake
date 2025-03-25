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

    $assignmentName = $request->assignmentName;
    $assignmentDescription = $request->assignmentDescription;
    $assignmentMaxScore = $request->assignmentMaxScore;
    $assignmentSubmissionDate = $request->assignmentSubmissionDate;
    $assignmentSubmissionTime = $request->assignmentSubmissionTime;
    $topic = $request->topic;

    $createAssignmentSql = "INSERT INTO assignments (assignment_name, assignment_description, max_score, submission_date, submission_time, topic) VALUES (?,?,?,?,?,?)";
    $createAssignmentStmt = $conn->prepare($createAssignmentSql);
    $createAssignmentStmt->bind_param('ssissi', $assignmentName, $assignmentDescription, $assignmentMaxScore, $assignmentSubmissionDate, $assignmentSubmissionTime, $topic);

    if ($createAssignmentStmt->execute()) {
        $response = [
            "isAssignmentCreated" => true
        ];
    } else {
        $response = [
            "isAssignmentCreated" => false
        ];
    }

    echo json_encode($response);
}
