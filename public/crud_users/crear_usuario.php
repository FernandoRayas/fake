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
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            overflow: hidden;
        }

        .main-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .main-title {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            animation: slideInDown 0.8s ease;
        }

        .subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 0;
        }

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
            transform: translateX(2px);
        }

        .form-modern {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            margin-bottom: 25px;
        }

        .form-group-modern {
            margin-bottom: 25px;
            position: relative;
        }

        .form-label-modern {
            display: block;
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
            transform: translateY(-1px);
        }

        .form-control-modern:hover {
            border-color: #d1d5db;
            background: white;
        }

        .select-modern {
            position: relative;
        }

        .select-modern::after {
            content: '\f078';
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            pointer-events: none;
        }

        .select-modern select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            padding-right: 45px;
        }

        .btn-modern {
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            border: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            width: 100%;
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .btn-success-modern {
            background: var(--success-gradient);
            color: white;
        }

        .btn-primary-modern {
            background: var(--primary-gradient);
            color: white;
        }

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
            transform: translateX(-5px);
        }

        .alert-modern {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInDown 0.5s ease;
        }

        .alert-error {
            background: linear-gradient(135deg, #ffe6e6 0%, #ffcccc 100%);
            color: #dc3545;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: linear-gradient(135deg, #e6f7ff 0%, #ccf2ff 100%);
            color: #28a745;
            border: 1px solid #c3e6cb;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .role-option {
            position: relative;
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
            transition: all 0.3s ease;
            text-align: center;
            font-weight: 500;
        }

        .role-option input[type="radio"]:checked + label {
            background: var(--primary-gradient);
            color: white;
            border-color: #667eea;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .role-option label i {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

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

        .strength-weak { background: #dc3545; width: 25%; }
        .strength-medium { background: #ffc107; width: 50%; }
        .strength-good { background: #28a745; width: 75%; }
        .strength-strong { background: #20c997; width: 100%; }

        @keyframes slideInDown {
            from {
                transform: translateY(-30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

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
        <div class="header-section">
            <h1 class="main-title">
                <i class="fas fa-user-plus"></i> Crear Usuario
            </h1>
            <p class="subtitle">Agrega un nuevo usuario al sistema</p>
        </div>

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

            // Animación de entrada
            const formElements = document.querySelectorAll('.form-group-modern');
            formElements.forEach((element, index) => {
                setTimeout(() => {
                    element.style.animation = 'fadeInUp 0.6s ease forwards';
                }, index * 100);
            });
        });
    </script>
</body>
</html>