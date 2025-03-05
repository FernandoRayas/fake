<?php
// Inicia sesión y verifica si el usuario es admin
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica el rol del usuario
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php");
    exit();
}

// Conexión a la base de datos
include "../modelo/conexion.php";

// Inicializar mensaje vacío
$mensaje = "";

// Procesa el formulario de creación de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validación de la contraseña
    if (strlen($password) < 5 || strlen($password) > 10) {
        $mensaje = "La contraseña debe tener entre 5 y 10 caracteres.";
    } else {
        // Inserta el nuevo usuario en la base de datos
        $sql = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            header("Location: ../pages/crud_users.php");
            exit();
        } else {
            $mensaje = "Error al crear usuario.";
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
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilo básico para el formulario */
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

<!-- Formulario -->
<div class="container">
    <h2>Crear Usuario</h2>

    <?php if ($mensaje): ?>
        <p class="alert"><?= $mensaje ?></p> <!-- Mensaje de error o éxito -->
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="name">Nombre:</label>
            <input type="text" name="name" id="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Correo:</label>
            <input type="email" name="email" id="email" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña (entre 5 y 10 caracteres):</label>
            <input type="password" name="password" id="password" required>
        </div>

        <div class="form-group">
            <label for="role">Rol:</label>
            <select name="role" id="role">
                <option value="admin">Admin</option>
                <option value="master">Docente</option>
                <option value="user">Usuario</option>
            </select>
        </div>

        <button type="submit">Crear Usuario</button>
    </form>

    <a class="back-link" href="../pages/crud_users.php">Volver a la Administración</a> <!-- Enlace para regresar -->
</div>

</body>
</html>
