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
        /* Estilo renovado para el formulario */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            text-align: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            width: 45%;
            max-width: 500px;
            margin: 30px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        h2 {
            color: #2c3e50;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 1.8em;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 0.95em;
        }
        input, select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dfe6e9;
            border-radius: 8px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            font-size: 0.95em;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
            background-color: white;
        }
        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #3498db, #2c81ba);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1em;
            transition: all 0.3s ease;
            margin-top: 10px;
            letter-spacing: 0.5px;
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
