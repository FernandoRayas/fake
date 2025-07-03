<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();

// Verificar si el usuario ha iniciado sesion y tiene el rol adecuado
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master')) {
    header("Location: ../index.php"); // Redirigir si no tiene permisos
    exit();
}

// Incluir la conexion a la base de datos
include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener el objeto de la peticion en formato JSON
    $request = json_decode(file_get_contents("php://input"));

    // Validar que los datos necesarios estan presentes
    if (!isset($request->courseId) || !isset($request->courseName) || !isset($request->courseDescription)) {
        $response = [
            "isCourseUpdated" => false,
            "msg" => "Faltan datos obligatorios para actualizar el curso."
        ];
        echo json_encode($response);
        exit();
    }

    // Obtener los datos del curso a actualizar
    $courseId = (int) $request->courseId; // Asegurarse de que sea un entero
    $courseName = trim($request->courseName);
    $courseDescription = trim($request->courseDescription);
    // Opcionalmente, puedes anadir mas campos para modificar, como el ID del profesor, etc.

    // Consulta SQL para actualizar el curso
    $sql = "UPDATE courses SET course_name = ?, course_description = ? WHERE course_id = ?";
    $stmt = $conn->prepare($sql);

    // Verificar si la preparacion de la consulta fue exitosa
    if ($stmt === false) {
        $response = [
            "isCourseUpdated" => false,
            "msg" => "Error al preparar la consulta de actualizacion: " . $conn->error
        ];
        echo json_encode($response);
        exit();
    }

    // Vincular los parametros a la consulta
    $stmt->bind_param("ssi", $courseName, $courseDescription, $courseId);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Verificar si se afectaron filas (si se actualizo el curso)
        if ($stmt->affected_rows > 0) {
            $response = [
                "isCourseUpdated" => true,
                "msg" => "Curso actualizado correctamente."
            ];
        } else {
            $response = [
                "isCourseUpdated" => false,
                "msg" => "No se realizaron cambios o el curso no existe."
            ];
        }
    } else {
        $response = [
            "isCourseUpdated" => false,
            "msg" => "Error al actualizar el curso: " . $stmt->error
        ];
    }

    // Cerrar la declaracion
    $stmt->close();
    // Cerrar la conexion (opcional, si no se realizan mas operaciones)
    $conn->close();

    echo json_encode($response);
} else {
    // Si la solicitud no es POST, devolver un error
    $response = [
        "isCourseUpdated" => false,
        "msg" => "Metodo de solicitud no permitido."
    ];
    echo json_encode($response);
}
