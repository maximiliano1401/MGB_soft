<?php
require_once 'auth.php';
require_once 'conexion.php';

$mensaje = "";
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
        $stmt = $conn->prepare("INSERT INTO empleados (nombre, apellido_paterno, apellido_materno, fecha_nacimiento, sexo, lugar_nacimiento, imss, rfc, curp, departamento_id, puesto_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssiii", $nombre, $apellido_paterno, $apellido_materno, $fecha_nacimiento, $sexo, $lugar_nacimiento, $imss, $rfc, $curp, $departamento_id, $puesto_id);
        if ($stmt->execute()) {
            $empleado_id = $conn->insert_id;
            
            // Registrar automáticamente el alta del empleado
            $fecha_alta = date('Y-m-d');
            $stmt_alta = $conn->prepare("INSERT INTO altas_bajas (empleado_id, fecha_alta, estado) VALUES (?, ?, 'activo')");
            $stmt_alta->bind_param("is", $empleado_id, $fecha_alta);
            $stmt_alta->execute();
            $stmt_alta->close();
            
            $mensaje = "Empleado registrado y dado de alta correctamente.";
        } else {
            $mensaje = "Error al registrar el empleado.";
        }
        $stmt->close();
    }
}

// Obtener departamentos y puestos desde la base de datos
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Empleado</title>
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
        input, select {
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
        <h1>Registrar Empleado</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="Ver_Registrados/ver_Registro_Empleados.php">Ver Empleados</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Formulario de registro de empleado</h2>
            <?php if ($mensaje): ?>
                <div style="color:<?= strpos($mensaje, 'correctamente') !== false ? 'green' : 'red' ?>;margin-bottom:1em;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>

                <label for="apellido_paterno">Apellido paterno</label>
                <input type="text" id="apellido_paterno" name="apellido_paterno" required>

                <label for="apellido_materno">Apellido materno</label>
                <input type="text" id="apellido_materno" name="apellido_materno" required>

                <label for="fecha_nacimiento">Fecha de nacimiento</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>

                <label for="sexo">Sexo</label>
                <select id="sexo" name="sexo" required>
                    <option value="">Selecciona</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>

                <label for="lugar_nacimiento">Lugar de nacimiento</label>
                <input type="text" id="lugar_nacimiento" name="lugar_nacimiento" required>

                <label for="imss">IMSS</label>
                <input type="text" id="imss" name="imss" required>

                <label for="rfc">RFC</label>
                <input type="text" id="rfc" name="rfc" required>

                <label for="curp">CURP</label>
                <input type="text" id="curp" name="curp" required>

                <label for="departamento">Departamento</label>
                <select id="departamento" name="departamento" required>
                    <option value="">Selecciona un departamento</option>
                    <?php foreach ($departamentos as $dep): ?>
                        <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="puesto">Puesto</label>
                <select id="puesto" name="puesto" required>
                    <option value="">Selecciona un puesto</option>
                </select>

                <button type="submit">Registrar</button>
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
                    selectPuesto.appendChild(option);
                }
            });
        }

        selectDepartamento.addEventListener('change', filtrarPuestos);
        if (selectDepartamento.value) filtrarPuestos();
    });
    </script>
</body>
</html>