<?php
// load_env.php

// Ruta absoluta del archivo .env
$env_file = realpath(__DIR__ . '/../.env'); // Ajusta la ruta para subir un nivel desde la carpeta 'modelo'

if ($env_file === false) {
    die('No se encontró el archivo .env');
}


// Lee el archivo .env
function loadEnv($file) {
    if (file_exists($file)) {
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            // Ignora los comentarios y líneas vacías
            if (strpos($line, '#') === 0) continue;

            // Divide la línea en clave y valor
            list($key, $value) = explode('=', $line, 2);

            // Elimina espacios en blanco alrededor de la clave y el valor
            $key = trim($key);
            $value = trim($value);

            // Define la variable de entorno
            putenv("$key=$value");
        }
    } else {
        die("No se encontró el archivo .env");
    }
}

// Llamar a la función loadEnv para cargar el archivo .env
loadEnv($env_file);
?>
