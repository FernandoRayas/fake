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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradient 3s ease infinite;
        }

        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #2c3e50;
            font-size: 2em;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .user-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2em;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .alert {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 1.1em;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 1.1em;
        }

        input, select {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 2px solid #e0e6ed;
            border-radius: 12px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: white;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px;
        }

        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            margin-top: 20px;
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: rgba(102, 126, 234, 0.1);
        }

        .back-link:hover {
            background: rgba(102, 126, 234, 0.2);
            transform: translateX(-5px);
        }

        .form-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        /* Animaciones de entrada */
        .container {
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.2s; }
        .form-group:nth-child(3) { animation-delay: 0.3s; }
        .form-group:nth-child(4) { animation-delay: 0.4s; }
        .form-group:nth-child(5) { animation-delay: 0.5s; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                padding: 30px 20px;
            }
            
            .header h2 {
                font-size: 1.8em;
            }
            
            .user-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="user-icon">
            <i class="fas fa-user-edit"></i>
        </div>
        <h2>Editar Usuario</h2>
        <p>Modifica los datos del usuario seleccionado</p>
    </div>

    <!-- Mensaje de error si existe -->
    <?php if ($mensaje): ?>
        <div class="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= $mensaje ?>
        </div>
    <?php endif; ?>

    <!-- Formulario de edición -->
    <form method="POST">
        <!-- Nombre -->
        <div class="form-group">
            <label for="name">
                <i class="fas fa-user"></i> Nombre Completo
            </label>
            <div class="input-wrapper">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
        </div>
        
        <!-- Correo -->
        <div class="form-group">
            <label for="email">
                <i class="fas fa-envelope"></i> Correo Electrónico
            </label>
            <div class="input-wrapper">
                <i class="fas fa-envelope input-icon"></i>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
        </div>

        <!-- Contraseña (opcional) -->
        <div class="form-group">
            <label for="password">
                <i class="fas fa-lock"></i> Nueva Contraseña
            </label>
            <div class="input-wrapper">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" id="password" placeholder="Dejar vacío para no cambiar">
            </div>
        </div>

        <!-- Rol -->
        <div class="form-group">
            <label for="role">
                <i class="fas fa-user-tag"></i> Rol del Usuario
            </label>
            <div class="input-wrapper">
                <i class="fas fa-user-tag input-icon"></i>
                <select name="role" id="role">
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>
                        <i class="fas fa-crown"></i> Administrador
                    </option>
                    <option value="master" <?= $user['role'] == 'master' ? 'selected' : '' ?>>
                        <i class="fas fa-chalkboard-teacher"></i> Docente
                    </option>
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>
                        <i class="fas fa-user"></i> Usuario
                    </option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <!-- Botón -->
            <button type="submit" class="submit-btn">
                <i class="fas fa-save"></i> Actualizar Usuario
            </button>

            <!-- Enlace de regreso -->
            <a class="back-link" href="../pages/crud_users.php">
                <i class="fas fa-arrow-left"></i> Volver a la Administración
            </a>
        </div>
    </form>
</div>

</body>
</html>