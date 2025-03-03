<?php
session_start();

// Verifica si es admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] != 'admin') {
    header("Location: ../index.php"); // Redirige si no es admin
    exit();
}

include "../modelo/conexion.php"; // Conexión DB

$id = $_GET['id']; // ID usuario
$sql = "DELETE FROM users WHERE id = ?"; // Eliminar usuario
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id); // Vincula ID

// Ejecuta y redirige
if ($stmt->execute()) {
    header("Location: ../pages/admin.php");
    exit();
} else {
    echo "Error."; // Error
}
?>
