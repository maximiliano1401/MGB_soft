<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'php/conexion.php';

// Eliminar cuenta
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $conn->query("DELETE FROM cuentas WHERE id=$id");
    header("Location: cuentas.php");
    exit;
}

// Obtener datos para edición
$edit = false;
$cuenta_edit = null;
if (isset($_GET['editar'])) {
    $edit = true;
    $id_edit = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM cuentas WHERE id=$id_edit");
    $cuenta_edit = $res->fetch_assoc();
}

// Procesar formulario (alta o edición)
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tipo'])) {
    $tipo = $conn->real_escape_string($_POST['tipo']);
    $tercero = $conn->real_escape_string($_POST['tercero']);
    $fecha = $conn->real_escape_string($_POST['fecha']);
    $monto = floatval($_POST['monto']);
    $concepto = $conn->real_escape_string($_POST['concepto']);
    $pagada = isset($_POST['pagada']) ? 1 : 0;

    if (isset($_POST['id_edit']) && $_POST['id_edit'] != "") {
        // Edición
        $id_edit = intval($_POST['id_edit']);
        $sql = "UPDATE cuentas SET tipo='$tipo', tercero='$tercero', fecha='$fecha', monto=$monto, concepto='$concepto', pagada=$pagada WHERE id=$id_edit";
        if ($conn->query($sql) === TRUE) {
            $mensaje = "Cuenta actualizada correctamente.";
        } else {
            $mensaje = "Error al actualizar: " . $conn->error;
        }
    } else {
        // Alta
        $sql = "INSERT INTO cuentas (tipo, tercero, fecha, monto, concepto, pagada)
                VALUES ('$tipo', '$tercero', '$fecha', $monto, '$concepto', $pagada)";
        if ($conn->query($sql) === TRUE) {
            $mensaje = "Cuenta registrada correctamente.";
        } else {
            $mensaje = "Error: " . $conn->error;
        }
    }
    header("Location: cuentas.php?mensaje=" . urlencode($mensaje));
    exit;
}

// Consultar cuentas existentes
$cuentas = $conn->query("SELECT * FROM cuentas ORDER BY fecha DESC, id DESC");

// Mensaje por GET
if (isset($_GET['mensaje'])) $mensaje = $_GET['mensaje'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuentas por Pagar y Cobrar | MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        form { margin-bottom: 32px; }
        label { display: block; margin-top: 12px; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; border-radius: 4px; border: 1px solid #bbb; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f4fa; }
        .mensaje { margin: 12px 0; color: green; }
        .acciones a { margin-right: 8px; }
    </style>
    <script>
    function validarCuenta() {
        let tercero = document.getElementById('tercero').value.trim();
        let monto = document.getElementById('monto').value.trim();
        if (tercero === "") {
            alert("El campo tercero es obligatorio.");
            return false;
        }
        if (isNaN(monto) || parseFloat(monto) <= 0) {
            alert("El monto debe ser un número positivo.");
            return false;
        }
        return true;
    }
    function confirmarEliminacion() {
        return confirm("¿Seguro que deseas eliminar esta cuenta?");
    }
    </script>
</head>
<body>
    <h2>Cuentas por Pagar y Cobrar</h2>
    <?php if ($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>
    <form method="post" onsubmit="return validarCuenta();">
        <input type="hidden" name="id_edit" value="<?= $edit && $cuenta_edit ? $cuenta_edit['id'] : '' ?>">
        <label>Tipo:
            <select name="tipo" required>
                <option value="por_pagar" <?= $edit && $cuenta_edit && $cuenta_edit['tipo']=='por_pagar' ? 'selected' : '' ?>>Por Pagar</option>
                <option value="por_cobrar" <?= $edit && $cuenta_edit && $cuenta_edit['tipo']=='por_cobrar' ? 'selected' : '' ?>>Por Cobrar</option>
            </select>
        </label>
        <label>Tercero (Proveedor/Cliente):
            <input type="text" name="tercero" id="tercero" required value="<?= $edit && $cuenta_edit ? htmlspecialchars($cuenta_edit['tercero']) : '' ?>">
        </label>
        <label>Fecha:
            <input type="date" name="fecha" required value="<?= $edit && $cuenta_edit ? $cuenta_edit['fecha'] : date('Y-m-d') ?>">
        </label>
        <label>Monto:
            <input type="number" step="0.01" min="0" name="monto" id="monto" required value="<?= $edit && $cuenta_edit ? $cuenta_edit['monto'] : '' ?>">
        </label>
        <label>Concepto:
            <input type="text" name="concepto" value="<?= $edit && $cuenta_edit ? htmlspecialchars($cuenta_edit['concepto']) : '' ?>">
        </label>
        <label>
            <input type="checkbox" name="pagada" value="1" <?= $edit && $cuenta_edit && $cuenta_edit['pagada'] ? 'checked' : '' ?>> Pagada/Cobrada
        </label>
        <button type="submit"><?= $edit ? 'Actualizar' : 'Registrar' ?></button>
        <?php if ($edit): ?>
            <a href="cuentas.php" style="margin-left:16px;">Cancelar edición</a>
        <?php endif; ?>
    </form>

    <h3>Cuentas registradas</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Tipo</th>
            <th>Tercero</th>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Concepto</th>
            <th>Pagada/Cobrada</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $cuentas->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['tipo'] == 'por_pagar' ? 'Por Pagar' : 'Por Cobrar' ?></td>
            <td><?= htmlspecialchars($row['tercero']) ?></td>
            <td><?= $row['fecha'] ?></td>
            <td><?= number_format($row['monto'], 2) ?></td>
            <td><?= htmlspecialchars($row['concepto']) ?></td>
            <td><?= $row['pagada'] ? 'Sí' : 'No' ?></td>
            <td class="acciones">
                <a href="cuentas.php?editar=<?= $row['id'] ?>">Editar</a>
                <a href="cuentas.php?eliminar=<?= $row['id'] ?>" onclick="return confirmarEliminacion();">Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>