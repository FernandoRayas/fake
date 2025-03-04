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
    <link rel="stylesheet" href="../styles/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Bienvenido, <?php echo $_SESSION['user_name']; ?> </h2>
        <p>Selecciona una opción:</p>
        <a href="crud_users.php" class="btn btn-primary">Gestión de Usuarios</a>
        <a href="chat.php" class="btn btn-success">Chat en Tiempo Real</a>
        <a href="../auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>
</body>
</html>
