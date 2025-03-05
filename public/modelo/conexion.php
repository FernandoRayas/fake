<?php
// Incluye el archivo que carga el .env
include_once 'load_env.php'; // Ajusta la ruta si es necesario

// Obtener las credenciales desde el archivo .env
$servername = getenv('DB_SERVER_NAME');
$username = getenv('DB_USER_NAME');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_DATABASE');

// Verifica si las variables fueron cargadas correctamente
if (!$servername || !$username || !$password || !$database) {
    die("Error: las variables de entorno no fueron cargadas correctamente.");
}

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>
