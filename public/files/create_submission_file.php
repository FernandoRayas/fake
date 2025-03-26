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
    $files = $_FILES['submission-files'];
    $areFilesUplodaded = true;
    $errors = [];

    $isAlreadySubmitted = false;

    $submittedAssignmentSql = "SELECT * FROM submissions_assignments_files WHERE assignment = ? AND student = ?";
    $submittedAssignmentStmt = $conn->prepare($submittedAssignmentSql);
    $submittedAssignmentStmt->bind_param('ii', $_GET['aid'], $_SESSION['user_id']);
    $submittedAssignmentStmt->execute();
    $submittedAssignmentResult = $submittedAssignmentStmt->get_result();

    if ($submittedAssignmentResult->num_rows > 0) {
        $isAlreadySubmitted = true;
    }

    if ($isAlreadySubmitted) {
        $response = [
            "response" => [
                "isAlreadySubmitted" => true,
            ],
        ];

        echo json_encode($response);
    } else {
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

                        $getLastFileIdQuery = $conn->query("SELECT MAX(file_id) as file_id FROM files");
                        $lastFileId = $getLastFileIdQuery->fetch_assoc()['file_id'];

                        $linkSubmissionSql = "INSERT INTO submissions_assignments_files (assignment, file, student) VALUES (?,?,?)";
                        $linkSubmissionStmt = $conn->prepare($linkSubmissionSql);
                        $linkSubmissionStmt->bind_param('iii', $_GET['aid'], $lastFileId, $_SESSION['user_id']);
                        if ($linkSubmissionStmt->execute()) {
                        } else {
                            $errors[] = "Error al enlazar el archivo " . $fileName . " con la entrega " . $lastAssignmentId;
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
                "response" => [
                    "isFileUploaded" => true,
                ]
            ];
        } else {
            $response = [
                "response" => [
                    "isFileUploaded" => false,
                    "errors" => $errors
                ]
            ];
        }

        echo json_encode($response);
    }
} else {
    $response = [
        "response" => [
            "error" => "Petición Inválida"
        ]
    ];

    echo json_encode($response);
}
