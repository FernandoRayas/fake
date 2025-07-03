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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --card-bg: rgba(255, 255, 255, 0.95);
            --text-primary: #2c3e50;
            --text-secondary: #6c757d;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            --radius: 15px;
        }

        body {
            background: var(--primary-bg);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow);
            margin: 20px auto;
            padding: 30px;
            max-width: 95%;
        }

        .main-title {
            background: var(--primary-bg);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 30px;
        }

        .breadcrumb-modern {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--radius);
            padding: 15px 20px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .breadcrumb-modern .breadcrumb-item a {
            color: #6366f1;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .breadcrumb-modern .breadcrumb-item a:hover {
            color: #4f46e5;
        }

        .btn-modern {
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .btn-primary-modern { background: var(--primary-bg); color: white; }
        .btn-success-modern { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
        .btn-danger-modern { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }

        .search-container, .table-container {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .search-input {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px 20px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .table-modern thead {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }

        .table-modern thead th {
            color: white;
            font-weight: 600;
            padding: 18px 15px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
        }

        .table-modern tbody td {
            padding: 15px;
            vertical-align: middle;
            border: none;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-bg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 10px;
        }

        .user-info { display: flex; align-items: center; }
        .user-name { font-weight: 600; color: var(--text-primary); }
        .user-email { color: var(--text-secondary); font-size: 0.9rem; }

        .role-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-admin {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .role-user {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }

        .btn-action {
            padding: 8px 12px;
            border-radius: 8px;
            border: none;
            font-size: 0.8rem;
            margin: 0 2px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            color: white;
        }

        .btn-delete {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
        }

        .btn-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            background: var(--primary-bg);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .main-title { font-size: 2rem; }
            .action-buttons { flex-direction: column; }
            .btn-modern { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-modern">
                <li class="breadcrumb-item">
                    <a href="dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-users"></i> Administración de Usuarios
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <h1 class="main-title">
            <i class="fas fa-users-cog"></i> Gestión de Usuarios
        </h1>

        <!-- Stats Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $result->num_rows ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $result->data_seek(0);
                    $adminCount = 0;
                    while ($row = $result->fetch_assoc()) {
                        if ($row['role'] == 'admin') $adminCount++;
                    }
                    echo $adminCount;
                    ?>
                </div>
                <div class="stat-label">Administradores</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-number">
                    <?php 
                    $result->data_seek(0);
                    $userCount = 0;
                    while ($row = $result->fetch_assoc()) {
                        if ($row['role'] == 'user') $userCount++;
                    }
                    echo $userCount;
                    ?>
                </div>
                <div class="stat-label">Usuarios Regulares</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="dashboard.php" class="btn btn-modern btn-primary-modern">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
            <a href="../crud_users/crear_usuario.php" class="btn btn-modern btn-success-modern">
                <i class="fas fa-user-plus"></i> Agregar Usuario
            </a>
        </div>

        <!-- Search Container -->
        <div class="search-container">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" class="form-control search-input" name="search" 
                               placeholder="Buscar por ID, nombre, email o rol..." 
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="d-flex gap-2">
                        <button class="btn btn-modern btn-primary-modern flex-fill" type="submit">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="?" class="btn btn-modern btn-danger-modern">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-modern">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-user"></i> Usuario</th>
                            <th><i class="fas fa-envelope"></i> Email</th>
                            <th><i class="fas fa-shield-alt"></i> Rol</th>
                            <th><i class="fas fa-cogs"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $result->data_seek(0);
                        if ($result->num_rows > 0):
                            while ($row = $result->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">#<?= $row['id'] ?></span>
                                </td>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($row['name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="user-name"><?= htmlspecialchars($row['name']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-email">
                                        <i class="fas fa-envelope"></i> <?= htmlspecialchars($row['email']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge <?= $row['role'] == 'admin' ? 'role-admin' : 'role-user' ?>">
                                        <i class="fas fa-<?= $row['role'] == 'admin' ? 'user-shield' : 'user' ?>"></i>
                                        <?= ucfirst($row['role']) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="../crud_users/editar_usuario.php?id=<?= $row['id'] ?>" 
                                       class="btn-action btn-edit" title="Editar usuario">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="../crud_users/eliminar_usuario.php?id=<?= $row['id'] ?>" 
                                       class="btn-action btn-delete" title="Eliminar usuario"
                                       onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5">
                                    <div class="no-data">
                                        <i class="fas fa-users-slash"></i>
                                        <h4>No hay usuarios registrados</h4>
                                        <p>Comienza agregando el primer usuario al sistema</p>
                                        <a href="../crud_users/crear_usuario.php" class="btn btn-modern btn-success-modern">
                                            <i class="fas fa-user-plus"></i> Agregar Usuario
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>