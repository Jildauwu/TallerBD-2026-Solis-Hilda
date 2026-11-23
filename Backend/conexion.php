<?php

$servidor = "localhost";
$base_datos = "control_prestamos";
$usuario = "prestamos.app";
$contrasena = getenv("DB_PASSWORD");

if (!$contrasena) {
    error_log("Falta configurar DB_PASSWORD");
    http_response_code(500);
    exit("Error de configuración de la base de datos.");
}

try {
    $conexion = new PDO(
        "mysql:host=$servidor;dbname=$base_datos;charset=utf8mb4",
        $usuario,
        $contrasena,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("No se pudo establecer la conexión con la base de datos.");
}