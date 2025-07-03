<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/icono_fake.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <title>Login</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(120deg, #dbeafe, #f8f9fa);
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .login-container {
        background: #ffffff;
        padding: 40px 30px;
        border-radius: 18px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        width: 340px;
        text-align: center;
        animation: fadeIn 0.9s ease-in-out;
    }

    @keyframes fadeIn {
        0% { opacity: 0; transform: translateY(20px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .logo {
        width: 100px;
        margin-bottom: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .logo:hover {
        transform: scale(1.05);
    }

    h2 {
        font-size: 22px;
        font-weight: 600;
        color: #2d3436;
        margin-bottom: 25px;
    }

    input[type="text"],
    input[type="email"],
    input[type="password"],
    input[type="submit"],
    input[type="captcha"],
    #captcha {
        width: 100%;
        padding: 12px 14px;
        margin: 8px 0;
        border: 1px solid #ced6e0;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s ease;
    }

    input:focus {
        border-color: #5c6bc0;
        outline: none;
        box-shadow: 0 0 8px rgba(92, 107, 192, 0.2);
    }

    input[type="submit"] {
        background: linear-gradient(135deg, #5c6bc0, #3f51b5);
        color: white;
        font-weight: 600;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.3s ease, transform 0.2s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    input[type="submit"]:hover {
        background: linear-gradient(135deg, #3f51b5, #283593);
        transform: scale(1.03);
        box-shadow: 0 6px 14px rgba(0, 0, 0, 0.15);
    }

    input[type="submit"]:active {
        transform: scale(0.97);
    }

    img#captcha-image {
        margin: 12px 0;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 260px;
        object-fit: cover;
    }

    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 4px;
        text-align: left;
        display: none;
    }

    p {
        font-size: 14px;
        color: #555;
        margin-top: 14px;
    }

    u {
        color: #3949ab;
    }

    @media screen and (max-width: 400px) {
        .login-container {
            width: 90%;
            padding: 30px 20px;
        }

        h2 {
            font-size: 20px;
        }

        input[type="submit"] {
            font-size: 14px;
        }
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
