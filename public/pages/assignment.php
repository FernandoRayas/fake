<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

$timezone = new DateTimeZone('America/Monterrey');
$currentDate = new DateTime('now', $timezone);
$currentDateString = $currentDate->format('d/m/Y');

$assignmentSql = "SELECT * FROM assignments WHERE assignment_id = ?";
$assignmentStmt = $conn->prepare($assignmentSql);
$assignmentStmt->bind_param('i', $_GET['aid']);
$assignmentStmt->execute();
$assignmentResult = $assignmentStmt->get_result();

$assignment = $assignmentResult->fetch_assoc();
$assignmentDate = new DateTime(($assignment['submission_date']));
$assignmentDateString = $assignmentDate->format('d/m/Y');

$assignmentTime = new DateTime($assignment['submission_time']);
$assignmentTimeString = $assignmentTime->format('H:i');

$filesSql = "SELECT files.*, assignment FROM files JOIN assignments_files ON files.file_id = assignments_files.file WHERE assignments_files.assignment = ?";
$filesStmt = $conn->prepare($filesSql);
$filesStmt->bind_param('i', $_GET['aid']);
$filesStmt->execute();
$filesResult = $filesStmt->get_result();
$files = [];
while ($row = $filesResult->fetch_assoc()) {
    $files[] = $row;
}

$isAlreadySubmitted = false;
$submissions = [];

