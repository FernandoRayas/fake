<?php
// Controlador para el sistema de cuestionarios
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

// Procesar según la acción solicitada
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'create_quiz':
        createQuiz($conn);
        break;
    case 'update_quiz':
        updateQuiz($conn);
        break;
    case 'delete_quiz':
        deleteQuiz($conn);
        break;
    case 'get_quiz_details':
        getQuizDetails($conn);
        break;
    case 'get_quiz_for_student':
        getQuizForStudent($conn);
        break;
    case 'submit_answers':
        submitAnswers($conn);
        break;
    case 'get_quiz_statistics':
        getQuizStatistics($conn);
        break;
    default:
        // Si no hay acción específica, no hacer nada
        // Este controlador también se usa para cargar funciones en course_home.php
        break;
}

// Función para obtener los cuestionarios creados por un profesor en un curso
function getQuizzesBymaster($conn, $master_id, $course_id) {
    $quizzes = [];
    
    $query = "SELECT * FROM quizzes 
              WHERE created_by = ? AND course_id = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $master_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    
    return $quizzes;
}

// Función para obtener los cuestionarios disponibles para un estudiante en un curso
function getAvailableQuizzes($conn, $course_id) {
    $quizzes = [];
    
    $query = "SELECT * FROM quizzes 
              WHERE course_id = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = $row;
    }
    
    return $quizzes;
}

// Función para verificar si un usuario ya respondió un cuestionario
function hasUserAnsweredQuiz($conn, $user_id, $quiz_id) {
    $query = "SELECT COUNT(*) as count FROM answers a
              JOIN questions q ON a.question_id = q.question_id
              WHERE q.quiz_id = ? AND a.user_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $quiz_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Función para obtener el conteo de respuestas de un cuestionario
function getQuizResponseCount($conn, $quiz_id) {
    $query = "SELECT COUNT(DISTINCT user_id) as count FROM answers a
              JOIN questions q ON a.question_id = q.question_id
              WHERE q.quiz_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'];
}

// Función para obtener el nombre del profesor
function getmasterName($conn, $master_id) {
    $query = "SELECT name FROM users WHERE id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $master_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['name'];
    }
    
    return "Profesor";
}

