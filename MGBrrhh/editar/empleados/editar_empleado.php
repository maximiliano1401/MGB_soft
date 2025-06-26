<?php
// filepath: c:\xampp\htdocs\xampp\angel\recursos humanos\editar\empleados\editar_empleado.php
require_once '../../conexion.php';

$mensaje = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Obtener departamentos y puestos para los selects
$departamentos = [];
$result = $conn->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $departamentos[] = $row;
}

$puestos = [];
$result = $conn->query("SELECT id, nombre, departamento_id FROM puestos ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $puestos[] = $row;
}

// Obtener datos del empleado
if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM empleados WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $empleado = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$empleado) {
        header("Location: ../../Ver_Registrados/ver_Registro_Empleados.php");
        exit;
    }
} else {
    header("Location: ../../Ver_Registrados/ver_Registro_Empleados.php");
    exit;
}

// Actualizar datos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido_paterno = trim($_POST['apellido_paterno'] ?? '');
    $apellido_materno = trim($_POST['apellido_materno'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $lugar_nacimiento = trim($_POST['lugar_nacimiento'] ?? '');
    $imss = trim($_POST['imss'] ?? '');
    $rfc = trim($_POST['rfc'] ?? '');
    $curp = trim($_POST['curp'] ?? '');
    $departamento_id = intval($_POST['departamento'] ?? 0);
    $puesto_id = intval($_POST['puesto'] ?? 0);

    if (
        $nombre === '' || $apellido_paterno === '' || $apellido_materno === '' ||
        $fecha_nacimiento === '' || $sexo === '' || $lugar_nacimiento === '' ||
        $imss === '' || $rfc === '' || $curp === '' || !$departamento_id || !$puesto_id
    ) {
        $mensaje = "Todos los campos son obligatorios.";
    } else {
        $stmt = $conn->prepare("UPDATE empleados SET nombre=?, apellido_paterno=?, apellido_materno=?, fecha_nacimiento=?, sexo=?, lugar_nacimiento=?, imss=?, rfc=?, curp=?, departamento_id=?, puesto_id=? WHERE id=?");
        $stmt->bind_param("ssssssssiiii", $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $sexo, $lugar_nacimiento, $imss, $rfc, $curp, $departamento_id, $puesto_id, $id);
        if ($stmt->execute()) {
            $mensaje = "Empleado actualizado correctamente.";
            // Recargar datos actualizados
            $empleado = [
                'nombre' => $nombre,
                'apellido_paterno' => $apellido_paterno,
                'apellido_materno' => $apellido_materno,
                'fecha_nacimiento' => $fecha_nacimiento,
                'sexo' => $sexo,
                'lugar_nacimiento' => $lugar_nacimiento,
                'imss' => $imss,
                'rfc' => $rfc,
                'curp' => $curp,
                'departamento_id' => $departamento_id,
                'puesto_id' => $puesto_id
            ];
        } else {
            $mensaje = "Error al actualizar el empleado.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Empleado</title>
    <link rel="stylesheet" href="../../../css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        main { max-width: 600px; margin: 2.5rem auto 0 auto; padding: 0 1rem 2rem 1rem; }
        .form-container { background: #fff; border-radius: 14px; box-shadow: 0 2px 12px rgba(44,62,80,0.08); padding: 2rem 1.5rem 1.5rem 1.5rem; }
        h2 { color: #005baa; margin-top: 0; }
        label { display: block; margin-top: 14px; font-weight: 500; }
        input, select { width: 100%; padding: 8px 10px; border-radius: 5px; border: 1px solid #bfc9d1; margin-top: 5px; font-size: 1em; }
        button { margin-top: 22px; padding: 10px 0; width: 100%; background: #004aad; color: #fff; border: none; border-radius: 5px; font-size: 1.08em; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <main>
        <div class="form-container">
            <h2>Editar Empleado</h2>
            <?php if ($mensaje): ?>
                <div style="color:<?= strpos($mensaje, 'correctamente') !== false ? 'green' : 'red' ?>;margin-bottom:1em;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($empleado['nombre']) ?>" required>

                <label for="apellido_paterno">Apellido paterno</label>
                <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?= htmlspecialchars($empleado['apellido_paterno']) ?>" required>

                <label for="apellido_materno">Apellido materno</label>
                <input type="text" id="apellido_materno" name="apellido_materno" value="<?= htmlspecialchars($empleado['apellido_materno']) ?>" required>

                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?= htmlspecialchars($empleado['fecha_nacimiento']) ?>" required>

                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecciona</option>
                    <option value="M" <?= $empleado['sexo'] == 'M' ? 'selected' : '' ?>>Masculino</option>
                    <option value="F" <?= $empleado['sexo'] == 'F' ? 'selected' : '' ?>>Femenino</option>
                </select>

                <label for="lugar_nacimiento">Lugar de nacimiento</label>
                <input type="text" id="lugar_nacimiento" name="lugar_nacimiento" value="<?= htmlspecialchars($empleado['lugar_nacimiento']) ?>" required>

                <label for="imss">IMSS</label>
                <input type="text" id="imss" name="imss" value="<?= htmlspecialchars($empleado['imss']) ?>" required>

                <label for="rfc">RFC</label>
                <input type="text" id="rfc" name="rfc" value="<?= htmlspecialchars($empleado['rfc']) ?>" required>

                <label for="curp">CURP</label>
                <input type="text" id="curp" name="curp" value="<?= htmlspecialchars($empleado['curp']) ?>" required>

                <label for="departamento">Departamento</label>
                <select id="departamento" name="departamento" required>
                    <option value="">Selecciona un departamento</option>
                    <?php foreach ($departamentos as $dep): ?>
                        <option value="<?= $dep['id'] ?>" <?= $empleado['departamento_id'] == $dep['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dep['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="puesto">Puesto</label>
                <select id="puesto" name="puesto" required>
                    <option value="">Selecciona un puesto</option>
                    <?php foreach ($puestos as $p): ?>
                        <?php if ($p['departamento_id'] == $empleado['departamento_id']): ?>
                            <option value="<?= $p['id'] ?>" <?= $empleado['puesto_id'] == $p['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['nombre']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Actualizar</button>
            </form>
        </div>
    </main>
    <script>
    // Filtra los puestos según el departamento seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        var puestos = <?php echo json_encode($puestos); ?>;
        var selectDepartamento = document.getElementById('departamento');
        var selectPuesto = document.getElementById('puesto');

        function filtrarPuestos() {
            var depId = selectDepartamento.value;
            selectPuesto.innerHTML = '<option value="">Selecciona un puesto</option>';
            puestos.forEach(function(puesto) {
                if (puesto.departamento_id == depId) {
                    var option = document.createElement('option');
                    option.value = puesto.id;
                    option.textContent = puesto.nombre;
                    if (<?= $empleado['puesto_id'] ?> == puesto.id) {
                        option.selected = true;
                    }
                    selectPuesto.appendChild(option);
                }
            });
        }

        selectDepartamento.addEventListener('change', filtrarPuestos);
        // Inicializa los puestos al cargar
        filtrarPuestos();
    });