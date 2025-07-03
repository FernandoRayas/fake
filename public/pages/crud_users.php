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
    <style>
        body {
            background-color: #f8f9fa;
        }
        .breadcrumb {
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            margin-top: 20px;
        }
        .table thead th {
            background-color: #343a40;
            color: white;
            font-weight: 500;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .search-container {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        h1 {
            color: #343a40;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
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
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Administración de Usuarios</h1>
        <div>
            <a href="dashboard.php" class="btn btn-outline-secondary">Volver</a>
            <a href="../crud_users/crear_usuario.php" class="btn btn-primary">Agregar Usuario</a>
        </div>
    </div>

    <div class="search-container">
        <form method="GET" action="" class="row g-2">
            <div class="col-md-8">
                <input type="text" class="form-control" name="search" placeholder="Buscar por ID, nombre, email o rol..." 
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
            </div>
            <div class="col-md-4 d-flex">
                <button class="btn btn-primary me-2" type="submit">Buscar</button>
                <a href="?" class="btn btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>

    <!-- Tabla principal -->
    <div class="table-container">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Contraseña</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td>••••••••</td>
                            <td><span class="badge bg-<?= $row['role'] == 'admin' ? 'primary' : 'secondary' ?>"><?= $row['role'] ?></span></td>
                            <td class="action-buttons">
                                <a href="../crud_users/editar_usuario.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                                <a href="../crud_users/eliminar_usuario.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro que deseas eliminar este usuario?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>