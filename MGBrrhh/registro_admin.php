<?php
require_once 'conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        // Encriptar la contraseña antes de guardar
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $usuario, $hash);
        if ($stmt->execute()) {
            $mensaje = "Usuario registrado correctamente.";
        } else {
            $mensaje = "Error al registrar el usuario. Puede que el usuario ya exista.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
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
            max-width: 600px;
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
        input {
            width: 100%; padding: 8px 10px; border-radius: 5px;
            border: 1px solid #bfc9d1; margin-top: 5px; font-size: 1em;
        }
        button {
            margin-top: 22px; padding: 10px 0; width: 100%;
            background: #004aad; color: #fff; border: none; border-radius: 5px;
            font-size: 1.08em; font-weight: 600; cursor: pointer;
        }
        @media (max-width: 700px) {
            main { padding: 0 0.2rem 1rem 0.2rem; }
            .form-container { padding: 1.2rem 0.5rem 1rem 0.5rem; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="IMG/logo.png" alt="Logo MGB">
        </div>
        <h1>Registrar Usuario</h1>
        <nav>
            <a href="index.html">Recursos Humanos</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Formulario de registro de usuario</h2>
            <?php if ($mensaje): ?>
                <div style="color:<?= strpos($mensaje, 'correctamente') !== false ? 'green' : 'red' ?>;margin-bottom:1em;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Registrar</button>
            </form>
        </div>
    </main>
</body>
</html>