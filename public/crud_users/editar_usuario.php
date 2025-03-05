<?php
// Inicia sesión y verifica rol de admin
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include "../modelo/conexion.php";
$id = $_GET['id'];

// Consulta el usuario por ID
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Mensaje de error
$mensaje = "";

// Procesa el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = trim($_POST['password']);

    // Valida la contraseña
    if (!empty($password) && (strlen($password) < 5 || strlen($password) > 10)) {
        $mensaje = "La contraseña debe tener entre 5 y 10 caracteres.";
    } else {
        // Actualiza usuario con o sin contraseña
        if (!empty($password)) {
            $sql = "UPDATE users SET name=?, email=?, password=?, role=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $email, $password, $role, $id);
        } else {
            $sql = "UPDATE users SET name=?, email=?, role=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $name, $email, $role, $id);
        }

        // Ejecuta y redirige si es exitoso
        if ($stmt->execute()) {
            header("Location: ../pages/crud_users.php");
            exit();
        } else {
            $mensaje = "Error al actualizar usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/icono_fake.png" type="image/x-icon">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos básicos para el diseño de la página */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .container {
            width: 50%;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }
        input, select, button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border: none;
        }
        button:hover {
            background-color: #0056b3;
        }
        .alert {
            color: red;
            font-weight: bold;
        }
        .back-link {
            display: block;
            margin-top: 10px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Usuario</h2>

    <!-- Mensaje de error si existe -->
    <?php if ($mensaje): ?>
        <p class="alert"><?= $mensaje ?></p>
    <?php endif; ?>

    <!-- Formulario de edición -->
    <form method="POST">
        <!-- Nombre -->
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
        </div>
        
        <!-- Correo -->
        <div class="form-group">
            <label for="email">Correo:</label>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <!-- Contraseña (opcional) -->
        <div class="form-group">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" name="password" id="password" placeholder="Dejar vacío para no cambiar">
        </div>

        <!-- Rol -->
        <div class="form-group">
            <label for="role">Rol:</label>
            <select name="role" id="role">
                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="master" <?= $user['role'] == 'master' ? 'selected' : '' ?>>Docente</option>
                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Usuario</option>
            </select>
        </div>

        <!-- Botón -->
        <button type="submit">Actualizar Usuario</button>
    </form>

    <!-- Enlace de regreso -->
    <a class="back-link" href="../pages/crud_users.php">Volver a la Administración</a> <!-- Enlace para regresar -->
</div>

</body>
</html>
