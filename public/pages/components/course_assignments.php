<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../../modelo/conexion.php";

$timezone = new DateTimeZone('America/Monterrey');
$currentDate = new DateTime('now', $timezone);
$currentDateString = $currentDate->format('d/m/Y');

$topicSql = "SELECT topics.topic_id, topics.topic_name, topics.topic_description FROM topics WHERE course = ?";
$topicStmt = $conn->prepare($topicSql);
$topicStmt->bind_param('i', $_GET['cid']);
$topicStmt->execute();
$topicResult = $topicStmt->get_result();
$topics = [];
while ($row = $topicResult->fetch_assoc()) {
    $topics[] = $row;
}
$topicStmt->close();

$assignmentSql = "SELECT assignments.* FROM assignments JOIN topics ON assignments.topic = topics.topic_id JOIN courses ON topics.course = courses.course_id WHERE courses.course_id = ?";
$assignmentStmt = $conn->prepare($assignmentSql);
$assignmentStmt->bind_param('i', $_GET['cid']);
$assignmentStmt->execute();
$assignmentResult = $assignmentStmt->get_result();
$assignments = [];
while ($row = $assignmentResult->fetch_assoc()) {
    $assignments[] = $row;
}
$assignmentStmt->close();

$files = [];
foreach ($assignments as $assignment) {
    $assignmentId = $assignment['assignment_id'];

    $assignmentFilesSql = "SELECT files.*, assignment FROM files JOIN assignments_files ON files.file_id = assignments_files.file JOIN assignments ON assignments_files.assignment = assignments.assignment_id WHERE assignment_id = ?";
    $assignmentFilesStmt = $conn->prepare($assignmentFilesSql);
    $assignmentFilesStmt->bind_param('i', $assignmentId);
    $assignmentFilesStmt->execute();
    $assignmentFilesResult = $assignmentFilesStmt->get_result();
    $files = [];
    while ($row = $assignmentFilesResult->fetch_assoc()) {
        $files[] = $row;
    }
}
?>

