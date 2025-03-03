<?php

// Inicia sesión
session_start();

// Verifica si el usuario es admin
if (!isset($_SESSION['admin'])) {
    // Redirige al login si no es admin
    header("Location: auth/loginform.php");
    exit();
}
?>
