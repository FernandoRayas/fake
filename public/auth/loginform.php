<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/icono_fake.png" type="image/x-icon">
    <title>Login</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            text-align: center;
        }

        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        input[type="email"],
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #5c6bc0;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #3f51b5;
        }

        .logo {
            width: 100px;
            margin-bottom: 20px;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="../images/fake_logo.jpg" alt="Fake Logo" class="logo">

        <h2>Bienvenido a Fake</h2>
        <form action="loginauth.php" method="post" onsubmit="return validarFormulario()">
            <input type="text" name="email" id="email" placeholder="Correo electrónico" required>
            <p id="emailError" class="error">Correo inválido.</p>

            <input type="password" name="password" id="password" placeholder="Contraseña" required>
            <p id="passwordError" class="error">La contraseña no debe contener caracteres especiales.</p>

            <!-- Imagen CAPTCHA (la imagen predefinida) -->
            <img src="https://e7.pngegg.com/pngimages/917/842/png-clipart-captcha-user-computer-program-computer-security-computer-software-computer-text-computer.png" alt="Captcha" id="captcha-image" height="80px">

            <!-- Campo para ingresar el captcha -->
            <input type="text" name="captcha" id="captcha" placeholder="Que dice la imagen?" required>
            <p id="captchaError" class="error">¡Captcha incorrecto!</p>

            <input type="submit" name="btnLogin" value="Iniciar sesión">

            <p>Dudas o quejas al telefono: <u> 6181795344</u></p>
        </form>
    </div>


    <script>
        function validarFormulario() {
            let email = document.getElementById("email").value;
            let password = document.getElementById("password").value;
            let emailError = document.getElementById("emailError");
            let passwordError = document.getElementById("passwordError");

            // Expresión regular
            let emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;

            // Expresión regular para validar 
            let passwordRegex = /^[a-zA-Z0-9]+$/;

            let isValid = true;

            // Validar correo
            if (!emailRegex.test(email)) {
                emailError.style.display = "block";
                isValid = false;
            } else {
                emailError.style.display = "none";
            }

            // Validar contraseña
            if (!passwordRegex.test(password)) {
                passwordError.style.display = "block";
                isValid = false;
            } else {
                passwordError.style.display = "none";
            }

            return isValid; // Si es válido, permite el envío del formulario
        }
    </script>
</body>
</html>
