<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'master') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $req = json_decode(file_get_contents("php://input"));

    $studentId = $req->studentId;
    $score = $req->score;
    $assignmentId = $req->aid;

    $sql = "UPDATE submissions_assignments_files SET score = ?, status = 'SCORED' WHERE student = ? AND assignment = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $score, $studentId, $assignmentId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false]);
    }
}