// Función para crear un nuevo cuestionario
function createQuiz($conn) {
    // Verificar permisos (solo profesores o admin)
    if ($_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'admin') {
        $_SESSION['message'] = "No tienes permisos para crear cuestionarios.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $_POST['course_id']);
        exit();
    }
    
    // Validar los datos recibidos
    if (empty($_POST['title']) || empty($_POST['course_id'])) {
        $_SESSION['message'] = "El título del cuestionario y el curso son obligatorios.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $_POST['course_id']);
        exit();
    }
    
    // Datos del cuestionario
    $course_id = intval($_POST['course_id']);
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    $created_by = $_SESSION['user_id'];
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Insertar el cuestionario
        $query = "INSERT INTO quizzes (course_id, title, description, created_by) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issi", $course_id, $title, $description, $created_by);
        $stmt->execute();
        
        $quiz_id = $conn->insert_id;
        
        // Procesar las preguntas si existen
        if (isset($_POST['questions']) && is_array($_POST['questions'])) {
            $position = 1;
            
            foreach ($_POST['questions'] as $question) {
                // Validar datos de la pregunta
                if (empty($question['text']) || empty($question['type'])) {
                    continue;
                }
                
                // Insertar la pregunta
                $query = "INSERT INTO questions (quiz_id, question_text, question_type, position) 
                          VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issi", $quiz_id, $question['text'], $question['type'], $position);
                $stmt->execute();
                
                $question_id = $conn->insert_id;
                $position++;
                
                // Si es una pregunta cerrada, procesar las opciones
                if ($question['type'] === 'closed' && isset($question['options']) && is_array($question['options'])) {
                    foreach ($question['options'] as $option_text) {
                        if (empty($option_text)) {
                            continue;
                        }
                        
                        // Insertar la opción
                        $query = "INSERT INTO question_options (question_id, option_text) 
                                  VALUES (?, ?)";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $question_id, $option_text);
                        $stmt->execute();
                    }
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        $_SESSION['message'] = "¡Cuestionario creado con éxito!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        $_SESSION['message'] = "Error al crear el cuestionario: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redireccionar de vuelta al curso
    header("Location: ../../pages/course.php?cid=" . $course_id);
    exit();
}

// Función para actualizar un cuestionario existente
function updateQuiz($conn) {
    // Verificar permisos (solo profesores o admin)
    if ($_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'admin') {
        $_SESSION['message'] = "No tienes permisos para actualizar cuestionarios.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $_POST['course_id']);
        exit();
    }
    
    // Validar los datos recibidos
    if (empty($_POST['quiz_id']) || empty($_POST['title']) || empty($_POST['course_id'])) {
        $_SESSION['message'] = "Faltan datos requeridos para actualizar el cuestionario.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $_POST['course_id']);
        exit();
    }
    
    // Datos del cuestionario
    $quiz_id = intval($_POST['quiz_id']);
    $course_id = intval($_POST['course_id']);
    $title = $_POST['title'];
    $description = $_POST['description'] ?? '';
    
    // Verificar que el cuestionario pertenezca al profesor
    $query = "SELECT created_by FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['created_by'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['message'] = "No tienes permisos para editar este cuestionario.";
            $_SESSION['message_type'] = "danger";
            header("Location: ../../pages/course.php?cid=" . $course_id);
            exit();
        }
    } else {
        $_SESSION['message'] = "El cuestionario no existe.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $course_id);
        exit();
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Actualizar el cuestionario
        $query = "UPDATE quizzes SET title = ?, description = ? WHERE quiz_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $title, $description, $quiz_id);
        $stmt->execute();
        
        // Si hay estudiantes que ya respondieron, no modificar las preguntas
        $has_responses = hasUserAnsweredQuiz($conn, 0, $quiz_id); // 0 para verificar cualquier usuario
        
        if (!$has_responses && isset($_POST['questions']) && is_array($_POST['questions'])) {
            // Eliminar preguntas y opciones existentes
            $query = "DELETE question_options FROM question_options 
                      JOIN questions ON question_options.question_id = questions.question_id 
                      WHERE questions.quiz_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $quiz_id);
            $stmt->execute();
            
            $query = "DELETE FROM questions WHERE quiz_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $quiz_id);
            $stmt->execute();
            
            // Insertar las nuevas preguntas
            $position = 1;
            
            foreach ($_POST['questions'] as $question) {
                // Validar datos de la pregunta
                if (empty($question['text']) || empty($question['type'])) {
                    continue;
                }
                
                // Insertar la pregunta
                $query = "INSERT INTO questions (quiz_id, question_text, question_type, position) 
                          VALUES (?, ?, ?, ?)";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("issi", $quiz_id, $question['text'], $question['type'], $position);
                $stmt->execute();
                
                $question_id = $conn->insert_id;
                $position++;
                
                // Si es una pregunta cerrada, procesar las opciones
                if ($question['type'] === 'closed' && isset($question['options']) && is_array($question['options'])) {
                    foreach ($question['options'] as $option_text) {
                        if (empty($option_text)) {
                            continue;
                        }
                        
                        // Insertar la opción
                        $query = "INSERT INTO question_options (question_id, option_text) 
                                  VALUES (?, ?)";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("is", $question_id, $option_text);
                        $stmt->execute();
                    }
                }
            }
        }
        
        // Confirmar transacción
        $conn->commit();
        
        $_SESSION['message'] = "¡Cuestionario actualizado con éxito!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        $_SESSION['message'] = "Error al actualizar el cuestionario: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redireccionar de vuelta al curso
    header("Location: ../../pages/course.php?cid=" . $course_id);
    exit();
}

