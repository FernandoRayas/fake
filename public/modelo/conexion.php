<?php
$servername = "127.0.0.1";
$username = "root";
$password = "Secret123x";
$database = "fake";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
$conn->set_charset("utf8");
?>
