<?php
// Parámetros de conexión
$host = 'localhost';
$user = 'root';
$password = ''; // Cambia por tu contraseña si tienes una
$database = 'mgb_contable';

// Crear conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Opcional: establecer el charset a utf8mb4
$conn->set_charset("utf8mb4");

// Puedes usar $conn en tus scripts PHP para consultas a la base de datos
?>