// Función para eliminar un cuestionario
function deleteQuiz($conn) {
    // Verificar permisos
    if ($_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'admin') {
        $_SESSION['message'] = "No tienes permisos para eliminar cuestionarios.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $_GET['cid']); // Cambiado a 'cid'
        exit();
    }
    
    // Validar parámetros (ahora usando 'cid' consistentemente)
    if (empty($_GET['quiz_id']) || empty($_GET['cid'])) {
        $_SESSION['message'] = "Faltan datos requeridos para eliminar el cuestionario.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . ($_GET['cid'] ?? '')); // Manejo seguro
        exit();
    }
    
    $quiz_id = intval($_GET['quiz_id']);
    $course_id = intval($_GET['cid']); // Variable renombrada para claridad
    
    // Verificar que el cuestionario pertenezca al profesor
    $query = "SELECT created_by FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['created_by'] != $_SESSION['user_id'] && $_SESSION['user_role'] != 'admin') {
            $_SESSION['message'] = "No tienes permisos para eliminar este cuestionario.";
            $_SESSION['message_type'] = "danger";
            header("Location: ../../pages/course.php?cid=" . $course_id);
            exit();
        }
    } else {
        $_SESSION['message'] = "El cuestionario no existe.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../../pages/course.php?cid=" . $course_id);
        exit();
    }
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // Eliminar respuestas
        $query = "DELETE answers FROM answers 
                  JOIN questions ON answers.question_id = questions.question_id 
                  WHERE questions.quiz_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        
        // Eliminar opciones de preguntas
        $query = "DELETE question_options FROM question_options 
                  JOIN questions ON question_options.question_id = questions.question_id 
                  WHERE questions.quiz_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        
        // Eliminar preguntas
        $query = "DELETE FROM questions WHERE quiz_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        
        // Eliminar el cuestionario
        $query = "DELETE FROM quizzes WHERE quiz_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        
        // Confirmar transacción
        $conn->commit();
        
        $_SESSION['message'] = "¡Cuestionario eliminado con éxito!";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $conn->rollback();
        
        $_SESSION['message'] = "Error al eliminar el cuestionario: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    
    // Redireccionar de vuelta al curso
    header("Location: ../../pages/course.php?cid=" . $course_id);
    exit();
}

