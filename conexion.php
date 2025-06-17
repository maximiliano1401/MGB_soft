<?php
// filepath: c:\xampp\htdocs\xampp\angel\ProyectoAngel\ProyectoAngel\php\conexion.php

$host = "localhost";
$user = "root";
$pass = ""; // Cambia esto si tu usuario tiene contraseña
$db   = "mgb"; // Cambia por el nombre real de tu base

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>