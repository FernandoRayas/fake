<?php
// Inicia sesión y configuración de errores
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Incluir el controlador de cuestionarios
include "../../quizzes/quiz_controller.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curso - Cuestionarios</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .quiz-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .stats-card {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .question-container {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .option-row {
            margin-bottom: 10px;
        }
        .action-buttons {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <!-- <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Cursos</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Cuestionarios</li>
                    </ol>
                </nav> -->
                
                <h2 class="mb-4">
                    <i class="fas fa-clipboard-question me-2"></i>Sistema de Cuestionarios
                    <?php if ($user_role == 'master' || $user_role == 'admin'): ?>
                        <button type="button" class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#createQuizModal">
                            <i class="fas fa-plus me-2"></i>Crear Cuestionario
                        </button>
                    <?php endif; ?>
                </h2>
                
                <hr>
                
                <!-- Mensaje de confirmación o error -->
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                <?php endif; ?>
                
                <!-- Contenido principal según el rol -->
                <?php if ($user_role == 'master' || $user_role == 'admin'): ?>
                    <!-- Vista para profesores -->
                    <div class="master-view">
                        <ul class="nav nav-tabs mb-4" id="masterTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="quizzes-tab" data-bs-toggle="tab" data-bs-target="#quizzes" type="button" role="tab" aria-controls="quizzes" aria-selected="true">
                                    <i class="fas fa-list me-2"></i>Mis Cuestionarios
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#statistics" type="button" role="tab" aria-controls="statistics" aria-selected="false">
                                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="masterTabsContent">
                            <!-- Listado de cuestionarios creados -->
                            <div class="tab-pane fade show active" id="quizzes" role="tabpanel" aria-labelledby="quizzes-tab">
                                <div class="row" id="quizList">
                                    <?php 
                                    $quizzes = getQuizzesBymaster($conn, $user_id, $course_id);
                                    if (count($quizzes) > 0):
                                        foreach ($quizzes as $quiz):
                                    ?>
                                    <div class="col-md-4">
                                        <div class="card quiz-card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?= htmlspecialchars($quiz['title']) ?></h5>
                                                <p class="card-text"><?= htmlspecialchars($quiz['description']) ?></p>
                                                <p class="card-text"><small class="text-muted">Creado: <?= date('d/m/Y', strtotime($quiz['created_at'])) ?></small></p>
                                                <hr>
                                                <div class="d-flex justify-content-between">
                                                    <button class="btn btn-sm btn-info" onclick="viewQuizDetails(<?= $quiz['quiz_id'] ?>)">
                                                        <i class="fas fa-eye me-1"></i>Ver
                                                    </button>
                                                    <!-- <button class="btn btn-sm btn-success" onclick="editQuiz(<?= $quiz['quiz_id'] ?>)">
                                                        <i class="fas fa-edit me-1"></i>Editar
                                                    </button> -->
                                                    <button class="btn btn-sm btn-danger" onclick="confirmDeleteQuiz(<?= $quiz['quiz_id'] ?>)">
                                                        <i class="fas fa-trash me-1"></i>Eliminar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-footer bg-transparent">
                                                <small class="text-muted">
                                                    <i class="fas fa-users me-1"></i>
                                                    <?= getQuizResponseCount($conn, $quiz['quiz_id']) ?> respuestas
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>Aún no has creado cuestionarios para este curso.
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Estadísticas de respuestas -->
                            <div class="tab-pane fade" id="statistics" role="tabpanel" aria-labelledby="stats-tab">
                                <?php if (count($quizzes) > 0): ?>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quizSelector" class="form-label">Selecciona un cuestionario:</label>
                                            <select class="form-select" id="quizSelector" onchange="loadQuizStatistics()">
                                                <option value="">-- Seleccionar cuestionario --</option>
                                                <?php foreach ($quizzes as $quiz): ?>
                                                <option value="<?= $quiz['quiz_id'] ?>"><?= htmlspecialchars($quiz['title']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div id="statistics-container">
                                    <div class="alert alert-info">
                                        <i class="fas fa-chart-pie me-2"></i>Selecciona un cuestionario para ver sus estadísticas.
                                    </div>
                                </div>
                                <?php else: ?>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No hay cuestionarios disponibles para mostrar estadísticas.
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Vista para estudiantes -->
                    <div class="student-view">
                        <h4 class="mb-3">Cuestionarios disponibles</h4>
                        <div class="row" id="studentQuizList">
                            <?php 
                            $available_quizzes = getAvailableQuizzes($conn, $course_id);
                            if (count($available_quizzes) > 0):
                                foreach ($available_quizzes as $quiz):
                                    $already_answered = hasUserAnsweredQuiz($conn, $user_id, $quiz['quiz_id']);
                            ?>
                            <div class="col-md-4">
                                <div class="card quiz-card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($quiz['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($quiz['description']) ?></p>
                                        <hr>
                                        <?php if ($already_answered): ?>
                                        <button class="btn btn-secondary w-100" disabled>
                                            <i class="fas fa-check-circle me-2"></i>Completado
                                        </button>
                                        <?php else: ?>
                                        <button class="btn btn-primary w-100" onclick="startQuiz(<?= $quiz['quiz_id'] ?>)">
                                            <i class="fas fa-play me-2"></i>Responder cuestionario
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-footer bg-transparent">
                                        <small class="text-muted">Creado por: <?= getmasterName($conn, $quiz['created_by']) ?></small>
                                    </div>
                                </div>
                            </div>
                            <?php 
                                endforeach;
                            else:
                            ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>No hay cuestionarios disponibles en este curso.
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Modal para crear cuestionario -->
    <div class="modal fade" id="createQuizModal" tabindex="-1" aria-labelledby="createQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="createQuizModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Crear Nuevo Cuestionario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quizForm" action="../quizzes/quiz_controller.php" method="post">
                        <input type="hidden" name="action" value="create_quiz">
                        <input type="hidden" name="course_id" value="<?= $course_id ?>">
                        
                        <div class="mb-3">
                            <label for="quizTitle" class="form-label">Título del cuestionario *</label>
                            <input type="text" class="form-control" id="quizTitle" name="title" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="quizDescription" class="form-label">Descripción</label>
                            <textarea class="form-control" id="quizDescription" name="description" rows="3"></textarea>
                        </div>
                        
                        <hr>
                        <h5>Preguntas</h5>
                        
                        <div id="questions-container">
                            <!-- Aquí se cargarán dinámicamente las preguntas -->
                            <div class="question-container" data-question-index="0">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6>Pregunta #1</h6>
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-question" onclick="removeQuestion(this)" disabled>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Texto de la pregunta *</label>
                                    <input type="text" class="form-control" name="questions[0][text]" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipo de pregunta *</label>
                                    <select class="form-select question-type" name="questions[0][type]" onchange="toggleQuestionType(this)" required>
                                        <option value="open">Abierta (respuesta de texto)</option>
                                        <option value="closed">Cerrada (opciones múltiples)</option>
                                    </select>
                                </div>
                                
                                <div class="options-container" style="display: none;">
                                    <label class="form-label">Opciones de respuesta</label>
                                    <div class="options-list">
                                        <div class="option-row d-flex">
                                            <input type="text" class="form-control me-2" name="questions[0][options][]" placeholder="Opción">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option" onclick="removeOption(this)" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                        <div class="option-row d-flex">
                                            <input type="text" class="form-control me-2" name="questions[0][options][]" placeholder="Opción">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-option" onclick="removeOption(this)" disabled>
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addOption(this)">
                                        <i class="fas fa-plus me-1"></i>Añadir opción
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <button type="button" class="btn btn-outline-primary" onclick="addQuestion()">
                                <i class="fas fa-plus me-1"></i>Añadir pregunta
                            </button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="submitQuizForm()">Guardar cuestionario</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para responder cuestionario (estudiantes) -->
    <div class="modal fade" id="answerQuizModal" tabindex="-1" aria-labelledby="answerQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="answerQuizModalLabel">Responder Cuestionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="answerQuizContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="submitAnswersBtn">Enviar respuestas</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal para ver detalles del cuestionario (profesor) -->
    <div class="modal fade" id="quizDetailsModal" tabindex="-1" aria-labelledby="quizDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="quizDetailsModalLabel">Detalles del Cuestionario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="quizDetailsContent">
                    <!-- Contenido cargado dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js para las estadísticas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>
</html>