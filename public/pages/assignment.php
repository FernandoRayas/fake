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

if ($_SESSION['user_role'] == "master" || $_SESSION['user_role'] == "admin") {
    $courseNameStmt = $conn->prepare("SELECT course_name FROM courses WHERE course_id = ? AND created_by = ?");
    $courseNameStmt->bind_param('ii', $_GET['cid'], $_SESSION['user_id']);
    $courseNameStmt->execute();
    $courseName = $courseNameStmt->get_result()->fetch_assoc()['course_name'];
} else if ($_SESSION['user_role'] == 'user') {
    $courseNameStmt = $conn->prepare("SELECT course_name FROM courses JOIN user_courses ON courses.course_id = user_courses.course WHERE user = ? AND course_id = ?");
    $courseNameStmt->bind_param('ii', $_SESSION['user_id'], $_GET['cid']);
    $courseNameStmt->execute();
    $courseName = $courseNameStmt->get_result()->fetch_assoc()['course_name'];
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
        <div class="row">
            <?php if ($_SESSION['user_role'] == 'user'): ?>
                <div class="col-12 col-sm-12 col-md-6 col-lg-9">
                    <h1><?php echo $assignment['assignment_name'] ?></h1>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-secondary">0/<?php echo $assignment['max_score'] ?></span>
                        <span class="text-secondary">
                            Fecha de Entrega:
                            <?php if ($assignmentDateString == $currentDateString): ?>
                                <?php echo "Hoy, $assignmentTimeString" ?>
                            <?php else: ?>
                                <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="border-bottom border-1 mb-3"></div>
                    <p>
                        <?php echo $assignment['assignment_description'] ?>
                    </p>
                </div>
                <div class="border rounded col-12 col-sm-12 col-md-6 col-lg-3 p-2">
                    <form class="container needs-validation" action="../assignments/submit_assignment.php" enctype="multipart/form-data" method="post" novalidate>
                        <div class="d-flex align-items-center justify-content-between">
                            <h5>Adjuntar Entrega</h5>
                        </div>
                        <div class="mb-3">
                            <input class="form-control" type="file" id="input-submission-file" multiple required>
                        </div>
                        <div class="invalid-feedback"></div>
                        <div class="valid-feedback"></div>
                        <input class="btn btn-primary mb-3 w-100" type="submit" value="Entregar">
                    </form>
                </div>
        </div>
    <?php elseif ($_SESSION['user_role'] == 'master' || $_SESSION['user_role'] == 'admin'): ?>
        <div class="col-12 col-sm-12 col-md-12 col-lg-12">
            <h1><?php echo $assignment['assignment_name'] ?></h1>
            <div class="d-flex align-items-center justify-content-between">
                <span class="text-secondary">0/<?php echo $assignment['max_score'] ?></span>
                <span class="text-secondary">
                    Fecha de Entrega:
                    <?php if ($assignmentDateString == $currentDateString): ?>
                        <?php echo "Hoy, $assignmentTimeString" ?>
                    <?php else: ?>
                        <?php echo "$assignmentDateString, $assignmentTimeString" ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="border-bottom border-1 mb-3"></div>
            <p>
                <?php echo $assignment['assignment_description'] ?>
            </p>
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