// Función para obtener los detalles de un cuestionario
function getQuizDetails($conn) {
    // Verificar que se proporcionó un ID válido
    if (empty($_GET['quiz_id'])) {
        echo "ID de cuestionario no proporcionado";
        exit();
    }
    
    $quiz_id = intval($_GET['quiz_id']);
    
    // Obtener información del cuestionario
    $query = "SELECT q.*, u.name as creator_name 
                FROM quizzes q 
                JOIN users u ON q.created_by = u.id 
                WHERE q.quiz_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($quiz = $result->fetch_assoc()) {
        // Obtener preguntas del cuestionario
        $query = "SELECT * FROM questions 
                  WHERE quiz_id = ? 
                  ORDER BY position";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $questions_result = $stmt->get_result();
        $questions = [];
        
        while ($question = $questions_result->fetch_assoc()) {
            // Si es pregunta cerrada, obtener opciones
            if ($question['question_type'] === 'closed') {
                $query = "SELECT * FROM question_options 
                          WHERE question_id = ?";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $options_result = $stmt->get_result();
                $options = [];
                
                while ($option = $options_result->fetch_assoc()) {
                    $options[] = $option;
                }
                
                $question['options'] = $options;
            }
            
            $questions[] = $question;
        }
        
        // Obtener estadísticas básicas
        $response_count = getQuizResponseCount($conn, $quiz_id);
        
        // Generar HTML para la vista detallada
        echo '<div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5><i class="fas fa-clipboard-question me-2"></i>' . htmlspecialchars($quiz['title']) . '</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">' . (empty($quiz['description']) ? '<em>Sin descripción</em>' : htmlspecialchars($quiz['description'])) . '</p>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Creado por:</strong> ' . htmlspecialchars($quiz['creator_name']) . '
                        </div>
                        <div class="col-md-6">
                            <strong>Fecha de creación:</strong> ' . date('d/m/Y H:i', strtotime($quiz['created_at'])) . '
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-users me-2"></i>Este cuestionario ha sido respondido por <strong>' . $response_count . '</strong> estudiantes.
                    </div>
                </div>
            </div>';
        
        // Mostrar preguntas
        echo '<h5 class="mb-3">Preguntas (' . count($questions) . ')</h5>';
        
        foreach ($questions as $index => $question) {
            echo '<div class="card mb-3">
                    <div class="card-header bg-light">
                        <strong>Pregunta ' . ($index + 1) . ':</strong> ' . htmlspecialchars($question['question_text']) . '
                        <span class="badge bg-' . ($question['question_type'] === 'open' ? 'primary' : 'success') . ' float-end">
                            ' . ($question['question_type'] === 'open' ? 'Abierta' : 'Opciones múltiples') . '
                        </span>
                    </div>';
            
            if ($question['question_type'] === 'closed' && !empty($question['options'])) {
                echo '<div class="card-body">
                        <div class="options-list">';
                
                foreach ($question['options'] as $opt_index => $option) {
                    echo '<div class="form-check">
                            <input class="form-check-input" type="radio" disabled>
                            <label class="form-check-label">
                                ' . htmlspecialchars($option['option_text']) . '
                            </label>
                        </div>';
                }
                
                echo '</div>
                    </div>';
            }
            
            echo '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">El cuestionario solicitado no existe.</div>';
    }
}

// Función para obtener un cuestionario para que un estudiante lo responda
function getQuizForStudent($conn) {
    // Verificar que se proporcionó un ID válido
    if (empty($_GET['quiz_id'])) {
        echo "ID de cuestionario no proporcionado";
        exit();
    }
    
    $quiz_id = intval($_GET['quiz_id']);
    $user_id = $_SESSION['user_id'];
    
    // Verificar que el estudiante no haya respondido ya
    if (hasUserAnsweredQuiz($conn, $user_id, $quiz_id)) {
        echo '<div class="alert alert-warning">Ya has respondido este cuestionario anteriormente.</div>';
        exit();
    }
    
    // Obtener información del cuestionario
    $query = "SELECT * FROM quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($quiz = $result->fetch_assoc()) {
        // Obtener preguntas del cuestionario
        $query = "SELECT * FROM questions 
                  WHERE quiz_id = ? 
                  ORDER BY position";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $questions_result = $stmt->get_result();
        
        echo '<h5 class="mb-3">' . htmlspecialchars($quiz['title']) . '</h5>';
        
        if (!empty($quiz['description'])) {
            echo '<p class="mb-4">' . htmlspecialchars($quiz['description']) . '</p>';
        }
        
        echo '<form id="answerForm">';
        echo '<input type="hidden" name="quiz_id" value="' . $quiz_id . '">';
        
        $question_number = 1;
        while ($question = $questions_result->fetch_assoc()) {
            echo '<div class="form-group mb-4">
                    <label class="form-label">
                        <strong>' . $question_number . '.</strong> ' . htmlspecialchars($question['question_text']) . ' <span class="text-danger">*</span>
                    </label>';
            
            if ($question['question_type'] === 'open') {
                // Pregunta abierta
                echo '<textarea class="form-control" name="answers[' . $question['question_id'] . '][text]" rows="3" required></textarea>';
            } else {
                // Pregunta cerrada
                $query = "SELECT * FROM question_options 
                          WHERE question_id = ?";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $options_result = $stmt->get_result();
                
                while ($option = $options_result->fetch_assoc()) {
                    echo '<div class="form-check">
                            <input class="form-check-input" type="radio" 
                                name="answers[' . $question['question_id'] . '][option_id]" 
                                id="option_' . $option['option_id'] . '" 
                                value="' . $option['option_id'] . '" required>
                            <label class="form-check-label" for="option_' . $option['option_id'] . '">
                                ' . htmlspecialchars($option['option_text']) . '
                            </label>
                          </div>';
                }
            }
            
            echo '</div>';
            $question_number++;
        }
        
        echo '</form>';
        // echo '<button id="submitAnswersBtn" class="btn btn-primary">Enviar respuestas</button>';
    } else {
        echo '<div class="alert alert-danger">No se encontró el cuestionario solicitado.</div>';
    }
}

