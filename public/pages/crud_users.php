<?php
// Inicia sesión y verifica si el usuario es admin
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos
include "../modelo/conexion.php"; 

// Obtener todos los usuarios
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/icono_fake.png" type="image/x-icon">
    <title>Administración de Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h1 class="text-center">Administración de Usuarios</h1>
    <!-- <a href="../auth/logout.php" class="btn btn-danger mb-3">Cerrar sesión</a> -->
    <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
    <a href="../crud_users/crear_usuario.php" class="btn btn-success mb-3">Agregar Usuario</a>

    <!-- Tabla principal -->
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Password</th>
                <th>Role</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['email'] ?></td>
                    <td>********</td> <!-- Contraseña oculta -->
                    <td><?= $row['role'] ?></td>
                    <td>
                        <!-- Botones de acciones -->
                        <a href="../crud_users/editar_usuario.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="../crud_users/eliminar_usuario.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
