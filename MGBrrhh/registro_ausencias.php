<?php
require_once 'conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empleado_id = intval($_POST['empleado_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$empleado_id || $tipo === '' || $fecha_inicio === '' || $fecha_fin === '') {
        $mensaje = "Todos los campos son obligatorios excepto el motivo.";
    } elseif ($fecha_inicio > $fecha_fin) {
        $mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    } else {
        $stmt = $conn->prepare("INSERT INTO ausencias (empleado_id, tipo, fecha_inicio, fecha_fin, motivo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $empleado_id, $tipo, $fecha_inicio, $fecha_fin, $motivo);
        if ($stmt->execute()) {
            $mensaje = "Ausencia registrada correctamente.";
        } else {
            $mensaje = "Error al registrar la ausencia.";
        }
        $stmt->close();
    }
}

// Obtener empleados
$empleados = [];
$result = $conn->query("SELECT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre_completo FROM empleados e ORDER BY e.nombre");
while ($row = $result->fetch_assoc()) {
    $empleados[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Ausencia</title>
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
        input, select, textarea {
            width: 100%; padding: 8px 10px; border-radius: 5px;
            border: 1px solid #bfc9d1; margin-top: 5px; font-size: 1em;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
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
        @media (max-width: 700px) {
            main { padding: 0 0.2rem 1rem 0.2rem; }
            .form-container { padding: 1.2rem 0.5rem 1rem 0.5rem; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="IMG/logo.png" alt="Logo MGB">
        </div>
        <h1>Registrar Ausencia</h1>
        <nav>
            <a href="index.html">Recursos Humanos</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Formulario de registro de ausencia</h2>
            <?php if ($mensaje): ?>
                <div style="color:<?= strpos($mensaje, 'correctamente') !== false ? 'green' : 'red' ?>;margin-bottom:1em;">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>
            <form method="post">
                <label for="empleado_id">Empleado</label>
                <select id="empleado_id" name="empleado_id" required>
                    <option value="">Selecciona un empleado</option>
                    <?php foreach ($empleados as $emp): ?>
                        <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre_completo']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="tipo">Tipo de ausencia</label>
                <select id="tipo" name="tipo" required>
                    <option value="">Selecciona el tipo</option>
                    <option value="vacaciones">Vacaciones</option>
                    <option value="incapacidad">Incapacidad</option>
                    <option value="falta">Falta</option>
                </select>

                <div class="form-row">
                    <div>
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                    </div>
                    <div>
                        <label for="fecha_fin">Fecha de fin</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" required>
                    </div>
                </div>

                <label for="motivo">Motivo (opcional)</label>
                <textarea id="motivo" name="motivo" placeholder="Describe el motivo de la ausencia..."></textarea>

                <button type="submit">Registrar Ausencia</button>
            </form>
        </div>
    </main>
</body>
</html>
