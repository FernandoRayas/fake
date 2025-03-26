<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/loginform.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .dashboard-container {
            max-width: 600px;
            margin: auto;
            margin-top: 100px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Fake</a>
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

    <div class="container dashboard-container">
        <div class="card shadow-lg">
            <div class="card-body text-center">
                <h2 class="card-title">Bienvenido, <?php echo $_SESSION['user_name']; ?> </h2>
                <p class="card-text">Selecciona una opción:</p>
                <div class="d-grid gap-2 col-8 mx-auto">
                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <a href="crud_users.php" class="btn btn-primary btn-lg"><i class="fas fa-users"></i> Gestión de Usuarios</a>
                    <?php endif; ?>
                    <a href="chat.php" class="btn btn-success btn-lg"><i class="fas fa-comments"></i> Chat en Tiempo Real</a>
                    <a href="home.php" class="btn btn-warning btn-lg"><i class="fas fa-sign-out-alt"></i> Tienda Online</a>
                    <?php if ($_SESSION['user_role'] == 'master' || $_SESSION['user_role'] == 'user'): ?>
                        <a href="courses.php" class="btn btn-info btn-lg">Cursos</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>