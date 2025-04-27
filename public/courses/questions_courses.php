<?php
// Controlador para el sistema de foros de cursos (questions_courses.php)
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir la conexión a la base de datos si no está ya incluida
if (!isset($conn)) {
    include_once "../modelo/conexion.php";
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        // No es una petición AJAX, redirigir
        header("Location: ../../index.php");
        exit();
    } else {
        // Es una petición AJAX, devolver error en JSON
        echo json_encode(['success' => false, 'message' => 'No autenticado']);
        exit();
    }
}

// Obtener el ID del usuario y su rol
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Procesar acciones según el parámetro 'action'
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    switch ($action) {
        case 'add_question':
            // Añadir una nueva pregunta
            if (isset($_POST['course_id']) && isset($_POST['question_title']) && isset($_POST['question_content'])) {
                $course_id = intval($_POST['course_id']);
                $question_title = trim($_POST['question_title']);
                $question_content = trim($_POST['question_content']);
                
                // Validar datos
                if (empty($question_title) || empty($question_content)) {
                    echo json_encode(['success' => false, 'message' => 'El título y el contenido de la pregunta son obligatorios']);
                    exit();
                }
                
                // Verificar que el usuario está inscrito en el curso o es profesor
                $valid_user = false;
                
                if ($user_role === 'teacher') {
                    // Verificar si el profesor está asociado con este curso
                    $check_query = "SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    $valid_user = $stmt->get_result()->num_rows > 0;
                } else {
                    // Verificar si el usuario está inscrito en este curso como estudiante
                    $check_query = "SELECT * FROM user_courses WHERE course = ? AND user = ? AND enrollment_role = 'STUDENT'";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    $valid_user = $stmt->get_result()->num_rows > 0;
                }
                
                if (!$valid_user) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permiso para hacer preguntas en este curso']);
                    exit();
                }
                
                // Insertar la pregunta
                $insert_query = "INSERT INTO courses_questions (course_id, user_id, question_title, question_content) 
                                VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iiss", $course_id, $user_id, $question_title, $question_content);

                if ($stmt->execute()) {
                    // Redirigir al curso después de insertar
                    header("Location: ../../pages/course.php?cid=" . $course_id);
                    exit(); // Importante para asegurar que el script se detenga después de la redirección
                } else {
                    // Mostrar error si hay problema con la inserción
                    echo json_encode(['success' => false, 'message' => 'Error al publicar la pregunta: ' . $conn->error]);
            }
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            }
            break;
            
        case 'add_answer':
            // Añadir una respuesta a una pregunta
            if (isset($_POST['question_id']) && isset($_POST['answer_content']) && isset($_POST['course_id'])) {
                $question_id = intval($_POST['question_id']);
                $answer_content = trim($_POST['answer_content']);
                $course_id = intval($_POST['course_id']);
                
                // Validar datos
                if (empty($answer_content)) {
                    echo json_encode(['success' => false, 'message' => 'El contenido de la respuesta es obligatorio']);
                    exit();
                }
                
                // Verificar que el usuario está inscrito en el curso o es profesor
                $valid_user = false;
                $is_teacher = false;
                
                if ($user_role === 'teacher') {
                    // Verificar si el profesor está asociado con este curso
                    $check_query = "SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    if ($stmt->get_result()->num_rows > 0) {
                        $valid_user = true;
                        $is_teacher = true;
                    }
                } else {
                    // Verificar si el usuario está inscrito en este curso como estudiante
                    $check_query = "SELECT * FROM user_courses WHERE course = ? AND user = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    $valid_user = $stmt->get_result()->num_rows > 0;
                }
                
                if (!$valid_user) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permiso para responder en este curso']);
                    exit();
                }
                
                // Verificar que la pregunta existe y pertenece al curso
                $check_question = "SELECT * FROM courses_questions WHERE question_id = ? AND course_id = ?";
                $stmt = $conn->prepare($check_question);
                $stmt->bind_param("ii", $question_id, $course_id);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'La pregunta no existe o no pertenece a este curso']);
                    exit();
                }
                
                // Insertar la respuesta
                $insert_query = "INSERT INTO courses_answers (question_id, user_id, answer_content, is_teacher_answer) 
                                VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param("iisi", $question_id, $user_id, $answer_content, $is_teacher);
                
                if ($stmt->execute()) {
                    header("Location: ../../pages/course.php?cid=" . $course_id);
                    exit(); // Importante para asegurar que el script se detenga después de la redirección
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al publicar la respuesta: ' . $conn->error]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            }
            break;
            
        case 'delete_question':
            // Eliminar una pregunta (solo el propietario o profesor del curso)
            if (isset($_POST['question_id']) && isset($_POST['course_id'])) {
                $question_id = intval($_POST['question_id']);
                $course_id = intval($_POST['course_id']);
                
                // Verificar propiedad de la pregunta o si es profesor del curso
                $can_delete = false;
                
                if ($user_role === 'teacher') {
                    // Verificar si el profesor está asociado con este curso
                    $check_query = "SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    $can_delete = $stmt->get_result()->num_rows > 0;
                } else {
                    // Verificar si el estudiante es el propietario de la pregunta
                    $check_query = "SELECT * FROM courses_questions WHERE question_id = ? AND user_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $question_id, $user_id);
                    $stmt->execute();
                    $can_delete = $stmt->get_result()->num_rows > 0;
                }
                
                if (!$can_delete) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta pregunta']);
                    exit();
                }
                
                // Primero eliminar todas las respuestas asociadas
                $delete_answers = "DELETE FROM courses_answers WHERE question_id = ?";
                $stmt = $conn->prepare($delete_answers);
                $stmt->bind_param("i", $question_id);
                $stmt->execute();
                
                // Luego eliminar la pregunta
                $delete_query = "DELETE FROM courses_questions WHERE question_id = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("i", $question_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Pregunta eliminada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la pregunta: ' . $conn->error]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            }
            break;
            
        case 'delete_answer':
            // Eliminar una respuesta (solo el propietario, el dueño de la pregunta o profesor del curso)
            if (isset($_POST['answer_id']) && isset($_POST['course_id'])) {
                $answer_id = intval($_POST['answer_id']);
                $course_id = intval($_POST['course_id']);
                
                // Obtener detalles de la respuesta
                $answer_query = "SELECT a.*, q.user_id as question_owner_id 
                               FROM courses_answers a
                               JOIN courses_questions q ON a.question_id = q.question_id
                               WHERE a.answer_id = ?";
                $stmt = $conn->prepare($answer_query);
                $stmt->bind_param("i", $answer_id);
                $stmt->execute();
                $answer_result = $stmt->get_result();
                
                if ($answer_result->num_rows === 0) {
                    echo json_encode(['success' => false, 'message' => 'La respuesta no existe']);
                    exit();
                }
                
                $answer_data = $answer_result->fetch_assoc();
                
                // Verificar permiso para eliminar
                $can_delete = false;
                
                // El propietario de la respuesta puede eliminarla
                if ($answer_data['user_id'] == $user_id) {
                    $can_delete = true;
                }
                // El propietario de la pregunta puede eliminar cualquier respuesta a su pregunta
                else if ($answer_data['question_owner_id'] == $user_id) {
                    $can_delete = true;
                }
                // El profesor del curso puede eliminar cualquier respuesta
                else if ($user_role === 'teacher') {
                    $check_query = "SELECT * FROM courses WHERE course_id = ? AND teacher_id = ?";
                    $stmt = $conn->prepare($check_query);
                    $stmt->bind_param("ii", $course_id, $user_id);
                    $stmt->execute();
                    $can_delete = $stmt->get_result()->num_rows > 0;
                }
                
                if (!$can_delete) {
                    echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta respuesta']);
                    exit();
                }
                
                // Eliminar la respuesta
                $delete_query = "DELETE FROM courses_answers WHERE answer_id = ?";
                $stmt = $conn->prepare($delete_query);
                $stmt->bind_param("i", $answer_id);
                
                if ($stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Respuesta eliminada correctamente']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al eliminar la respuesta: ' . $conn->error]);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
    
    exit(); // Terminar la ejecución después de procesar una acción POST
}

// Si llegamos aquí, significa que no había una acción POST para procesar,
// así que el script puede continuar con la visualización normal de la página
?>