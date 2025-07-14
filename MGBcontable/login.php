<?php
session_start();
include 'php/conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email='$email'";
    $res = $conn->query($sql);
    if ($res && $user = $res->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nombre'] = $user['nombre'];
            $_SESSION['usuario_rol'] = $user['rol'];
            header("Location: index.php");
            exit;
        } else {
            $mensaje = "Contraseña incorrecta.";
        }
    } else {
        $mensaje = "Usuario no encontrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
<style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: linear-gradient(135deg, #e3f0ff 0%, #f6f8fa 100%);
        color: #222;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card {
        display: flex;
        flex-direction: row;
        align-items: stretch;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(44,62,80,0.13);
        overflow: hidden;
        min-width: 420px;
        min-height: 220px;
        max-width: 520px;
    }
    .login-side {
        background: linear-gradient(135deg, #004aad 60%, #1976d2 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 24px 18px;
        min-width: 150px;
    }
    .login-side h2 {
        font-size: 1.3em;
        font-weight: 600;
        margin: 0;
        letter-spacing: 1px;
    }
    .login-side .icon {
        font-size: 2.5em;
        margin-bottom: 12px;
    }
    .login-form-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 24px 22px 18px 22px;
        position: relative;
    }
    .mensaje-error {
        color: #c0392b;
        background: #ffeaea;
        border: 1px solid #e57373;
        border-radius: 6px;
        padding: 7px 0;
        text-align: center;
        margin-bottom: 10px;
        font-size: 0.97em;
        position: relative;
        z-index: 1;
    }
    form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #2c3e50;
        margin-top: 8px;
        font-size: 0.93em;
    }
    input[type="email"], input[type="password"] {
        width: 100%;
        padding: 7px 8px;
        border-radius: 5px;
        border: 1.1px solid #bfc9d1;
        font-size: 0.97em;
        background: #f9fbfc;
        color: #222;
        margin-top: 2px;
        margin-bottom: 2px;
        transition: border 0.2s;
        box-sizing: border-box;
    }
    input[type="email"]:focus, input[type="password"]:focus {
        border-color: #004aad;
        outline: none;
        background: #e3f0ff;
    }
    button[type="submit"] {
        width: 100%;
        padding: 8px 0;
        background: linear-gradient(90deg, #004aad 60%, #1976d2 100%);
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 0.97em;
        font-weight: 600;
        cursor: pointer;
        margin-top: 12px;
        box-shadow: 0 2px 8px #004aad22;
        transition: background 0.2s, box-shadow 0.2s;
    }
    button[type="submit"]:hover {
        background: linear-gradient(90deg, #003366 60%, #1565c0 100%);
        box-shadow: 0 4px 16px #004aad33;
    }
    .login-footer {
        text-align: center;
        margin-top: 10px;
        color: #888;
        font-size: 0.85em;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }
    @media (max-width: 600px) {
        .login-card {
            flex-direction: column;
            min-width: 0;
            max-width: 98vw;
        }
        .login-side {
            min-width: 0;
            flex-direction: row;
            justify-content: flex-start;
            padding: 14px 10px;
        }
        .login-side h2 {
            font-size: 1.1em;
        }
        .login-form-area {
            padding: 16px 8px 10px 8px;
        }
    }
</style>
 
</head>
<body>
<header>
</header>
<div class="login-card">
    <div class="login-side">
    <img src="img/3-removebg-preview.png" alt="Logo"
        style="width: 140px; height: auto; margin-bottom: 18px; display: block;">
        <h2>MGB Contabilidad</h2>
            <a href="../index.html" class="back-btn">
        ← Volver al Inicio
    </a>
    </div>
    <div class="login-form-area">
        <?php if ($mensaje) echo "<div class='mensaje-error'>$mensaje</div>"; ?>
        <form method="post">
            <label>Email:
                <input type="email" name="email" required>
            </label>
            <label>Contraseña:
                <input type="password" name="password" required>
            </label>
            <button type="submit">Entrar</button>
        </form>
        <div style="text-align:center; margin-top:18px;">
            <a href="../contabilidad.html"
               style="display:inline-block;
                      padding:11px 32px;
                      background:linear-gradient(90deg,#004aad 80%,#2563eb 100%);
                      color:#fff;
                      border:none;
                      border-radius:28px;
                      font-size:1.08em;
                      font-weight:600;
                      text-align:center;
                      text-decoration:none;
                      box-shadow:0 2px 8px rgba(0,74,173,0.13);
                      letter-spacing:0.5px;
                      transition:background 0.2s,box-shadow 0.2s,color 0.2s;"
               onmouseover="this.style.background='linear-gradient(90deg,#003366 80%,#2563eb 100%)';this.style.color='#e3eefd';"
               onmouseout="this.style.background='linear-gradient(90deg,#004aad 80%,#2563eb 100%)';this.style.color='#fff';"
            >Volver a la página principal</a>
        </div>
    </div>
</div>
</body>
</html>