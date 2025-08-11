<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
</head>

<body>
    <header>
        <div style="display: flex; align-items: center; justify-content: flex-start; max-width: 900px; margin: 0 auto;">
            <img src="img/3-removebg-preview.png" alt="Logo"
                style="width: 100px; height: auto; margin-bottom: 0; margin-right: 24px;">
            <div style="flex: 1; text-align: left;">
                <h1 style="
    margin-bottom: 6px;
    font-size: 2.6rem;
    font-weight: 700;
    letter-spacing: 2px;
    color: #000000;
    text-shadow: 1px 2px 8px #004aad22;
    font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif;
">
                    MGB Contabilidad
                    <span
                        style="display:block; font-size:1.1rem; font-weight:400; color:#000000; letter-spacing:1px; margin-top:8px;">
                        Soluciones profesionales para tu empresa
                    </span>
                </h1>
                <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
            </div>
        </div>
    </header>
    <main>
        <section id="apartado1">
            <h2>Menú Principal</h2>
            <div class="botones-grid">
                <button onclick="window.location.href='registro_empresa.php'">
                    Registro de Empresa
                </button>

                <button onclick="window.location.href='balance_inicial.php'">
                    Balance General Inicial
                </button>

                <button onclick="window.location.href='diario_general.php'">
                    Diario General
                </button>

                <button onclick="window.location.href='balanza_comprobacion.php'">
                    Balanza de Comprobación
                </button>

                <button onclick="window.location.href='estado_resultados.php'">
                    Estado de Resultados
                </button>

                <button onclick="window.location.href='estadisticas_ok.php'" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    📊 Estadísticas 3D
                </button>

                <button onclick="window.location.href='demo_estadisticas_3d.php'" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%); color: white;">
                    🚀 Demo Estadísticas 3D
                </button>

                <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] == 'admin'): ?>
                    <button onclick="window.location.href='usuarios.php'">
                        Gestión de Usuarios
                    </button>
                <?php endif; ?>

                <button onclick="window.location.href='logout.php'" style="background:#e53935;color:#fff;">
                    Cerrar Sesión
                </button>
                                <button onclick="window.location.href='../servicios.html'" class="btn btn-primary">
                    SERVICIOS
                </button>
            </div>
        </section>
    </main>
</body>

</html>