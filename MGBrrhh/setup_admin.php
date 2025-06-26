<?php
require_once 'conexion.php';

// Datos del administrador inicial
$usuario = 'admin';
$password = 'admin123'; // Cambia esta contraseña por una más segura

// Verificar si ya existe un usuario
$check = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$result = $check->fetch_assoc();

if ($result['total'] > 0) {
    echo "Ya existen usuarios en el sistema.<br>";
    echo "Para crear otro usuario, usa el formulario de registro normal.<br>";
    echo "<a href='registro_admin.php'>Ir al formulario de registro</a>";
} else {
    // Encriptar la contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar el usuario inicial
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $usuario, $hash);
    
    if ($stmt->execute()) {
        echo "<h2>✅ Usuario administrador creado exitosamente</h2>";
        echo "<p><strong>Usuario:</strong> admin</p>";
        echo "<p><strong>Contraseña:</strong> admin123</p>";
        echo "<br>";
        echo "<p style='color: red;'><strong>⚠️ IMPORTANTE:</strong> Cambia esta contraseña después del primer login por seguridad.</p>";
        echo "<br>";
        echo "<a href='login.php' style='background: #005baa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Iniciar Sesión</a>";
    } else {
        echo "<h2>❌ Error al crear el usuario</h2>";
        echo "<p>Error: " . $conn->error . "</p>";
    }
    
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración Inicial - MGB Software</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #005baa 0%, #00bcd4 100%);
            color: #333;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #fff;
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .logo {
            width: 80px;
            height: 80px;
            background: #005baa;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        h1 {
            color: #005baa;
            margin-bottom: 1rem;
        }
        a {
            display: inline-block;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">MGB</div>
        <h1>Configuración Inicial del Sistema</h1>
        <!-- El contenido PHP se muestra aquí -->
    </div>
</body>
</html>
