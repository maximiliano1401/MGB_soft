<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'php/conexion.php';

// Eliminar usuario
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($id != $_SESSION['usuario_id']) { // Evita que el admin se elimine a sí mismo
        $conn->query("DELETE FROM usuarios WHERE id=$id");
    }
    header("Location: usuarios.php");
    exit;
}

// Obtener datos para edición
$edit = false;
$usuario_edit = null;
if (isset($_GET['editar'])) {
    $edit = true;
    $id_edit = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM usuarios WHERE id=$id_edit");
    $usuario_edit = $res->fetch_assoc();
}

// Procesar formulario (alta o edición)
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $email = $conn->real_escape_string($_POST['email']);
    $rol = $conn->real_escape_string($_POST['rol']);

    if (isset($_POST['id_edit']) && $_POST['id_edit'] != "") {
        // Edición (opcionalmente actualizar contraseña)
        $id_edit = intval($_POST['id_edit']);
        $set_password = "";
        if (!empty($_POST['password'])) {
            $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $set_password = ", password='$password_hash'";
        }
        $sql = "UPDATE usuarios SET nombre='$nombre', email='$email', rol='$rol' $set_password WHERE id=$id_edit";
        if ($conn->query($sql) === TRUE) {
            $mensaje = "Usuario actualizado correctamente.";
        } else {
            $mensaje = "Error al actualizar: " . $conn->error;
        }
    } else {
        // Alta
        $password = $_POST['password'];
        if (strlen($password) < 4) {
            $mensaje = "La contraseña debe tener al menos 4 caracteres.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nombre, email, password, rol)
                    VALUES ('$nombre', '$email', '$password_hash', '$rol')";
            if ($conn->query($sql) === TRUE) {
                $mensaje = "Usuario registrado correctamente.";
            } else {
                $mensaje = "Error: " . $conn->error;
            }
        }
    }
    header("Location: usuarios.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Consultar usuarios existentes
$usuarios = $conn->query("SELECT * FROM usuarios ORDER BY nombre");

// Mensaje por GET
if (isset($_GET['mensaje'])) $mensaje = $_GET['mensaje'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios | MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #f6f8fa;
        color: #222;
        margin: 0;
        padding: 0;
    }
    h2 {
        text-align: center;
        margin-top: 32px;
        font-size: 2.2em;
        letter-spacing: 1px;
        color: #004aad;
        text-shadow: 0 2px 8px #e3eefd;
    }
    h3 {
        text-align: center;
        margin-top: 24px;
        color: #34495e;
        font-weight: 500;
    }
    .mensaje {
        margin: 18px auto;
        color: #155724;
        background: linear-gradient(90deg, #d4edda 80%, #e3fcec 100%);
        border: 1.5px solid #c3e6cb;
        border-radius: 8px;
        width: 60%;
        padding: 14px 0;
        text-align: center;
        font-size: 1.13em;
        box-shadow: 0 2px 8px rgba(44,62,80,0.07);
    }
    form {
        background: #fff;
        max-width: 440px;
        margin: 38px auto 28px auto;
        padding: 32px 36px 22px 36px;
        border-radius: 14px;
        box-shadow: 0 4px 18px rgba(44,62,80,0.11);
        border: 1.5px solid #e3eefd;
    }
    label {
        display: block;
        margin-top: 18px;
        font-weight: 600;
        color: #2c3e50;
        letter-spacing: 0.2px;
    }
    input, select {
        width: 100%;
        padding: 10px 12px;
        margin-top: 6px;
        border-radius: 6px;
        border: 1.5px solid #bfc9d1;
        font-size: 1.07em;
        background: #f9fbfc;
        color: #222;
        transition: border 0.2s, box-shadow 0.2s;
    }
    input:focus, select:focus {
        border-color: #004aad;
        outline: none;
        box-shadow: 0 0 0 2px #e3eefd;
    }
    button[type="submit"] {
        margin-top: 26px;
        padding: 12px 28px;
        background: linear-gradient(90deg, #004aad 80%, #2563eb 100%);
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 1.13em;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s, box-shadow 0.2s;
        box-shadow: 0 2px 8px rgba(0,74,173,0.09);
        letter-spacing: 0.5px;
    }
    button[type="submit"]:hover {
        background: linear-gradient(90deg, #003366 80%, #2563eb 100%);
        box-shadow: 0 4px 16px rgba(0,74,173,0.14);
    }
    form a {
        color: #004aad;
        text-decoration: none;
        font-weight: 500;
        margin-left: 18px;
        padding: 8px 16px;
        border-radius: 5px;
        border: 1.5px solid #e3eefd;
        background: #f6f8fa;
        transition: background 0.2s, color 0.2s, border 0.2s;
    }
    form a:hover {
        background: #004aad;
        color: #fff;
        border-color: #004aad;
    }
    table {
        border-collapse: separate;
        border-spacing: 0;
        width: 94%;
        margin: 36px auto 0 auto;
        background: #fff;
        box-shadow: 0 4px 18px rgba(44,62,80,0.09);
        border-radius: 12px;
        overflow: hidden;
        border: 1.5px solid #e3eefd;
    }
    th, td {
        padding: 14px 12px;
        border-bottom: 1.5px solid #e5e9f2;
        text-align: left;
        font-size: 1.04em;
    }
    th {
        background: linear-gradient(90deg, #004aad 80%, #2563eb 100%);
        color: #fff;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    tr:last-child td {
        border-bottom: none;
    }
    tr:nth-child(even) td {
        background: #f4f6fa;
    }
    .acciones a {
        margin-right: 12px;
        color: #004aad;
        font-weight: 600;
        text-decoration: none;
        padding: 6px 14px;
        border-radius: 5px;
        border: 1.5px solid transparent;
        transition: background 0.2s, color 0.2s, border 0.2s;
    }
    .acciones a:last-child {
        color: #c0392b;
        border: 1.5px solid #c0392b;
        background: #fff0f0;
    }
    .acciones a:hover {
        background: #e3eefd;
        color: #003366;
        border-color: #004aad;
    }
    .acciones a:last-child:hover {
        background: #c0392b;
        color: #fff;
        border-color: #c0392b;
    }
    /* Botón volver mejorado */
    .volver-btn {
        display: inline-block;
        margin: 38px auto 0 auto;
        padding: 13px 38px;
        background: linear-gradient(90deg, #004aad 80%, #2563eb 100%);
        color: #fff;
        border: none;
        border-radius: 32px;
        font-size: 1.18em;
        font-weight: 700;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 4px 18px rgba(0,74,173,0.13);
        letter-spacing: 0.7px;
        transition: background 0.2s, box-shadow 0.2s, color 0.2s;
    }
    .volver-btn:hover {
        background: linear-gradient(90deg, #003366 80%, #2563eb 100%);
        color: #e3eefd;
        box-shadow: 0 8px 28px rgba(0,74,173,0.18);
        text-decoration: none;
    }
    @media (max-width: 900px) {
        table { width: 99%; font-size: 0.98em; }
        form { max-width: 98%; padding: 18px 8px; }
        .volver-btn { width: 98%; font-size: 1em; padding: 12px 0; }
    }
    </style>
    <script>
    function validarUsuario() {
        let nombre = document.getElementById('nombre').value.trim();
        let email = document.getElementById('email').value.trim();
        let password = document.getElementById('password').value;
        if (nombre === "" || email === "") {
            alert("Nombre y email son obligatorios.");
            return false;
        }
        <?php if (!$edit): ?>
        if (password === "" || password.length < 4) {
            alert("La contraseña debe tener al menos 4 caracteres.");
            return false;
        }
        <?php endif; ?>
        return true;
    }
    function confirmarEliminacion() {
        return confirm("¿Seguro que deseas eliminar este usuario?");
    }
    </script>
</head>
<body>
    <h2>Gestión de Usuarios</h2>
    <?php if ($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <form method="post" onsubmit="return validarUsuario();">
        <input type="hidden" name="id_edit" value="<?= $edit && $usuario_edit ? $usuario_edit['id'] : '' ?>">
        <label>Nombre:
            <input type="text" name="nombre" id="nombre" required value="<?= $edit && $usuario_edit ? htmlspecialchars($usuario_edit['nombre']) : '' ?>">
        </label>
        <label>Email:
            <input type="email" name="email" id="email" required value="<?= $edit && $usuario_edit ? htmlspecialchars($usuario_edit['email']) : '' ?>">
        </label>
        <label>Contraseña:
            <input type="password" name="password" id="password" <?= $edit ? '' : 'required' ?> placeholder="<?= $edit ? 'Dejar en blanco para no cambiar' : '' ?>">
        </label>
        <label>Rol:
            <select name="rol" required>
                <option value="usuario" <?= $edit && $usuario_edit && $usuario_edit['rol']=='usuario' ? 'selected' : '' ?>>Usuario</option>
                <option value="admin" <?= $edit && $usuario_edit && $usuario_edit['rol']=='admin' ? 'selected' : '' ?>>Administrador</option>
            </select>
        </label>
        <button type="submit"><?= $edit ? 'Actualizar Usuario' : 'Registrar Usuario' ?></button>
        <?php if ($edit): ?>
            <a href="usuarios.php" style="margin-left:16px;">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <h3>Usuarios Registrados</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Email</th>
            <th>Rol</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $usuarios->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= ucfirst($row['rol']) ?></td>
            <td class="acciones">
                <a href="usuarios.php?editar=<?= $row['id'] ?>">Editar</a>
                <?php if ($row['id'] != $_SESSION['usuario_id']): ?>
                <a href="usuarios.php?eliminar=<?= $row['id'] ?>" onclick="return confirmarEliminacion();">Eliminar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p style="text-align:center;">
        <a href="index.php" class="volver-btn">Volver al menú principal</a>
    </p>
</body>
</html>