<?php
require_once 'conexion.php';

echo "<h2>🔧 Verificación del Sistema</h2>";
echo "<p>Verificando la conexión y estructura de la base de datos...</p>";

// Verificar conexión
if ($conn->connect_error) {
    echo "❌ Error de conexión: " . $conn->connect_error;
    exit;
}
echo "✅ Conexión a la base de datos exitosa<br>";

// Verificar tablas principales
$tablas = ['usuarios', 'empresas', 'departamentos', 'puestos', 'empleados', 'nomina', 'ausencias', 'altas_bajas'];

foreach ($tablas as $tabla) {
    $result = $conn->query("SHOW TABLES LIKE '$tabla'");
    if ($result->num_rows > 0) {
        echo "✅ Tabla '$tabla' existe<br>";
    } else {
        echo "❌ Tabla '$tabla' no encontrada<br>";
    }
}

// Verificar si hay usuarios
$result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
$usuarios = $result->fetch_assoc()['total'];
echo "<br><strong>👥 Usuarios registrados:</strong> $usuarios<br>";

if ($usuarios == 0) {
    echo "<p style='color: orange;'>⚠️ No hay usuarios registrados. <a href='setup_admin.php'>Crear administrador inicial</a></p>";
} else {
    echo "<p style='color: green;'>✅ Sistema listo para usar. <a href='login.php'>Iniciar sesión</a></p>";
}

// Verificar algunas configuraciones PHP importantes
echo "<br><h3>📋 Información del Sistema:</h3>";
echo "PHP Version: " . phpversion() . "<br>";
echo "MySQL Version: " . $conn->server_info . "<br>";

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificación del Sistema - MGB</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: #f6f8fa;
            line-height: 1.6;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #005baa; }
        h3 { color: #333; border-bottom: 2px solid #00bcd4; padding-bottom: 0.5rem; }
        a { color: #005baa; text-decoration: none; font-weight: bold; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <!-- El contenido PHP se muestra aquí -->
        
        <br>
        <hr>
        <p><strong>🚀 Próximos pasos:</strong></p>
        <ol>
            <li>Si no hay usuarios, crear el administrador inicial</li>
            <li>Iniciar sesión en el sistema</li>
            <li>Registrar empresas y departamentos</li>
            <li>Comenzar a gestionar empleados</li>
        </ol>
        
        <p><a href="login.php">← Volver al Login</a></p>
    </div>
</body>
</html>