<div class="row d-flex align-items-center my-2 border rounded py-2">
    <h2 class="col-12 col-sm-12 col-md-8 col-lg-9 col-xl-10 col-xxl-10">Trabajo de Curso</h2>
    <?php if ($_SESSION['user_role'] == 'master'): ?>
        <div id="dropdown" class="dropdown col-3 col-sm-4 col-md-4 col-lg-3 col-xl-2 col-xxl-2">
            <button class="btn btn-secondary dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-clipboard-plus-fill" viewBox="0 0 16 16">
                    <path d="M6.5 0A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0zm3 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5z" />
                    <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1A2.5 2.5 0 0 1 9.5 5h-3A2.5 2.5 0 0 1 4 2.5zm4.5 6V9H10a.5.5 0 0 1 0 1H8.5v1.5a.5.5 0 0 1-1 0V10H6a.5.5 0 0 1 0-1h1.5V7.5a.5.5 0 0 1 1 0" />
                </svg>
                <span class="ms-3">Crear</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <button id="open-assignment-modal-button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#create-assignment-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-check" viewBox="0 0 16 16">
                            <path d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                            <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1" />
                        </svg>
                        <span class="ms-3">Tarea</span>
                    </button>
                </li>
                <li>
                    <button id="open-topic-modal-button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#create-topic-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                        </svg>
                        <span class="ms-3">Tema</span>
                    </button>
                </li>
            </ul>
        </div>
        <!-- Modal Crear Tarea -->
        <div class="modal fade" id="create-assignment-modal" tabindex="-1" aria-labelledby="create-assignment-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="create-assignment-modal">Crear Tarea</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" action="../assignments/create_assignment.php" method="post" enctype="multipart/form-data" novalidate>
                            <div class="mb-3">
                                <label for="assignment-name" class="form-label">Titulo de la Tarea *:</label>
                                <input type="text" class="form-control" id="assignment-name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-description" class="form-label">Descripcion de la Tarea:</label>
                                <textarea id="assignment-description" class="form-control" rows="3" style="resize: none;"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-max-score" class="form-label">Puntuación Máxima *:</label>
                                <input type="number" id="assignment-max-score" class="form-control" min=0 max=100 required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-topic" class="form-label">Tema *:</label>
                                <select class="form-select" aria-label="Default select example" id="assignment-topic" required>
                                    <option selected value="">Selecciona un Tema</option>
                                    <?php foreach ($topics as $topic): ?>
                                        <option value="<?= $topic['topic_id'] ?>"><?= $topic['topic_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3 row">
                                <div class="col">
                                    <label for="assignment-submission-date" class="form-label">Fecha de Entrega *:</label>
                                    <input type="date" id="assignment-submission-date" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col">
                                    <label for="assignment-submission-time" class="form-label">Hora de Entrega:</label>
                                    <input type="time" id="assignment-submission-time" class="form-control" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-files" class="form-label">Adjuntar archivos:</label>
                                <input class="form-control" name="assignment-files" type="file" id="assignment-files" accept=".docx,.pdf,.xlsx,.csv,.pptx,.jpg,.jpeg,.png,.mp3,.wav,.mp4,.txt,.rft,.zip" multiple>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                        <div class="border border-1 mb-1"></div>
                        <span>Los campos marcados con * son obligatorios</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="create-assignment-button" disabled class="btn btn-primary">Crear Tarea</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Crear Tema -->
        <div class="modal fade" id="create-topic-modal" tabindex="-1" aria-labelledby="create-topic-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="create-topic-modal">Crear Tema</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" action="../courses/add_course.php" method="post" novalidate>
                            <div class="mb-3">
                                <label for="topic-name" class="form-label">Nombre *:</label>
                                <input type="text" class="form-control" id="topic-name" placeholder="Ciencias 3" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="topic-description" class="form-label">Descripcion:</label>
                                <input type="text" class="form-control" id="topic-description" placeholder="Clase de ciencias naturales" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                        <div class="border border-1 mb-1"></div>
                        <span>Los campos marcados con * son obligatorios</span>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="create-topic-button" disabled class="btn btn-primary">Crear Tema</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php foreach ($topics as $row): ?>
    <h4 class="mt-4 border-bottom border-1"><?= $row['topic_name'] ?></h4>
    <?php if (count($assignments) > 0): ?>
        <div class="accordion accordion-flush" id="assignments-accordion<?= $row['topic_id'] ?>">
            <?php foreach ($assignments as $assignment): ?>
                <?php
                $assignmentDate = new DateTime(($assignment['submission_date']));
                $assignmentDateString = $assignmentDate->format('d/m/Y');
                $assignmentTime = new DateTime($assignment['submission_time']);
                $assignmentTimeString = $assignmentTime->format('H:i');
                ?>
                <?php if ($assignment['topic'] == $row['topic_id']): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?= $assignment['assignment_id'] ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $assignment['assignment_id'] ?>" aria-expanded="false" aria-controls="collapse<?= $assignment['assignment_id'] ?>">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <span><?= $assignment['assignment_name'] ?></span>
                                    <span class="text-secondary me-2">
                                        <?php if ($assignmentDateString == $currentDateString): ?>
                                            <?php echo "Hoy, $assignmentTimeString" ?>
                                        <?php else: ?>
                                            <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?= $assignment['assignment_id'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $assignment['assignment_id'] ?>" data-bs-parent="#assignments-accordion<?= $row['topic_id'] ?>">
                            <div class="accordion-body">
                                <?php if ($assignment['assignment_description'] == ""): ?>
                                    <p>No se proporciono una descripción de la tarea</p>
                                <?php else: ?>
                                    <p><?= $assignment['assignment_description'] ?></p>
                                <?php endif; ?>
                                <div class="row">
                                    <?php foreach ($files as $file): ?>
                                        <?php if ($file['assignment'] == $assignment['assignment_id']): ?>
                                            <div class="col-12 col-sm-12 col-md-6">
                                                <a class="text-secondary link-secondary link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="<?= htmlspecialchars("../files/" . $file['file_path']) ?>" target="_blank"><?= $file['file_name']  ?></a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="container">
                                    <a class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" href="assignment.php?aid=<?= $assignment['assignment_id'] ?>&cid=<?php echo $_GET['cid'] ?>">Ver detalles de la asignación</a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <h5>Aún no hay tareas</h5>
    <?php endif; ?>
<?php endforeach; ?>