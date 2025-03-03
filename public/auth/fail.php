<?php
// Muestra alerta si hay error
if (isset($_GET['error']) && $_GET['error'] == 1) {
    echo '<script type="text/javascript">
            alert("Correo o contraseña incorrectos."); // Alerta error
            setTimeout(function() {
                window.location.href = "../index.php"; // Redirige después de 0ms
            }, 0);
          </script>';
}
?>
