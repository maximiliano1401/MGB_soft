<?php
require_once 'auth.php';
require_once 'conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $razon_social = trim($_POST['razon_social'] ?? '');
    $rfc = trim($_POST['rfc'] ?? '');
    $regimen_fiscal = trim($_POST['regimen_fiscal'] ?? '');
    $calle = trim($_POST['calle'] ?? '');
    $numero_ext = trim($_POST['numero_ext'] ?? '');
    $numero_int = trim($_POST['numero_int'] ?? '');
    $colonia = trim($_POST['colonia'] ?? '');
    $estado = trim($_POST['estado'] ?? '');
    $municipio = trim($_POST['municipio'] ?? '');
    $pais = trim($_POST['pais'] ?? '');
    $codigo_postal = trim($_POST['codigo_postal'] ?? '');

    if ($nombre === '' || $razon_social === '' || $rfc === '') {
        $mensaje = "El nombre, razón social y RFC son obligatorios.";
    } else {
        $stmt = $conn->prepare("INSERT INTO empresas (nombre, razon_social, rfc, regimen_fiscal, calle, numero_ext, numero_int, colonia, estado, municipio, pais, codigo_postal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", $nombre, $razon_social, $rfc, $regimen_fiscal, $calle, $numero_ext, $numero_int, $colonia, $estado, $municipio, $pais, $codigo_postal);
        if ($stmt->execute()) {
            $mensaje = "Empresa registrada correctamente.";
        } else {
            $mensaje = "Error al registrar la empresa.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Empresa</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        header {
            background: linear-gradient(120deg, #005baa 60%, #00bcd4 100%);
            color: #fff;
            padding: 2rem 1rem 1rem 1rem;
            text-align: center;
        }
        .logo-container img {
            height: 60px;
            border-radius: 10px;
            background: #fff;
            border: 2px solid #00bcd4;
            margin-bottom: 0.7rem;
        }
        h1 { margin: 0.5rem 0 0.2rem 0; font-size: 2.1rem; letter-spacing: 1px; }
        nav { margin-top: 1rem; }
        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 1.05rem;
            padding: 0.4rem 1.1rem;
            border-radius: 8px;
            background: rgba(0,0,0,0.07);
            margin: 0 0.3rem;
            transition: background 0.2s;
        }
        nav a:hover { background: #00bcd4; }
        main {
            max-width: 800px;
            margin: 2.5rem auto 0 auto;
            padding: 0 1rem 2rem 1rem;
        }
        .form-container {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(44,62,80,0.08);
            padding: 2rem 1.5rem 1.5rem 1.5rem;
        }
        h2 { color: #005baa; margin-top: 0; }
        label { display: block; margin-top: 14px; font-weight: 500; }
        input, select {
            width: 100%; padding: 8px 10px; border-radius: 5px;
            border: 1px solid #bfc9d1; margin-top: 5px; font-size: 1em;
        }
        button {
            margin-top: 22px; padding: 10px 0; width: 100%;
            background: #004aad; color: #fff; border: none; border-radius: 5px;
            font-size: 1.08em; font-weight: 600; cursor: pointer;
        }
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .form-row-3 {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 1rem;
        }
        @media (max-width: 700px) {
            main { padding: 0 0.2rem 1rem 0.2rem; }
            .form-container { padding: 1.2rem 0.5rem 1rem 0.5rem; }
            .form-row, .form-row-3 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="IMG/4.png" alt="Logo MGB - Recursos Humanos">
        </div>
        <h1>Registrar Empresa</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="Ver_Registrados/Ver_Registro_Empresas.php">Ver Empresas</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Formulario de registro de empresa</h2>
            <?php if ($mensaje): ?>
                <div style="color:<?= strpos($mensaje, 'correctamente') !== false ? 'green' : 'red' ?>;margin-bottom:1em;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <div class="form-row">
                    <div>
                        <label for="nombre">Nombre de la empresa *</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </div>
                    <div>
                        <label for="razon_social">Razón social *</label>
                        <input type="text" id="razon_social" name="razon_social" required>
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="rfc">RFC *</label>
                        <input type="text" id="rfc" name="rfc" required maxlength="13">
                    </div>
                    <div>
                        <label for="regimen_fiscal">Régimen fiscal</label>
                        <input type="text" id="regimen_fiscal" name="regimen_fiscal">
                    </div>
                </div>

                <h3 style="color: #005baa; margin-top: 2rem; margin-bottom: 1rem;">Dirección</h3>
                
                <div class="form-row-3">
                    <div>
                        <label for="calle">Calle</label>
                        <input type="text" id="calle" name="calle">
                    </div>
                    <div>
                        <label for="numero_ext">Número exterior</label>
                        <input type="text" id="numero_ext" name="numero_ext">
                    </div>
                    <div>
                        <label for="numero_int">Número interior</label>
                        <input type="text" id="numero_int" name="numero_int">
                    </div>
                </div>

                <div class="form-row">
                    <div>
                        <label for="colonia">Colonia</label>
                        <input type="text" id="colonia" name="colonia">
                    </div>
                    <div>
                        <label for="codigo_postal">Código postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal" maxlength="10">
                    </div>
                </div>

                <div class="form-row-3">
                    <div>
                        <label for="municipio">Municipio</label>
                        <input type="text" id="municipio" name="municipio">
                    </div>
                    <div>
                        <label for="estado">Estado</label>
                        <input type="text" id="estado" name="estado">
                    </div>
                    <div>
                        <label for="pais">País</label>
                        <input type="text" id="pais" name="pais" value="México">
                    </div>
                </div>

                <button type="submit">Registrar Empresa</button>
            </form>
        </div>
    </main>
</body>
</html>
