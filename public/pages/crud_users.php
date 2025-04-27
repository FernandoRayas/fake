<?php
// Inicia sesión y verifica si el usuario es admin
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: index.php");
    exit();
}

// Conexión a la base de datos
include "../modelo/conexion.php"; 

// Consulta base
$sql = "SELECT * FROM users";
$params = [];
$types = '';

// Si hay búsqueda, agregamos condiciones
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    $sql .= " WHERE id LIKE ? OR name LIKE ? OR email LIKE ? OR role LIKE ?";
    $searchParam = "%$search%";
    $params = array_fill(0, 4, $searchParam);
    $types = str_repeat('s', 4); // 4 strings
}

// Preparar la consulta
$stmt = $conn->prepare($sql);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
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
    <div class="container mt-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white p-2 rounded">
                    <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Administración de Usuarios</li>
                </ol>
            </nav>
        </div>
    <h1 class="text-center">Administración de Usuarios</h1>
    <!-- <a href="../auth/logout.php" class="btn btn-danger mb-3">Cerrar sesión</a> -->
    <a href="dashboard.php" class="btn btn-danger mb-3">Volver</a>
    <a href="../crud_users/crear_usuario.php" class="btn btn-success mb-3">Agregar Usuario</a>

    <div class="input-group mb-2">
        <form method="GET" action="" class="d-flex w-100">
            <input type="text" class="form-control" name="search" placeholder="Buscar usuario.." 
                value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            <button class="btn btn-outline-primary" type="submit">Buscar</button>
            <a href="?" class="btn btn-outline-secondary">Limpiar</a>
        </form>
    </div>

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
