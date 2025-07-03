<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesión
session_start();
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master')) {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php"; // Asegúrate de que esta ruta sea correcta para tu conexión a la DB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el objeto de la petición en formato JSON
    $request = json_decode(file_get_contents("php://input"));

    // Validar que se ha proporcionado un ID para la tarea a modificar
    if (!isset($request->assignmentId)) {
        echo json_encode(["success" => false, "message" => "El ID de la tarea es requerido para la modificación."]);
        exit();
    }

    $assignmentId = $request->assignmentId;
    $assignmentName = $request->assignmentName ?? null; // Usar null coalescing para campos opcionales
    $assignmentDescription = $request->assignmentDescription ?? null;
    $assignmentMaxScore = $request->assignmentMaxScore ?? null;
    $assignmentSubmissionDate = $request->assignmentSubmissionDate ?? null;
    $assignmentSubmissionTime = $request->assignmentSubmissionTime ?? null;
    $topic = $request->topic ?? null;

    // Construir dinámicamente la consulta UPDATE
    $updateFields = [];
    $bindParams = [];
    $bindTypes = '';

    if ($assignmentName !== null) {
        $updateFields[] = "assignment_name = ?";
        $bindParams[] = $assignmentName;
        $bindTypes .= 's';
    }
    if ($assignmentDescription !== null) {
        $updateFields[] = "assignment_description = ?";
        $bindParams[] = $assignmentDescription;
        $bindTypes .= 's';
    }
    if ($assignmentMaxScore !== null) {
        $updateFields[] = "max_score = ?";
        $bindParams[] = $assignmentMaxScore;
        $bindTypes .= 'i';
    }
    if ($assignmentSubmissionDate !== null) {
        $updateFields[] = "submission_date = ?";
        $bindParams[] = $assignmentSubmissionDate;
        $bindTypes .= 's';
    }
    if ($assignmentSubmissionTime !== null) {
        $updateFields[] = "submission_time = ?";
        $bindParams[] = $assignmentSubmissionTime;
        $bindTypes .= 's';
    }
    if ($topic !== null) {
        $updateFields[] = "topic = ?";
        $bindParams[] = $topic;
        $bindTypes .= 'i';
    }

    // Verificar si hay campos para actualizar
    if (empty($updateFields)) {
        echo json_encode(["success" => false, "message" => "No se proporcionaron campos para actualizar."]);
        exit();
    }

    $updateAssignmentSql = "UPDATE assignments SET " . implode(", ", $updateFields) . " WHERE id = ?";

    // Añadir el ID al final de los parámetros de enlace
    $bindParams[] = $assignmentId;
    $bindTypes .= 'i'; // El ID es un entero

    $updateAssignmentStmt = $conn->prepare($updateAssignmentSql);

    // Unir los parámetros dinámicamente
    // Aquí es crucial usar '...' para desempaquetar el array de parámetros
    if (!empty($bindParams)) {
        $updateAssignmentStmt->bind_param($bindTypes, ...$bindParams);
    }


    if ($updateAssignmentStmt->execute()) {
        if ($updateAssignmentStmt->affected_rows > 0) {
            $response = [
                "success" => true,
                "message" => "Tarea actualizada exitosamente."
            ];
        } else {
            $response = [
                "success" => false,
                "message" => "No se encontró la tarea con el ID proporcionado o no hubo cambios para aplicar."
            ];
        }
    } else {
        $response = [
            "success" => false,
            "message" => "Error al actualizar la tarea: " . $updateAssignmentStmt->error
        ];
    }

    echo json_encode($response);
} else {
    // Si la solicitud no es POST, se podría manejar aquí (ej. devolver un error)
    echo json_encode(["success" => false, "message" => "Método de solicitud no permitido. Usa POST para esta operación."]);
}

// Cerrar la conexión
$conn->close();
