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
$tipo_mensaje = "";

// Procesa el formulario de creación de usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    // Validación de la contraseña
    if (strlen($password) < 5 || strlen($password) > 10) {
        $mensaje = "La contraseña debe tener entre 5 y 10 caracteres.";
        $tipo_mensaje = "error";
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
            $tipo_mensaje = "error";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilo renovado para el formulario */
        /* Variables CSS */
        :root {
            --primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --danger: #dc3545;
            --warning: #ffc107;
            --success-color: #28a745;
        }

        /* Layout Principal */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            background: var(--primary);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .main-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            position: relative;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
        }

        /* Título */
        .main-title {
            background: var(--primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
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
            margin-bottom: 30px;
        }

        /* Breadcrumb */
        .breadcrumb-modern {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 30px;
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

        /* Formulario */
        .form-modern {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;

        .form-group-modern {
            margin-bottom: 25px;
        }

        .form-label-modern {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #34495e;
            font-size: 0.95em;
        }
        input, select {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .form-label-modern i {
            margin-right: 8px;
            color: #667eea;
        }

        .form-control-modern {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            background: white;
        }

        /* Botones */
        .btn-modern {
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
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

        .btn-success-modern {
            background: var(--success);
            color: white;
            border: none;
            border-radius: 8px;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Alertas */
        .alert-modern {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-error {
            background: linear-gradient(135deg, #ffe6e6 0%, #ffcccc 100%);
            color: var(--danger);
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: linear-gradient(135deg, #e6f7ff 0%, #ccf2ff 100%);
            color: var(--success-color);
            border: 1px solid #c3e6cb;
        }

        /* Roles */
        .role-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .role-option input[type="radio"] {
            display: none;
        }

        .role-option label {
            display: block;
            padding: 15px 20px;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 500;
            font-size: 1em;
            transition: all 0.3s ease;
            margin-top: 10px;
            letter-spacing: 0.5px;
        }
        button:hover {
            background-color: #0056b3;
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }

        .role-option input[type="radio"]:checked + label {
            background: var(--primary);
            color: white;
            border-color: #667eea;
        }

        .role-option label i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        /* Barra de Contraseña */
        .password-strength {
            margin-top: 5px;
            font-size: 0.85rem;
        }

        .strength-bar {
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 5px;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { background: var(--danger); width: 25%; }
        .strength-medium { background: var(--warning); width: 50%; }
        .strength-good { background: var(--success-color); width: 75%; }
        .strength-strong { background: #20c997; width: 100%; }

        /* Link de Regreso */
        .back-link-modern {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s ease;
            background: rgba(102, 126, 234, 0.1);
        }

        .back-link-modern:hover {
            color: #4f46e5;
            background: rgba(102, 126, 234, 0.2);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                margin: 10px;
                padding: 25px;
            }
            
            .main-title {
                font-size: 2rem;
            }
            
            .role-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-modern">
                <li class="breadcrumb-item">
                    <a href="../pages/dashboard.php">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="../pages/crud_users.php">
                        <i class="fas fa-users"></i> Usuarios
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-user-plus"></i> Crear Usuario
                </li>
            </ol>
        </nav>

        <!-- Header -->
        <h1 class="main-title">
            <i class="fas fa-user-plus"></i> Crear Usuario
        </h1>

        <!-- Alert Messages -->
        <?php if ($mensaje): ?>
            <div class="alert-modern <?= $tipo_mensaje == 'error' ? 'alert-error' : 'alert-success' ?>">
                <i class="fas fa-<?= $tipo_mensaje == 'error' ? 'exclamation-triangle' : 'check-circle' ?>"></i>
                <?= $mensaje ?>
            </div>
        <?php endif; ?>

        <!-- Form Container -->
        <div class="form-modern">
            <form method="POST" id="createUserForm">
                <div class="form-group-modern">
                    <label for="name" class="form-label-modern">
                        <i class="fas fa-user"></i> Nombre Completo
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           class="form-control-modern" 
                           placeholder="Ingresa el nombre completo"
                           required>
                </div>
                
                <div class="form-group-modern">
                    <label for="email" class="form-label-modern">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" 
                           name="email" 
                           id="email" 
                           class="form-control-modern" 
                           placeholder="usuario@ejemplo.com"
                           required>
                </div>

                <div class="form-group-modern">
                    <label for="password" class="form-label-modern">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <input type="password" 
                           name="password" 
                           id="password" 
                           class="form-control-modern" 
                           placeholder="Entre 5 y 10 caracteres"
                           required
                           minlength="5"
                           maxlength="10">
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <small id="strengthText" class="text-muted">Ingresa una contraseña</small>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">
                        <i class="fas fa-shield-alt"></i> Rol del Usuario
                    </label>
                    <div class="role-options">
                        <div class="role-option">
                            <input type="radio" name="role" value="admin" id="role-admin">
                            <label for="role-admin">
                                <i class="fas fa-user-shield"></i>
                                Administrador
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" value="master" id="role-master">
                            <label for="role-master">
                                <i class="fas fa-chalkboard-teacher"></i>
                                Docente
                            </label>
                        </div>
                        <div class="role-option">
                            <input type="radio" name="role" value="user" id="role-user" checked>
                            <label for="role-user">
                                <i class="fas fa-user"></i>
                                Usuario
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-modern btn-success-modern">
                    <i class="fas fa-user-plus"></i> Crear Usuario
                </button>
            </form>
        </div>

        <!-- Back Link -->
        <div class="text-center">
            <a href="../pages/crud_users.php" class="back-link-modern">
                <i class="fas fa-arrow-left"></i> Volver a Administración
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');

            // Validación de contraseña en tiempo real
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const length = password.length;
                
                // Limpiar clases anteriores
                strengthFill.className = 'strength-fill';
                
                if (length === 0) {
                    strengthText.textContent = 'Ingresa una contraseña';
                    strengthText.className = 'text-muted';
                } else if (length < 5) {
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Muy corta (mínimo 5 caracteres)';
                    strengthText.className = 'text-danger';
                } else if (length > 10) {
                    strengthFill.classList.add('strength-weak');
                    strengthText.textContent = 'Muy larga (máximo 10 caracteres)';
                    strengthText.className = 'text-danger';
                } else if (length >= 5 && length <= 6) {
                    strengthFill.classList.add('strength-medium');
                    strengthText.textContent = 'Contraseña aceptable';
                    strengthText.className = 'text-warning';
                } else if (length >= 7 && length <= 8) {
                    strengthFill.classList.add('strength-good');
                    strengthText.textContent = 'Buena contraseña';
                    strengthText.className = 'text-success';
                } else {
                    strengthFill.classList.add('strength-strong');
                    strengthText.textContent = 'Excelente contraseña';
                    strengthText.className = 'text-success';
                }
            });

            // Validación del formulario
            document.getElementById('createUserForm').addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                
                if (name === '' || email === '') {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos obligatorios.');
                    return;
                }
                
                if (password.length < 5 || password.length > 10) {
                    e.preventDefault();
                    alert('La contraseña debe tener entre 5 y 10 caracteres.');
                    return;
                }
            });
        });
    </script>
</body>
</html>