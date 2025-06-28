<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: ../index.php"); // Redirigir si no está autenticado
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/icono_fake.png" type="image/x-icon">
    <title>Home - Fake</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #f8f9fa;
            --text-color: #333;
            --card-bg: #fff;
            --shadow: rgba(0, 0, 0, 0.1);
            --primary-color: #5c6bc0;
            --primary-hover: #3f51b5;
        }

        [data-theme="dark"] {
            --bg-color: #1a1a1a;
            --text-color: #e0e0e0;
            --card-bg: #2d2d2d;
            --shadow: rgba(0, 0, 0, 0.3);
            --primary-color: #7986cb;
            --primary-hover: #5c6bc0;
        }

        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px var(--shadow);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .theme-toggle:hover {
            background-color: var(--primary-hover);
            transform: scale(1.1);
        }

        .logo {
            width: 200px; /* Ajusta el tamaño del logo si es necesario */
            margin-top: 50px;
        }

        .welcome {
            text-align: center;
            font-size: 24px;
            margin-top: 20px;
            color: var(--text-color);
        }

        .product-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
            gap: 20px;
        }

        .product {
            background-color: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px var(--shadow);
            width: 220px;
            text-align: center;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .product img {
            width: 100%;
            border-radius: 5px;
        }

        .product-name {
            font-size: 18px;
            color: var(--text-color);
            margin-top: 10px;
        }

        .product-price {
            font-size: 16px;
            color: var(--primary-color);
            margin-top: 5px;
        }

        .btn-buy {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-buy:hover {
            background-color: var(--primary-hover);
        }

        .logout {
            margin-top: 20px;
            font-size: 16px;
            color: #007bff;
            text-decoration: none;
        }

        .logout:hover {
            text-decoration: underline;
        }

        /* Ajustes para breadcrumb en modo oscuro */
        .breadcrumb {
            background-color: var(--card-bg) !important;
            transition: background-color 0.3s ease;
        }

        .breadcrumb a {
            color: var(--primary-color);
        }

        .breadcrumb-item.active {
            color: var(--text-color);
        }
    </style>
</head>
<body>
    <!-- Botón de cambio de tema -->
    <button class="theme-toggle" onclick="toggleTheme()" title="Cambiar tema">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>

    <!-- Breadcrumb -->
    <div class="container mt-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-white p-2 rounded">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tienda Online</li>
            </ol>
        </nav>
    </div>

    <!-- Logo -->
    <img src="../images/fake_logo.jpg" alt="Fake Logo" class="logo">

    <!-- Mensaje de bienvenida -->
    <p class="welcome">Bienvenido a Fake</p>

    <!-- Productos -->
    <div class="product-container">
        <!-- Producto 1 -->
        <div class="product">
            <img src="../images/tennis1.jpg" alt="Tenis 1">
            <p class="product-name">Tenis Modelo A</p>
            <p class="product-price">$59.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 2 -->
        <div class="product">
            <img src="../images/tennis2.jpg" alt="Tenis 2">
            <p class="product-name">Tenis Modelo B</p>
            <p class="product-price">$69.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 3 -->
        <div class="product">
            <img src="../images/tennis3.jpg" alt="Tenis 3">
            <p class="product-name">Tenis Modelo C</p>
            <p class="product-price">$79.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 4 -->
        <div class="product">
            <img src="../images/tennis4.jpg" alt="Tenis 4">
            <p class="product-name">Tenis Modelo D</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>
    </div>

    <!-- Cerrar sesión -->
    <a href="dashboard.php" class="btn btn-danger mt-3 mb-3"><i class="fas fa-sign-out-alt"></i> Volver</a>

    <script>
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('theme-icon');
            
            if (body.getAttribute('data-theme') === 'dark') {
                body.removeAttribute('data-theme');
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
                localStorage.setItem('theme', 'light');
            } else {
                body.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
                localStorage.setItem('theme', 'dark');
            }
        }

        // Cargar tema guardado al inicio
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme');
            const themeIcon = document.getElementById('theme-icon');
            
            if (savedTheme === 'dark') {
                document.body.setAttribute('data-theme', 'dark');
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        });
    </script>
</body>
</html>