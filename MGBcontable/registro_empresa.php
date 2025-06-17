<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'php/conexion.php';

$mensaje = "";

// Buscar empresas
$filtro = "";
if (isset($_GET['buscar']) && $_GET['buscar'] !== "") {
    $busqueda = $conn->real_escape_string($_GET['buscar']);
    $filtro = "WHERE nombre LIKE '%$busqueda%' OR rfc LIKE '%$busqueda%'";
}

// Validar antes de eliminar empresa
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $tiene_balances = $conn->query("SELECT id FROM balance_inicial WHERE empresa_id = $id LIMIT 1")->num_rows > 0;
    $tiene_asientos = $conn->query("SELECT id FROM asientos_contables WHERE empresa_id = $id LIMIT 1")->num_rows > 0;

    if ($tiene_balances || $tiene_asientos) {
        $mensaje = "No se puede eliminar: la empresa tiene registros contables.";
    } else {
        $conn->query("DELETE FROM empresas WHERE id = $id");
        $mensaje = "Empresa eliminada correctamente.";
    }
    header("Location: registro_empresa.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Editar empresa
$edit = false;
$empresa_edit = null;
if (isset($_GET['editar'])) {
    $edit = true;
    $id_edit = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM empresas WHERE id = $id_edit");
    $empresa_edit = $res->fetch_assoc();
}

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre'])) {
    $nombre = $conn->real_escape_string($_POST['nombre']);
    $rfc = $conn->real_escape_string($_POST['rfc']);
    $direccion = $conn->real_escape_string($_POST['direccion']);
    $telefono = $conn->real_escape_string($_POST['telefono']);

    if (!empty($_POST['id_edit'])) {
        $id_edit = intval($_POST['id_edit']);
        $conn->query("UPDATE empresas SET nombre='$nombre', rfc='$rfc', direccion='$direccion', telefono='$telefono' WHERE id=$id_edit");
        $mensaje = "Empresa actualizada correctamente.";
    } else {
        $conn->query("INSERT INTO empresas (nombre, rfc, direccion, telefono) VALUES ('$nombre', '$rfc', '$direccion', '$telefono')");
        $mensaje = "Empresa registrada correctamente.";
    }

    header("Location: registro_empresa.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Obtener empresas (con filtro si aplica)
$empresas = $conn->query("SELECT * FROM empresas $filtro ORDER BY nombre");

// Mensaje
if (isset($_GET['mensaje'])) $mensaje = $_GET['mensaje'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Empresas | MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
       <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #f6f8fa 60%, #e3eefd 100%);
            color: #222;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        h2 {
            text-align: center;
            margin-top: 38px;
            font-size: 2.5em;
            letter-spacing: 1.5px;
            color: #004aad;
            text-shadow: 0 2px 8px #e3eefd;
        }
        h3 {
            text-align: center;
            margin-top: 32px;
            color: #34495e;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .mensaje {
            margin: 22px auto;
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
        .buscar-form {
            max-width: 520px;
            margin: 0 auto 28px auto;
            display: flex;
            gap: 10px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(44,62,80,0.07);
            padding: 12px 16px;
        }
        .buscar-form input {
            flex: 1;
            padding: 9px 14px;
            border-radius: 6px;
            border: 1.5px solid #bfc9d1;
            font-size: 1.07em;
            background: #f9fbfc;
            color: #222;
            transition: border 0.2s, box-shadow 0.2s;
        }
        .buscar-form input:focus {
            border-color: #004aad;
            outline: none;
            box-shadow: 0 0 0 2px #e3eefd;
        }
        .buscar-form button {
            padding: 9px 22px;
            background: linear-gradient(90deg, #004aad 80%, #2563eb 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.07em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 6px rgba(0,74,173,0.08);
        }
        .buscar-form button:hover {
            background: linear-gradient(90deg, #003366 80%, #2563eb 100%);
            box-shadow: 0 4px 12px rgba(0,74,173,0.13);
        }
        .buscar-form a {
            padding: 9px 18px;
            background: #e3eefd;
            color: #004aad;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            border: 1.5px solid #bfc9d1;
            transition: background 0.2s, color 0.2s, border 0.2s;
        }
        .buscar-form a:hover {
            background: #004aad;
            color: #fff;
            border-color: #004aad;
        }
        form[method="post"] {
            background: #fff;
            max-width: 540px;
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
        input, textarea {
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
        input:focus, textarea:focus {
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
        form[method="post"] a {
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
        form[method="post"] a:hover {
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
        p a.volver-btn {
            margin: 38px auto 0 auto;
        }
        @media (max-width: 900px) {
            table { width: 99%; font-size: 0.98em; }
            form[method="post"] { max-width: 98%; padding: 18px 8px; }
            .buscar-form { max-width: 98%; }
            .volver-btn { width: 98%; font-size: 1em; padding: 12px 0; }
        }
    </style>
    <script>
    function validarEmpresa() {
        let nombre = document.getElementById('nombre').value.trim();
        if (nombre === "") {
            alert("El nombre de la empresa es obligatorio.");
            return false;
        }
        return true;
    }

    function confirmarEliminacion() {
        return confirm("¿Estás seguro de eliminar esta empresa? Esta acción no se puede deshacer.");
    }
    </script>
</head>
<body>
    <h2>Registro de Empresas</h2>

    <?php if ($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>

    <!-- Formulario de búsqueda -->
    <form class="buscar-form" method="get" action="registro_empresa.php">
        <input type="text" name="buscar" placeholder="Buscar por nombre o RFC" value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
        <button type="submit">Buscar</button>
        <a href="registro_empresa.php" style="padding: 6px 12px; background: #ccc; text-decoration: none;">Limpiar</a>
    </form>

    <!-- Formulario registro/edición -->
    <form method="post" onsubmit="return validarEmpresa();">
        <input type="hidden" name="id_edit" value="<?= $edit && $empresa_edit ? $empresa_edit['id'] : '' ?>">
        <label>Nombre:
            <input type="text" name="nombre" id="nombre" required value="<?= $edit && $empresa_edit ? htmlspecialchars($empresa_edit['nombre']) : '' ?>">
        </label>
        <label>RFC:
            <input type="text" name="rfc" value="<?= $edit && $empresa_edit ? htmlspecialchars($empresa_edit['rfc']) : '' ?>">
        </label>
        <label>Dirección:
            <textarea name="direccion" rows="3"><?= $edit && $empresa_edit ? htmlspecialchars($empresa_edit['direccion']) : '' ?></textarea>
        </label>
        <label>Teléfono:
            <input type="text" name="telefono" value="<?= $edit && $empresa_edit ? htmlspecialchars($empresa_edit['telefono']) : '' ?>">
        </label>
        <button type="submit"><?= $edit ? 'Actualizar Empresa' : 'Registrar Empresa' ?></button>
        <?php if ($edit): ?>
            <a href="registro_empresa.php" style="margin-left:16px;">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <h3>Empresas Registradas</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>RFC</th>
            <th>Teléfono</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $empresas->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nombre']) ?></td>
            <td><?= htmlspecialchars($row['rfc']) ?></td>
            <td><?= htmlspecialchars($row['telefono']) ?></td>
            <td class="acciones">
                <a href="registro_empresa.php?editar=<?= $row['id'] ?>">Editar</a>
                <a href="registro_empresa.php?eliminar=<?= $row['id'] ?>" onclick="return confirmarEliminacion();">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <p><a href="index.php" class="volver-btn">Volver al menú principal</a></p>
</body>
</html>
