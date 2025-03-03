<?php
$servername = "127.0.0.1"; // Dirección del servidor 
$username = "root"; // Nombre de usuario
$password = "Secret123x"; // Contraseña
$database = "fake"; // Nombre de la base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