if ($_SESSION['user_role'] == "master" || $_SESSION['user_role'] == "admin") {
    $courseNameStmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ? AND created_by = ?");
    $courseNameStmt->bind_param('ii', $_GET['cid'], $_SESSION['user_id']);
    $courseNameStmt->execute();
    $courseName = $courseNameStmt->get_result()->fetch_assoc()['course_name'];

    $submittedAssignmentsSql = "SELECT users.name, users.id, file_name, file_path, submitted_at, status, score, student, max_score FROM users 
    JOIN submissions_assignments_files ON users.id = submissions_assignments_files.student 
    JOIN assignments ON assignments.assignment_id = submissions_assignments_files.assignment 
    JOIN files ON files.file_id = submissions_assignments_files.file WHERE assignment = ? AND status != 'SCORED'";

    $submittedAssignmentsStmt = $conn->prepare($submittedAssignmentsSql);
    $submittedAssignmentsStmt->bind_param('i', $_GET['aid']);
    $submittedAssignmentsStmt->execute();
    $submittedAssignmentsResult = $submittedAssignmentsStmt->get_result();
    if ($submittedAssignmentsResult->num_rows > 0) {
        while ($row = $submittedAssignmentsResult->fetch_assoc()) {
            $submissions[] = $row;
        }
    }
} else if ($_SESSION['user_role'] == 'user') {
    $courseNameStmt = $conn->prepare("SELECT course_name FROM courses JOIN user_courses ON courses.course_id = user_courses.course WHERE user = ? AND course_id = ?");
    $courseNameStmt->bind_param('ii', $_SESSION['user_id'], $_GET['cid']);
    $courseNameStmt->execute();
    $courseName = $courseNameStmt->get_result()->fetch_assoc()['course_name'];

    $submittedAssignmentSql = "SELECT * FROM submissions_assignments_files WHERE assignment = ? AND student = ?";
    $submittedAssignmentStmt = $conn->prepare($submittedAssignmentSql);
    $submittedAssignmentStmt->bind_param('ii', $_GET['aid'], $_SESSION['user_id']);
    $submittedAssignmentStmt->execute();
    $submittedAssignmentResult = $submittedAssignmentStmt->get_result();

    if ($submittedAssignmentResult->num_rows > 0) {
        $isAlreadySubmitted = true;
        $submission = $submittedAssignmentResult->fetch_assoc();
    } else {
        $isAlreadySubmitted = false;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Tarea</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Fake</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../auth/logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white p-2 rounded">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="courses.php">Cursos</a></li>
                <li class="breadcrumb-item"><a href="course.php?cid=<?php echo $_GET['cid'] ?>"><?php echo $courseName ?></a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $assignment['assignment_name'] ?></li>
            </ol>
        </nav>
    </div>
    <div class="container">
        <div id="alert-container"></div>
        <div class="row">
            <?php if ($_SESSION['user_role'] == 'user'): ?>
                <div class="col-12 col-sm-12 col-md-6 col-lg-9">
                    <div class="d-flex align-items-center justify-content-between">
                        <h1><?php echo $assignment['assignment_name'] ?></h1>
                        <?php if ($isAlreadySubmitted): ?>
                            <span class="text-success">
                                <?php if ($submission['status'] == 'SUBMITTED'): ?>
                                    <strong><?= "Entregado" ?></strong>
                                <?php elseif ($submission['status'] == 'SCORED'): ?>
                                    <strong><?= "Calificado" ?></strong>
                                <?php elseif ($submission['status'] == 'SUBMITTED_LATE'): ?>
                                    <strong><?= "Entregado Tarde" ?></strong>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <strong>Pendiente</strong>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <?php if ($isAlreadySubmitted): ?>
                            <?php if ($assignmentDateString == $currentDateString): ?>
                                <span class="text-success">
                                    <?php echo ($submission['score'] == -1) ? '-' : $submission['score']; ?>/<?php echo $assignment['max_score'] ?>
                                </span>
                                <span class="text-success">
                                    Fecha de Entrega:
                                    <?php echo "Hoy, $assignmentTimeString" ?>
                                </span>
                            <?php elseif ($assignmentDateString < $currentDateString): ?>
                                <span class="text-secondary">
                                    <?php echo ($submission['score'] == -1) ? '-' : $submission['score']; ?>/<?php echo $assignment['max_score'] ?>

                                </span>
                                <span class="text-secondary">
                                    Fecha de Entrega:
                                    <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                                </span>
                            <?php else: ?>
                                <span class="text-secondary">
                                    <?php echo ($submission['score'] == -1) ? '-' : $submission['score']; ?>/<?php echo $assignment['max_score'] ?>
                                </span>
                                <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($assignmentDateString == $currentDateString): ?>
                                <span class="text-secondary">
                                    <?php echo ($submission['score'] == -1) ? '-' : $submission['score']; ?>/<?php echo $assignment['max_score'] ?>
                                </span>
                                <span class="text-secondary">
                                    Fecha de Entrega:
                                    <?php echo "Hoy, $assignmentTimeString" ?>
                                </span>
                            <?php elseif ($assignmentDateString < $currentDateString): ?>
                                <span class="text-danger">-/<?php echo $assignment['max_score'] ?></span>
                                <span class="text-danger">
                                    Fecha de Entrega:
                                    <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                                </span>
                            <?php else: ?>
                                <span class="text-secondary">
                                    -/<?php echo $assignment['max_score'] ?>
                                </span>
                                <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="border-bottom border-1 mb-3"></div>
                    <p>
                        <?php if ($assignment['assignment_description'] == ""): ?>
                            <?php echo "Descripción de la tarea no disponible"; ?>
                        <?php else: ?>
                            <?php echo $assignment['assignment_description'] ?>
                        <?php endif; ?>
                    </p>

                    <?php if (count($files) > 0): ?>
                        <h3>Archivos Adjuntos a la Tarea</h3>
                        <div class="border-bottom border-1 mb-3"></div>
                        <?php foreach ($files as $file): ?>
                            <?php if ($file['assignment'] == $assignment['assignment_id']): ?>
                                <div class="mb-4">
                                    <a class="text-secondary link-secondary link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="<?= htmlspecialchars("../files/" . $file['file_path']) ?>" target="_blank"><?= $file['file_name']  ?></a>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="col-12 col-sm-12 col-md-6 col-lg-3">
                    <form class="container border rounded needs-validation p-3" action="../assignments/submit_assignment.php" enctype="multipart/form-data" method="post" novalidate>
                        <div class="d-flex align-items-center justify-content-between">
                            <h5>Adjuntar Entrega</h5>
                        </div>
                        <div class="mb-3">
                            <label for="submission-file" class="form-label">Adjuntar archivos:</label>
                            <?php if ($isAlreadySubmitted): ?>
                                <input class="form-control" type="file" id="submission-file" accept=".docx,.pdf,.xlsx,.csv,.pptx,.jpg,.jpeg,.png,.mp3,.wav,.mp4,.txt,.rft,.zip" disabled multiple required>
                            <?php else: ?>
                                <input class="form-control" type="file" id="submission-file" accept=".docx,.pdf,.xlsx,.csv,.pptx,.jpg,.jpeg,.png,.mp3,.wav,.mp4,.txt,.rft,.zip" multiple required>
                            <?php endif; ?>
                            <div class="invalid-feedback"></div>
                        </div>
                        <input class="btn btn-primary mb-3 w-100" id="submit" disabled type="submit" value="Entregar">
                        <div class="border-top border-1 mb-3"></div>
                        <a href="chat.php" class="btn btn-secondary w-100">Ponerse en contacto</a>
                    </form>
                </div>
        </div>

        <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
            <symbol id="check-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
            </symbol>
            <symbol id="exclamation-triangle-fill" viewBox="0 0 16 16">
                <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
            </symbol>
        </svg>

    <?php elseif ($_SESSION['user_role'] == 'master' || $_SESSION['user_role'] == 'admin'): ?>
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <h1><?php echo $assignment['assignment_name'] ?></h1>
            <div class="border-bottom border-1 mb-3"></div>
            <p>
                <?php if ($assignment['assignment_description'] == ""): ?>
                    <?= "Descripción de la tarea no disponible" ?>
                <?php else: ?>
                    <?= $assignment['assignment_description'] ?>
                <?php endif; ?>
            </p>
            <?php if (count($files) > 0): ?>
                <h3>Archivos Adjuntos a la Tarea</h3>
                <div class="border-bottom border-1 mb-3"></div>
                <div class="row">
                    <?php foreach ($files as $file): ?>
                        <?php if ($file['assignment'] == $assignment['assignment_id']): ?>
                            <div class="mb-4 text-truncate">
                                <a class="text-secondary link-secondary link-underline link-underline-opacity-0 link-underline-opacity-100-hover" href="<?= htmlspecialchars("../files/" . $file['file_path']) ?>" target="_blank"><?= $file['file_name']  ?></a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($submissions) > 0): ?>
                <h3>Entregas</h3>
                <div class="border-bottom border-1"></div>
                <div class="table-responsive">
                    <table class="table  table-stripped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Estudiante</th>
                                <th scope="col">Archivos Adjuntos</th>
                                <th scope="col">Fecha y hora</th>
                                <th scope="col">Puntuación</th>
                                <th scope="col">Máxima Puntuación</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $groupedSubmissions = [];
                            foreach ($submissions as $submission) {
                                $studentId = $submission['student'];
                                if (!isset($groupedSubmissions[$studentId])) {
                                    $groupedSubmissions[$studentId] = [
                                        'id' => $submission['id'],
                                        'student_name' => $submission['name'],
                                        'submitted_at' => $submission['submitted_at'],
                                        'score' => $submission['score'],
                                        'max_score' => $submission['max_score'],
                                        'status' => $submission['status'],
                                        'files' => [],
                                    ];
                                }
                                $groupedSubmissions[$studentId]['files'][] = [
                                    'file_path' => $submission['file_path'],
                                    'file_name' => $submission['file_name'],
                                ];
                            }

                            $rowNumber = 1;
                            foreach ($groupedSubmissions as $studentId => $submissionData):
                            ?>
                                <tr>
                                    <td><?= $submissionData['id'] ?></td>
                                    <td><?= $submissionData['student_name'] ?></td>
                                    <td>
                                        <?php foreach ($submissionData['files'] as $file): ?>
                                            <a href="../files/<?= $file['file_path'] ?>" target="_blank"><?= $file['file_name'] ?></a><br>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?= $submissionData['submitted_at'] ?></td>
                                    <td>
                                        <input type="number" class="form-control input-score" value="<?= $submissionData['score'] ?>">
                                        <div class="invalid-feedback"></div>
                                    </td>
                                    <td>
                                        <?= $submissionData['max_score'] ?>
                                    </td>
                                    <td><?= $submissionData['status'] ?></td>
                                    <td>
                                        <button class="btn btn-primary score-btn" disabled type="button">Calificar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                    </table>
                </div>
            <?php else: ?>
                <h3>Aún no hay entregas pendientes de calificar</h3>
                <div class="border-bottom border-1"></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php if ($_SESSION['user_role'] == 'user'): ?>
    <script src="../js/assignment.js"></script>
<?php elseif ($_SESSION['user_role'] == 'master' || $_SESSION['user_role'] == 'admin'): ?>
    <script src="../js/assignment_teacher.js"></script>
<?php endif; ?>

</body>

</html>