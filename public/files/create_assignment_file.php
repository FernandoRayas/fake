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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['assignment-files'])) {
    $files = $_FILES['assignment-files'];
    $areFilesUplodaded = true;
    $errors = [];

    for ($fileIndex = 0; $fileIndex < count($files['name']); $fileIndex++) {
        if ($files['error'][$fileIndex] === UPLOAD_ERR_OK) {
            $tempName = $files['tmp_name'][$fileIndex];
            $fileName = $files['name'][$fileIndex];
            $fileSize = $files['size'][$fileIndex];

            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $path = "uploads/" . uniqid() . $files['name'][$fileIndex];

            $allowedExtensions = [
                "pdf",
                "docx",
                "xlsx",
                "csv",
                "pptx",
                "jpg",
                "jpeg",
                "png",
                "mp3",
                "wav",
                "mp4",
                "txt",
                "rft",
                "zip",
            ];

            if (!in_array($fileExtension, $allowedExtensions)) {
                $errors[] = "El archivo " . $fileName . "tiene una extensión no permitida";
                $areFilesUplodaded = false;
                continue;
            }

            if (!is_dir("uploads")) {
                mkdir("uploads");
            }

            if (move_uploaded_file($tempName, $path)) {
                $createFileSql = "INSERT INTO files (file_name, file_path, file_size, file_type) VALUES (?,?,?,?)";
                $createFileStmt = $conn->prepare($createFileSql);
                $createFileStmt->bind_param('ssis', $fileName, $path, $fileSize, $fileExtension);
                if ($createFileStmt->execute()) {
                    $createFileStmt->close();

                    $getLastAssignmentIdQuery = $conn->query("SELECT MAX(assignment_id) as last_id FROM assignments");
                    $lastAssignmentId = $getLastAssignmentIdQuery->fetch_assoc()['last_id'];

                    $getLastFileIdQuery = $conn->query("SELECT MAX(file_id) as file_id FROM files");
                    $lastFileId = $getLastFileIdQuery->fetch_assoc()['file_id'];

                    $linkAssignmentFileSql = "INSERT INTO assignments_files (assignment, file) VALUES (?,?)";
                    $linkAssignmentFileStmt = $conn->prepare($linkAssignmentFileSql);
                    $linkAssignmentFileStmt->bind_param('ii', $lastAssignmentId, $lastFileId);
                    if ($linkAssignmentFileStmt->execute()) {
                    } else {
                        $errors[] = "Error al enlazar el archivo " . $fileName . " con la tarea " . $lastAssignmentId;
                        return false;
                    }
                } else {
                    $errors[] = "Error al insertar " . $fileName . " en la base de datos";
                    $areFilesUplodaded = false;
                }
            } else {
                $errors[] = "Error en la subida del archivo " . $files['name'][$fileIndex];
                $areFilesUplodaded = false;
            }
        }
    }

    if ($areFilesUplodaded) {
        $response = [
            "isFileUplodaded" => true,
        ];
    } else {
        $response = [
            "isFileUplodaded" => false,
            "errors" => $errors
        ];
    }

    echo json_encode($response);
} else {
    $response = [
        "error" => "Petición Inválida"
    ];

    echo json_encode($response);
}
