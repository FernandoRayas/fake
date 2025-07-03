<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesión
session_start();
// Verificar el rol del usuario para permitir la eliminación de tareas (solo 'admin' o 'master')
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master')) {
    // Si el usuario no tiene los roles permitidos, redirigirlo a la página principal y salir
    header("Location: ../index.php");
    exit();
}

// Incluir el archivo de conexión a la base de datos
include "../modelo/conexion.php";

// Verificar si la solicitud HTTP es de tipo POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener el cuerpo de la petición en formato JSON
    $request = json_decode(file_get_contents("php://input"));

    // Verificar si se ha proporcionado el 'assignmentId' en la petición
    if (isset($request->assignmentId)) {
        // Obtener el ID de la tarea desde la petición
        $assignmentId = $request->assignmentId;

        // Preparar la consulta SQL para eliminar la tarea
        $deleteAssignmentSql = "DELETE FROM assignments WHERE assignment_id = ?";
        $deleteAssignmentStmt = $conn->prepare($deleteAssignmentSql);

        // Vincular el parámetro ID a la consulta preparada
        // 'i' indica que el parámetro es de tipo entero
        $deleteAssignmentStmt->bind_param('i', $assignmentId);

        // Ejecutar la consulta
        if ($deleteAssignmentStmt->execute()) {
            // Si la eliminación fue exitosa, preparar una respuesta JSON indicándolo
            $response = [
                "isAssignmentDeleted" => true,
                "message" => "Tarea eliminada exitosamente."
            ];
        } else {
            // Si hubo un error al ejecutar la consulta, preparar una respuesta JSON con un mensaje de error
            $response = [
                "isAssignmentDeleted" => false,
                "message" => "Error al eliminar la tarea: " . $deleteAssignmentStmt->error
            ];
        }

        // Cerrar la declaración preparada
        $deleteAssignmentStmt->close();
    } else {
        // Si 'assignmentId' no fue proporcionado en la petición, preparar una respuesta de error
        $response = [
            "isAssignmentDeleted" => false,
            "message" => "El 'assignmentId' no fue proporcionado en la solicitud."
        ];
    }

    // Devolver la respuesta en formato JSON
    echo json_encode($response);
} else {
    // Si la solicitud no es POST, devolver un error indicando que solo se permite POST
    $response = [
        "isAssignmentDeleted" => false,
        "message" => "Método de solicitud no permitido. Solo se acepta POST."
    ];
    echo json_encode($response);
}

// Cerrar la conexión a la base de datos (opcional, PHP la cierra automáticamente al finalizar el script)
$conn->close();
