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
        --bg-color: #f4f6f9;
        --text-color: #2c3e50;
        --card-bg: #ffffff;
        --shadow: rgba(0, 0, 0, 0.08);
        --primary-color: #5c6bc0;
        --primary-hover: #3f51b5;
        --radius: 16px;
        --transition: 0.3s ease;
    }

    [data-theme="dark"] {
        --bg-color: #121212;
        --text-color: #e0e0e0;
        --card-bg: #1e1e1e;
        --shadow: rgba(255, 255, 255, 0.05);
        --primary-color: #7986cb;
        --primary-hover: #5c6bc0;
    }

    body {
        font-family: 'Segoe UI', 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--bg-color);
        color: var(--text-color);
        display: flex;
        flex-direction: column;
        align-items: center;
        transition: background-color var(--transition), color var(--transition);
    }

    .theme-toggle {
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: var(--primary-color);
        color: white;
        border: none;
        border-radius: 50%;
        width: 48px;
        height: 48px;
        font-size: 20px;
        cursor: pointer;
        box-shadow: 0 4px 12px var(--shadow);
        transition: all var(--transition);
        z-index: 1000;
    }

    .theme-toggle:hover {
        background-color: var(--primary-hover);
        transform: scale(1.1);
    }

    .logo {
        width: 180px;
        margin: 40px auto 20px auto;
        filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.1));
        transition: transform var(--transition);
    }

    .logo:hover {
        transform: scale(1.03);
    }

    .welcome {
        font-size: 28px;
        font-weight: 600;
        margin-top: 10px;
        margin-bottom: 30px;
        text-align: center;
        color: var(--text-color);
    }

    .container nav.breadcrumb {
        background-color: transparent;
    }

    .breadcrumb {
        background-color: var(--card-bg) !important;
        border-radius: var(--radius);
        box-shadow: 0 2px 8px var(--shadow);
        padding: 10px 20px;
        transition: background-color var(--transition);
    }

    .breadcrumb a {
        color: var(--primary-color);
        text-decoration: none;
    }

    .breadcrumb a:hover {
        text-decoration: underline;
    }

    .breadcrumb-item.active {
        color: var(--text-color);
    }

    .product-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
        padding: 30px 20px 40px;
        max-width: 1400px;
        width: 100%;
    }

    .product {
        background-color: var(--card-bg);
        border-radius: var(--radius);
        padding: 20px;
        box-shadow: 0 10px 20px var(--shadow);
        text-align: center;
        transition: transform 0.2s ease, box-shadow var(--transition);
    }

    .product:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px var(--shadow);
    }

    .product img {
        width: 100%;
        height: auto;
        border-radius: 12px;
        object-fit: cover;
        transition: transform var(--transition);
    }

    .product img:hover {
        transform: scale(1.03);
    }

    .product-name {
        font-size: 18px;
        font-weight: 600;
        margin-top: 15px;
        color: var(--text-color);
    }

    .product-price {
        font-size: 17px;
        font-weight: 500;
        color: var(--primary-color);
        margin-top: 8px;
    }

    .btn-buy {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover));
        color: #fff;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        margin-top: 12px;
        cursor: pointer;
        transition: background var(--transition), transform 0.2s;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-buy:hover {
        transform: scale(1.05);
        background: linear-gradient(135deg, var(--primary-hover), var(--primary-color));
    }

    .btn-buy:active {
        transform: scale(0.98);
    }

    .btn-danger {
        font-size: 15px;
        padding: 12px 24px;
        border-radius: 10px;
        font-weight: 500;
        transition: transform 0.2s ease;
        margin-top: 20px;
        box-shadow: 0 4px 14px rgba(255, 0, 0, 0.2);
    }

    .btn-danger:hover {
        transform: scale(1.05);
    }

    @media (max-width: 576px) {
        .logo {
            width: 140px;
        }

        .welcome {
            font-size: 22px;
        }

        .btn-buy {
            font-size: 14px;
            padding: 10px 16px;
        }

        .btn-danger {
            font-size: 14px;
        }
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
            <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=300&fit=crop" alt="Nike Air Max">
            <p class="product-name">Nike Air Max 90</p>
            <p class="product-price">$59.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 2 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?w=400&h=300&fit=crop" alt="Adidas Ultraboost">
            <p class="product-name">Adidas Ultraboost 22</p>
            <p class="product-price">$69.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 3 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=400&h=300&fit=crop" alt="Converse Chuck Taylor">
            <p class="product-name">Converse Chuck Taylor</p>
            <p class="product-price">$79.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 4 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=400&h=300&fit=crop" alt="Vans Old Skool">
            <p class="product-name">Vans Old Skool</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

         <!-- Producto 5 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1584735175315-9d5df23860e6?w=400&h=300&fit=crop" alt="Puma Suede Classic">
            <p class="product-name">Puma Suede Classic</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

         <!-- Producto 6 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=400&h=300&fit=crop" alt="New Balance 574">
            <p class="product-name">New Balance 574</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

         <!-- Producto 7 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1551107696-a4b0c5a0d9a2?w=400&h=300&fit=crop" alt="Reebok Classic">
            <p class="product-name">Reebok Classic Leather</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 8 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1543508282-6319a3e2621f?w=400&h=300&fit=crop" alt="Jordan 1 Retro">
            <p class="product-name">Jordan 1 Retro High</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 9 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1520256862855-398228c41684?w=400&h=300&fit=crop" alt="Fila Disruptor">
            <p class="product-name">Fila Disruptor II</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 10 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1552066344-2464c1135c32?w=400&h=300&fit=crop" alt="Under Armour Charged">
            <p class="product-name">Under Armour Charged</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 11 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=400&h=300&fit=crop" alt="Asics Gel-Kayano">
            <p class="product-name">Asics Gel-Kayano</p>
            <p class="product-price">$89.99</p>
            <button class="btn-buy">Comprar</button>
        </div>

        <!-- Producto 12 -->
        <div class="product">
            <img src="https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=300&fit=crop" alt="Nike Air Force 1">
            <p class="product-name">Nike Air Force 1</p>
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