// Función para procesar el envío de respuestas
function submitAnswers($conn) {
    // Verificar que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        return;
    }
    
    // Validar datos recibidos
    if (empty($_POST['quiz_id']) || empty($_POST['answers'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
        return;
    }
    
    $quiz_id = intval($_POST['quiz_id']);
    $user_id = $_SESSION['user_id'];
    $answers = $_POST['answers'];
    
    // Verificar que el estudiante no haya respondido ya
    if (hasUserAnsweredQuiz($conn, $user_id, $quiz_id)) {
        echo json_encode(['success' => false, 'message' => 'Ya has respondido este cuestionario anteriormente']);
        return;
    }
    
    try {
        // Iniciar transacción
        $conn->begin_transaction();
        
        // Guardar respuestas
        foreach ($answers as $question_id => $answer) {
            if (isset($answer['text'])) {
                // Respuesta a pregunta abierta
                $query = "INSERT INTO answers (question_id, user_id, answer_text, created_at) 
                          VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $answer_text = $answer['text'];
                $stmt->bind_param("iis", $question_id, $user_id, $answer_text);
                $stmt->execute();
            } elseif (isset($answer['option_id'])) {
                // Respuesta a pregunta cerrada
                $query = "INSERT INTO answers (question_id, user_id, option_id, created_at) 
                          VALUES (?, ?, ?, NOW())";
                $stmt = $conn->prepare($query);
                $option_id = intval($answer['option_id']);
                $stmt->bind_param("iii", $question_id, $user_id, $option_id);
                $stmt->execute();
            }
        }
        
        // Confirmar la transacción
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Respuestas guardadas correctamente']);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error al guardar las respuestas: ' . $e->getMessage()]);
    }
}

