<?php
// File: course_questions.php - Interfaz del foro de preguntas
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Conexión a la base de datos
include "../../modelo/conexion.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Obtener información del usuario actual
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];
$course_id = isset($_GET['cid']) ? intval($_GET['cid']) : 0;

// Verificar que el course_id sea válido
if ($course_id <= 0) {
    echo "<div class='alert alert-danger'>ID de curso inválido</div>";
    exit();
}

// Incluir el controlador de preguntas
include "../../courses/questions_courses.php";

// Obtener el nombre del curso
$course_query = "SELECT course_name FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course_result = $stmt->get_result();
$course_name = ($course_result->num_rows > 0) ? $course_result->fetch_assoc()['course_name'] : "Curso desconocido";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro de Preguntas - <?php echo htmlspecialchars($course_name); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .question-card {
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        .answer-card {
            margin-left: 40px;
            margin-bottom: 10px;
            border-left: 4px solid #28a745;
        }
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .timestamp {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .form-container {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1>Foro de Preguntas - <?php echo htmlspecialchars($course_name); ?></h1>
                <p class="lead">Aquí puedes hacer preguntas sobre el curso y ver las respuestas</p>
            </div>
        </div>

        <!-- Formulario para hacer una pregunta -->
        <?php if ($user_role === 'user'): ?>
            <!-- Formulario para hacer una pregunta -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="form-container">
                        <h3>Hacer una pregunta</h3>
                        <form id="questionForm" method="post" action="../../courses/questions_courses.php">
                            <input type="hidden" name="action" value="add_question">
                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                            
                            <div class="mb-3">
                                <label for="question_title" class="form-label">Título</label>
                                <input type="text" class="form-control" id="question_title" name="question_title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="question_content" class="form-label">Pregunta</label>
                                <textarea class="form-control" id="question_content" name="question_content" rows="3" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Publicar Pregunta</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Lista de preguntas -->
        <div class="row">
            <div class="col-12">
                <h2>Preguntas del curso</h2>
                <div id="questionsList">
                    <?php
                    // Obtener todas las preguntas para este curso
                    $query = "SELECT q.*, u.name 
                                FROM courses_questions q 
                                JOIN users u ON q.user_id = u.id 
                                WHERE q.course_id = ? 
                                ORDER BY q.created_at DESC";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $course_id);
                    $stmt->execute();
                    $questions_result = $stmt->get_result();
                    
                    if ($questions_result->num_rows > 0) {
                        while ($question = $questions_result->fetch_assoc()) {
                            ?>
                            <div class="card question-card mb-4">
                                <div class="card-header bg-light question-header">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($question['question_title']); ?></h5>
                                    <small class="timestamp">
                                        Por: <?php echo htmlspecialchars($question['name']); ?> | 
                                        <?php echo date('d/m/Y H:i', strtotime($question['created_at'])); ?>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($question['question_content'])); ?></p>
                                    
                                    <!-- Respuestas a esta pregunta -->
                                    <div class="answers-container mt-4">
                                        <h6>Respuestas:</h6>
                                        <?php
                                        // Obtener respuestas para esta pregunta
                                        $answers_query = "SELECT *
                                                         FROM courses_answers a 
                                                         JOIN users u ON a.user_id = u.id 
                                                         WHERE a.question_id = ? 
                                                         ORDER BY a.created_at ASC";
                                        
                                        $answers_stmt = $conn->prepare($answers_query);
                                        $answers_stmt->bind_param("i", $question['question_id']);
                                        $answers_stmt->execute();
                                        $answers_result = $answers_stmt->get_result();
                                        
                                        if ($answers_result->num_rows > 0) {
                                            while ($answer = $answers_result->fetch_assoc()) {
                                                ?>
                                                <div class="card answer-card">
                                                    <div class="card-body">
                                                        <p class="card-text"><?php echo nl2br(htmlspecialchars($answer['answer_content'])); ?></p>
                                                        <div class="d-flex justify-content-end">
                                                            <small class="timestamp">
                                                                Por: <?php echo htmlspecialchars($answer['name']); ?> | 
                                                                <?php echo date('d/m/Y H:i', strtotime($answer['created_at'])); ?>
                                                                <?php if ($answer['is_teacher_answer']): ?>
                                                                    <span class="badge bg-success ms-2">Profesor</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        } else {
                                            echo '<p class="text-muted">No hay respuestas todavía</p>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <!-- Formulario para responder -->
                                    <div class="mt-3">
                                        <form class="answer-form" method="post" action="../../courses/questions_courses.php">
                                            <input type="hidden" name="action" value="add_answer">
                                            <input type="hidden" name="question_id" value="<?php echo $question['question_id']; ?>">
                                            <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                            
                                            <div class="mb-3">
                                                <textarea class="form-control" name="answer_content" rows="2" placeholder="Escribe tu respuesta..." required></textarea>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-sm btn-success">Responder</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    } else {
                        echo '<div class="alert alert-info">Todavía no hay preguntas en este curso.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejar el envío del formulario de preguntas con AJAX
            $("#questionForm").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert("Pregunta publicada correctamente.");
                            location.reload(); // Recargar para ver la nueva pregunta
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error al procesar la solicitud.");
                    }
                });
            });

            // Manejar el envío de los formularios de respuesta con AJAX
            $(".answer-form").submit(function(e) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: $(this).serialize(),
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            alert("Respuesta publicada correctamente.");
                            location.reload(); // Recargar para ver la nueva respuesta
                        } else {
                            alert("Error: " + response.message);
                        }
                    },
                    error: function() {
                        alert("Error al procesar la solicitud.");
                    }
                });
            });
        });
    </script>
</body>
</html>