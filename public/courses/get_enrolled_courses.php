<?php


// Configurar el encabezado HTTP como JSON para el intercambio de datos
header('Content-Type: application/json');

// Iniciar la sesion
session_start();
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin' && $_SESSION['user_role'] != 'master') {
    header("Location: ../index.php");
    exit();
}

include "../modelo/conexion.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
}
