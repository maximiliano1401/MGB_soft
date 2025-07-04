<?php
require_once 'includes/config.php';

// Destruir todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Redireccionar al login
redirect('index.php');
?>
