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
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo {
            width: 200px; /* Ajusta el tamaño del logo si es necesario */
            margin-top: 50px;
        }

        .welcome {
            text-align: center;
            font-size: 24px;
            margin-top: 20px;
            color: #333;
        }

        .product-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
            gap: 20px;
        }

        .product {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 220px;
            text-align: center;
        }

        .product img {
            width: 100%;
            border-radius: 5px;
        }

        .product-name {
            font-size: 18px;
            color: #333;
            margin-top: 10px;
        }

        .product-price {
            font-size: 16px;
            color: #5c6bc0;
            margin-top: 5px;
        }

        .btn-buy {
            background-color: #5c6bc0;
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
            background-color: #3f51b5;
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
    </style>
</head>
<body>
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
    <a href="dashboard.php" class="btn btn-danger mt-3"><i class="fas fa-sign-out-alt"></i> Volver</a>
</body>
</html>
