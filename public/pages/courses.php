<?php

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SESSION['user_role'] === 'master' || $_SESSION['user_role'] === 'admin') {
    $sql = "SELECT * FROM courses WHERE created_by = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($_SESSION['user_role'] === 'user') {
    $sql = "SELECT courses.* FROM courses JOIN user_courses ON courses.course_id = user_courses.course WHERE user_courses.user = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .fab-container {
            position: fixed;
            right: 1rem;
            bottom: 1rem;

            & .fab {
                width: 3rem;
                height: 3rem;
            }
        }
        .card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border: none;
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
  border-radius: 12px;
}

.card:hover {
  transform: scale(1.05); /* se hace un 5% más grande */
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
}
    </style>
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
                <li class="breadcrumb-item active" aria-current="page">Cursos</li>
            </ol>
        </nav>
    </div>

    <!-- Header Container -->
    <div class="container mt-3">
        <div class="row">
            <div class="col-sm-12 col-md-6 col-lg-9">
                <h1>Mis Cursos</h1>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-3">
                <label for="find-course" class="form-label">Buscar Curso</label>
                <input type="text" class="form-control" id="find-course">
            </div>
        </div>
    </div>


    <!-- Alert Container -->
    <div class="container alert-container">
        <div id="alert" class="alert alert-dismissible fade show align-items-center" style="display: none;" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
            </svg>
            <div class="ms-3">
                <span id="alert-message"></span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>

    <!-- My Courses container -->
    <div class="container mt-3">
        <div class="row row-cols-auto courses-container">
            <?php if ($result->num_rows == 0): ?>
                <h5>No se encontraron cursos disponibles</h4>
                <?php else: ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="col">
                            <div class="card mb-3" style="width: 18rem;">
                                <div class="card-body">
                                    <h5 class="card-title row">
                                        <a href="course.php?cid=<?= $row['course_id'] ?>" class="link-secondary link-underline-secondary link-underline-opacity-0 link-underline-opacity-100-hover text-truncate">
                                            <?= $row['course_name'] ?>
                                        </a>
                                    </h5>
                                    <p class="card-text"><?= $row['course_description'] ?></p>
                                </div>
                                <div class="card-footer"></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
        </div>
    <?php endif; ?>
    </div>

    <!-- FAB Container -->
    <div class="fab-container">
        <?php if ($_SESSION['user_role'] == 'master'): ?>
            <button type="button" class="fab rounded-circle btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-course-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
                </svg>
            </button>
        <?php elseif ($_SESSION['user_role'] == 'user'): ?>
            <button type="button" class="fab rounded-circle btn btn-primary" data-bs-toggle="modal" data-bs-target="#enroll-course-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
                </svg>
            </button>
        <?php endif; ?>
    </div>

    <!-- Create Course Modal -->
    <?php if ($_SESSION['user_role'] == 'master'): ?>
        <div class="modal fade" id="create-course-modal" tabindex="-1" aria-labelledby="create-course-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="create-course-modal">Crear Curso</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" action="../courses/add_course.php" method="post" novalidate>
                            <div class="mb-3">
                                <label for="course-name" class="form-label">Nombre del Curso</label>
                                <input type="text" class="form-control" id="course-name" placeholder="Ciencias 3" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="course-description" class="form-label">Descripcion del Curso</label>
                                <input type="text" class="form-control" id="course-description" placeholder="Clase de ciencias naturales" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="create-course-btn" disabled class="btn btn-primary">Crear Curso</button>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($_SESSION['user_role'] == 'user'): ?>
        <div class="modal fade" id="enroll-course-modal" tabindex="-1" aria-labelledby="enroll-course-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="enroll-course-modal">Inscribirme al Curso</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form class="needs-validation" action="../courses/add_course.php" method="post" novalidate>
                            <div class="mb-3">
                                <label for="course-code" class="form-label">Código del Curso</label>
                                <input type="text" class="form-control" id="course-code" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Descartar</button>
                        <button type="button" id="enroll-course-btn" disabled class="btn btn-primary">Inscribirme al Curso</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/courses.js"></script>
    <script>
  document.addEventListener("DOMContentLoaded", function () {
    const footers = document.querySelectorAll(".card-footer");
    const colors = ["#FFD700", "#FF6F61", "#6A5ACD", "#20B2AA", "#FFA07A", "#87CEEB", "#98FB98", "#FFB6C1", "#FF6347", "#4682B4", "#DDA0DD", "#F08080", "#20B2AA", "#FF4500", "#32CD32", "#00CED1"];
    
    footers.forEach(footer => {
      const randomColor = colors[Math.floor(Math.random() * colors.length)];
      footer.style.backgroundColor = randomColor;
    });
  });
</script>
</body>

</html>