<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../../modelo/conexion.php";

$topicSql = "SELECT topics.topic_id, topics.topic_name, topics.topic_description FROM topics WHERE course = ?";
$topicStmt = $conn->prepare($topicSql);
$topicStmt->bind_param('i', $_GET['cid']);
$topicStmt->execute();
$topicResult = $topicStmt->get_result();
$topics = [];
while ($row = $topicResult->fetch_assoc()) {
    $topics[] = $row;
}

?>

<div class="row d-flex align-items-center my-2">
    <h3 class="col-9 col-sm-8 col-md-8 col-lg-9 col-xl-10 col-xxl-10">Trabajo de Curso</h3>
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
                    <button id="create-assignment-button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#create-assignment-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-check" viewBox="0 0 16 16">
                            <path d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0" />
                            <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1" />
                        </svg>
                        <span class="ms-3">Tarea</span>
                    </button>
                </li>
                <li>
                    <button id="create-topic-button" class="dropdown-item d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#create-topic-modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list-ul" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m-3 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2m0 4a1 1 0 1 0 0-2 1 1 0 0 0 0 2" />
                        </svg>
                        <span class="ms-3">Tema</span>
                    </button>
                </li>
            </ul>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="create-assignment-modal" tabindex="-1" aria-labelledby="create-assignment-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="create-assignment-modal">Crear Tarea</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" action="../courses/add_course.php" method="post" novalidate>
                            <div class="mb-3">
                                <label for="assignment-name" class="form-label">Titulo de la Tarea</label>
                                <input type="text" class="form-control" id="assignment-name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-description" class="form-label">Descripcion de la Tarea</label>
                                <textarea id="assignment-description" class="form-control" rows="3" style="resize: none;" required></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-max-score" class="form-label">Puntuación Máxima</label>
                                <input type="number" id="assignment-max-score" class="form-control" min=0 max=100>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-topic" class="form-label">Tema</label>
                                <select class="form-select" aria-label="Default select example" id="assignment-files">
                                    <option selected value="">Selecciona un Tema</option>
                                    <?php foreach ($topics as $topic): ?>
                                        <option value="<?= $topic['topic_id'] ?>"><?= $topic['topic_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="assignment-files" class="form-label">Adjuntar archivos</label>
                                <input class="form-control" type="file" id="assignment-files" multiple>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="create-assignment-button-modal" disabled class="btn btn-primary">Crear Tarea</button>
                    </div>
                </div>
            </div>
        </div>
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
                                <label for="topic-name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="topic-name" placeholder="Ciencias 3" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="topic-description" class="form-label">Descripcion</label>
                                <input type="text" class="form-control" id="topic-description" placeholder="Clase de ciencias naturales" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="create-topic-button-modal" disabled class="btn btn-primary">Crear Tema</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="container border-top border-1"></div>
<?php foreach ($topics as $row): ?>
    <h4 class="mt-3"><?= $row['topic_name'] ?></h4>
<?php endforeach; ?>