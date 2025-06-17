<?php
// filepath: c:\xampp\htdocs\xampp\angel\ProyectoAngel\ProyectoAngel\procesar.php
include 'conexion.php';

// Validar y recibir datos
$nombre  = trim($_POST['nombre'] ?? '');
$correo  = trim($_POST['correo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

$errores = [];

if ($nombre === '' || $correo === '' || $mensaje === '') {
    $errores[] = "Todos los campos son obligatorios.";
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo electrónico no es válido.";
}

if (empty($errores)) {
    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO contacto (nombre, correo, asunto) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nombre, $correo, $mensaje);
    if ($stmt->execute()) {
        $exito = true;
    } else {
        $errores[] = "Error al guardar el mensaje. Intenta más tarde.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contacto | MGB Software</title>
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .mensaje-exito {
            background: #e0f7e9;
            color: #20734b;
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            padding: 14px;
            margin: 24px auto;
            max-width: 400px;
            text-align: center;
            font-size: 1.1em;
        }
        .mensaje-error {
            background: #ffeaea;
            color: #c0392b;
            border: 1px solid #e57373;
            border-radius: 6px;
            padding: 14px;
            margin: 24px auto;
            max-width: 400px;
            text-align: center;
            font-size: 1.1em;
        }
        .volver {
            display: block;
            margin: 18px auto 0 auto;
            text-align: center;
            color: #005baa;
            font-weight: 600;
            text-decoration: none;
        }
        .volver:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="IMG/logo.png" alt="Logo MGB">
        </div>
        <h1>Contacto</h1>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="servicios.html">Servicios</a>
            <a href="paquetes.html">Paquetes</a>
            <a href="contacto.php">Contacto</a>
        </nav>
    </header>
    <main>
        <?php if (!empty($exito)): ?>
            <div class="mensaje-exito">
                ¡Gracias por contactarnos! Tu mensaje ha sido enviado correctamente.
            </div>
        <?php else: ?>
            <?php foreach ($errores as $error): ?>
                <div class="mensaje-error"><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a class="volver" href="contacto.php">Volver</a>
    </main>
</body>
</html>