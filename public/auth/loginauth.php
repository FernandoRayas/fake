<?php
session_start();

if (isset($_POST['btnLogin'])) {
    $txtEmail = $_POST['email'];
    $txtPassword = $_POST['password'];

    include "../modelo/conexion.php";

    // Consulta preparada
    $sql = "SELECT * FROM users WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $txtEmail, $txtPassword);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Guardar sesión
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        // Redirigir a todos al mismo dashboard
        header("Location: ../pages/dashboard.php");
        exit();
    }

    // Error
    header("Location: fail.php?error=1");
    exit();
}
?>
