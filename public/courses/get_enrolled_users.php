<?php

// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master' && $_SESSION['user_role'] != 'user') {
    header("Location: ../index.php");
    exit();
}