// Función para obtener estadísticas de un cuestionario
function getQuizStatistics($conn) {
    // Verificar que se proporcionó un ID válido
    if (empty($_GET['quiz_id'])) {
        echo '<div class="alert alert-danger">ID de cuestionario no proporcionado</div>';
        return;
    }
    
    $quiz_id = intval($_GET['quiz_id']);
    
    // Obtener información del cuestionario
    $query = "SELECT q.*, c.course_name, u.name as creator_name 
              FROM quizzes q
              JOIN courses c ON q.course_id = c.course_id
              JOIN users u ON q.created_by = u.id
              WHERE q.quiz_id = ?";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($quiz = $result->fetch_assoc()) {
        // Obtener número total de usuarios que han respondido
        $query = "SELECT COUNT(DISTINCT user_id) as total_respondents 
                  FROM answers a
                  JOIN questions q ON a.question_id = q.question_id
                  WHERE q.quiz_id = ?";
                  
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $respondents_result = $stmt->get_result();
        $respondents_data = $respondents_result->fetch_assoc();
        $total_respondents = $respondents_data['total_respondents'];
        
        echo '<div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">' . htmlspecialchars($quiz['title']) . ' - Estadísticas</h5>
                </div>
                <div class="card-body">
                    <p><strong>Curso:</strong> ' . htmlspecialchars($quiz['course_name']) . '</p>
                    <p><strong>Descripción:</strong> ' . htmlspecialchars($quiz['description']) . '</p>
                    <p><strong>Creado por:</strong> ' . htmlspecialchars($quiz['creator_name']) . '</p>
                    <p><strong>Fecha de creación:</strong> ' . date('d/m/Y H:i', strtotime($quiz['created_at'])) . '</p>
                    <p><strong>Total de estudiantes que han respondido:</strong> ' . $total_respondents . '</p>
                </div>
             </div>';
        
        // Obtener preguntas del cuestionario
        $query = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY position";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $questions_result = $stmt->get_result();
        
        $question_number = 1;
        while ($question = $questions_result->fetch_assoc()) {
            echo '<div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Pregunta ' . $question_number . ': ' . htmlspecialchars($question['question_text']) . '</h6>
                    </div>
                    <div class="card-body">';
            
            if ($question['question_type'] === 'open') {
                // Estadísticas para preguntas abiertas
                $query = "SELECT a.answer_text, u.name, a.created_at 
                          FROM answers a 
                          JOIN users u ON a.user_id = u.id 
                          WHERE a.question_id = ? 
                          ORDER BY a.created_at DESC";
                          
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $answers_result = $stmt->get_result();
                
                if ($answers_result->num_rows > 0) {
                    echo '<div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Estudiante</th>
                                        <th>Respuesta</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>';
                    
                    while ($answer = $answers_result->fetch_assoc()) {
                        echo '<tr>
                                <td>' . htmlspecialchars($answer['name']) . '</td>
                                <td>' . htmlspecialchars($answer['answer_text']) . '</td>
                                <td>' . date('d/m/Y H:i', strtotime($answer['created_at'])) . '</td>
                              </tr>';
                    }
                    
                    echo '    </tbody>
                            </table>
                          </div>';
                } else {
                    echo '<div class="alert alert-info">Aún no hay respuestas para esta pregunta.</div>';
                }
            } else {
                // Estadísticas para preguntas cerradas
                $query = "SELECT qo.option_text, COUNT(a.option_id) as count 
                          FROM question_options qo 
                          LEFT JOIN answers a ON qo.option_id = a.option_id 
                          WHERE qo.question_id = ? 
                          GROUP BY qo.option_id
                          ORDER BY count DESC";
                          
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $question['question_id']);
                $stmt->execute();
                $options_result = $stmt->get_result();
                
                if ($options_result->num_rows > 0) {
                    // Preparar datos para el gráfico
                    $labels = [];
                    $data = [];
                    $colors = [];
                    
                    // Colores para el gráfico
                    $chart_colors = [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ];
                    
                    $color_index = 0;
                    $total_responses = 0;
                    
                    // Primero contamos el total de respuestas
                    while ($option = $options_result->fetch_assoc()) {
                        $total_responses += $option['count'];
                    }
                    
                    // Reiniciamos el puntero del resultado
                    $options_result->data_seek(0);
                    
                    // Ahora construimos los arrays para el gráfico
                    echo '<div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Opción</th>
                                                <th>Respuestas</th>
                                                <th>Porcentaje</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                    
                    while ($option = $options_result->fetch_assoc()) {
                        $labels[] = $option['option_text'];
                        $data[] = $option['count'];
                        $colors[] = $chart_colors[$color_index % count($chart_colors)];
                        $color_index++;
                        
                        $percentage = ($total_responses > 0) ? round(($option['count'] / $total_responses) * 100, 1) : 0;
                        
                        echo '<tr>
                                <td>' . htmlspecialchars($option['option_text']) . '</td>
                                <td>' . $option['count'] . '</td>
                                <td>' . $percentage . '%</td>
                              </tr>';
                    }
                    
                    echo '        </tbody>
                                </table>
                            </div>
                        </div>';
                    
                    // Contenedor para el gráfico
                    echo '<div class="col-md-6">
                            <canvas id="chart_question_' . $question['question_id'] . '" width="400" height="300"></canvas>
                          </div>
                          <script>
                          document.addEventListener("DOMContentLoaded", function() {
                              var ctx = document.getElementById("chart_question_' . $question['question_id'] . '").getContext("2d");
                              var myChart = new Chart(ctx, {
                                  type: "pie",
                                  data: {
                                      labels: ' . json_encode($labels) . ',
                                      datasets: [{
                                          data: ' . json_encode($data) . ',
                                          backgroundColor: ' . json_encode($colors) . ',
                                          borderWidth: 1
                                      }]
                                  },
                                  options: {
                                      responsive: true,
                                      plugins: {
                                          legend: {
                                              position: "bottom"
                                          },
                                          tooltip: {
                                              callbacks: {
                                                  label: function(context) {
                                                      var label = context.label || "";
                                                      var value = context.raw || 0;
                                                      var percentage = Math.round((value / ' . $total_responses . ') * 100);
                                                      return label + ": " + value + " (" + percentage + "%)";
                                                  }
                                              }
                                          }
                                      }
                                  }
                              });
                          });
                          </script>';
                } else {
                    echo '<div class="alert alert-info">Aún no hay respuestas para esta pregunta.</div>';
                }
            }
            
            echo '</div></div>';
            $question_number++;
        }
        
        // Agregar inicialización de gráficos si es necesario
        echo '<script>
        function initializeCharts() {
            // Los gráficos ya se inicializan mediante callbacks en DOMContentLoaded
            // Esta función se mantiene por compatibilidad con el llamado desde JS
        }
        </script>';
    } else {
        echo '<div class="alert alert-danger">No se encontró el cuestionario solicitado.</div>';
    }
}