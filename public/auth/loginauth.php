<?php
session_start();

if (isset($_POST['btnLogin'])) {
    // Verificar CAPTCHA
    if ($_POST['captcha'] !== "smwm") {
        // Si el CAPTCHA no es correcto
        header("Location: fail.php?error=1");
        exit();
    }

    $txtEmail = $_POST['email'];
    $txtPassword = $_POST['password'];

    include "../modelo/conexion.php";

    // Consulta preparada para evitar SQL Injection
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

        // Redirigir a todos al dashboard
        header("Location: ../pages/dashboard.php");
        exit();
    }

    // Si el usuario no es válido
    header("Location: fail.php?error=1");
    exit();
}
?>
