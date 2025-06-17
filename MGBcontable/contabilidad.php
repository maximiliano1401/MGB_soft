<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'php/conexion.php';

// Eliminar asiento
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM asientos_contables WHERE id=$id");
    header("Location: contabilidad.php");
    exit;
}

// Obtener datos para edición
$edit = false;
$asiento_edit = null;
if (isset($_GET['editar'])) {
    $edit = true;
    $id_edit = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM asientos_contables WHERE id=$id_edit");
    $asiento_edit = $res->fetch_assoc();
}

// Procesar formulario (alta o edición)
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cuenta'])) {
    $fecha = $conn->real_escape_string($_POST['fecha']);
    $descripcion = $conn->real_escape_string($_POST['descripcion']);
    $monto = floatval($_POST['monto']);
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $cuenta = $conn->real_escape_string($_POST['cuenta']);

    if (isset($_POST['id_edit']) && $_POST['id_edit'] != "") {
        // Edición
        $id_edit = intval($_POST['id_edit']);
        $sql = "UPDATE asientos_contables SET fecha='$fecha', descripcion='$descripcion', monto=$monto, tipo='$tipo', cuenta='$cuenta' WHERE id=$id_edit";
        if ($conn->query($sql) === TRUE) {
            $mensaje = "Asiento actualizado correctamente.";
        } else {
            $mensaje = "Error al actualizar: " . $conn->error;
        }
    } else {
        // Alta
        $sql = "INSERT INTO asientos_contables (fecha, descripcion, monto, tipo, cuenta)
                VALUES ('$fecha', '$descripcion', $monto, '$tipo', '$cuenta')";
        if ($conn->query($sql) === TRUE) {
            $mensaje = "Asiento registrado correctamente.";
        } else {
            $mensaje = "Error: " . $conn->error;
        }
    }
    header("Location: contabilidad.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Consultar asientos existentes
$asientos = $conn->query("SELECT * FROM asientos_contables ORDER BY fecha DESC, id DESC");

// Mensaje por GET
if (isset($_GET['mensaje'])) $mensaje = $_GET['mensaje'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contabilidad General | MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
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
            color: #2c3e50;
        }
        h3 {
            text-align: center;
            margin-top: 18px;
            color: #34495e;
            font-weight: 500;
        }
        .mensaje {
            margin: 18px auto;
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 6px;
            width: 60%;
            padding: 12px 0;
            text-align: center;
            font-size: 1.08em;
        }
        form {
            background: #fff;
            max-width: 520px;
            margin: 32px auto 24px auto;
            padding: 28px 32px 18px 32px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(44,62,80,0.09);
        }
        label {
            display: block;
            margin-top: 16px;
            font-weight: 500;
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 8px 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #bfc9d1;
            font-size: 1em;
            background: #f9fbfc;
            color: #222;
            transition: border 0.2s;
        }
        input:focus, select:focus {
            border-color: #2980b9;
            outline: none;
        }
        button {
            margin-top: 22px;
            padding: 10px 24px;
            background: #34495e;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.08em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #2c3e50;
        }
        a {
            color: #2980b9;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 92%;
            margin: 30px auto 0 auto;
            background: #fff;
            box-shadow: 0 2px 12px rgba(44,62,80,0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e9f2;
            text-align: left;
            font-size: 1em;
        }
        th {
            background: #34495e;
            color: #fff;
            font-weight: 600;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) td {
            background: #f4f6fa;
        }
        .acciones a {
            margin-right: 10px;
            color: #34495e;
            font-weight: 500;
        }
        .acciones a:last-child {
            color: #c0392b;
        }
        @media (max-width: 900px) {
            table { width: 99%; font-size: 0.98em; }
            form { max-width: 98%; padding: 18px 8px; }
        }
    </style>
    </style>
    <script>
    function validarAsiento() {
        let monto = document.getElementById('monto').value.trim();
        let cuenta = document.getElementById('cuenta').value.trim();
        if (cuenta === "") {
            alert("La cuenta es obligatoria.");
            return false;
        }
        if (isNaN(monto) || parseFloat(monto) <= 0) {
            alert("El monto debe ser un número positivo.");
            return false;
        }
        return true;
    }
    function confirmarEliminacion() {
        return confirm("¿Seguro que deseas eliminar este asiento?");
    }
    </script>
</head>
<body>
    <h2>Contabilidad General - Asientos Contables</h2>
    <?php if ($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <form method="post" onsubmit="return validarAsiento();">
        <input type="hidden" name="id_edit" value="<?= $edit && $asiento_edit ? $asiento_edit['id'] : '' ?>">
        <label>Fecha:
            <input type="date" name="fecha" required value="<?= $edit && $asiento_edit ? $asiento_edit['fecha'] : date('Y-m-d') ?>">
        </label>
        <label>Descripción:
            <input type="text" name="descripcion" value="<?= $edit && $asiento_edit ? htmlspecialchars($asiento_edit['descripcion']) : '' ?>">
        </label>
        <label>Monto:
            <input type="number" step="0.01" min="0" name="monto" id="monto" required value="<?= $edit && $asiento_edit ? $asiento_edit['monto'] : '' ?>">
        </label>
        <label>Tipo:
            <select name="tipo" required>
                <option value="debe" <?= $edit && $asiento_edit && $asiento_edit['tipo']=='debe' ? 'selected' : '' ?>>Debe</option>
                <option value="haber" <?= $edit && $asiento_edit && $asiento_edit['tipo']=='haber' ? 'selected' : '' ?>>Haber</option>
            </select>
        </label>
        <label>Cuenta:
            <input type="text" name="cuenta" id="cuenta" required value="<?= $edit && $asiento_edit ? htmlspecialchars($asiento_edit['cuenta']) : '' ?>">
        </label>
        <button type="submit"><?= $edit ? 'Actualizar Asiento' : 'Registrar Asiento' ?></button>
        <?php if ($edit): ?>
            <a href="contabilidad.php" style="margin-left:16px;">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <h3>Asientos registrados</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th>Monto</th>
            <th>Tipo</th>
            <th>Cuenta</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $asientos->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['fecha'] ?></td>
            <td><?= htmlspecialchars($row['descripcion']) ?></td>
            <td><?= number_format($row['monto'], 2) ?></td>
            <td><?= ucfirst($row['tipo']) ?></td>
            <td><?= htmlspecialchars($row['cuenta']) ?></td>
            <td class="acciones">
                <a href="contabilidad.php?editar=<?= $row['id'] ?>">Editar</a>
                <a href="contabilidad.php?eliminar=<?= $row['id'] ?>" onclick="return confirmarEliminacion();">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>