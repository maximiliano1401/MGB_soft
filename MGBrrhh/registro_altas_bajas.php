<?php
require_once 'conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empleado_id = intval($_POST['empleado_id'] ?? 0);
    $tipo_operacion = $_POST['tipo_operacion'] ?? '';
    $fecha_alta = $_POST['fecha_alta'] ?? '';
    $fecha_baja = $_POST['fecha_baja'] ?? '';
    $causa_baja = trim($_POST['causa_baja'] ?? '');

    if (!$empleado_id || $tipo_operacion === '') {
        $mensaje = "El empleado y tipo de operación son obligatorios.";
    } elseif ($tipo_operacion === 'alta' && $fecha_alta === '') {
        $mensaje = "La fecha de alta es obligatoria.";
    } elseif ($tipo_operacion === 'baja' && ($fecha_baja === '' || $causa_baja === '')) {
        $mensaje = "La fecha de baja y causa son obligatorias.";
    } else {
        if ($tipo_operacion === 'alta') {
            // Verificar si ya existe un registro activo
            $check = $conn->prepare("SELECT id FROM altas_bajas WHERE empleado_id = ? AND estado = 'activo'");
            $check->bind_param("i", $empleado_id);
            $check->execute();
            $result = $check->get_result();
            
            if ($result->num_rows > 0) {
                $mensaje = "Ya existe un registro activo para este empleado.";
            } else {
                $stmt = $conn->prepare("INSERT INTO altas_bajas (empleado_id, fecha_alta, estado) VALUES (?, ?, 'activo')");
                $stmt->bind_param("is", $empleado_id, $fecha_alta);
                if ($stmt->execute()) {
                    $mensaje = "Alta registrada correctamente.";
                } else {
                    $mensaje = "Error al registrar el alta.";
                }
                $stmt->close();
            }
            $check->close();
        } else {
            // Buscar registro activo y actualizarlo
            $stmt = $conn->prepare("UPDATE altas_bajas SET fecha_baja = ?, causa_baja = ?, estado = 'inactivo' WHERE empleado_id = ? AND estado = 'activo'");
            $stmt->bind_param("ssi", $fecha_baja, $causa_baja, $empleado_id);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $mensaje = "Baja registrada correctamente.";
            } else {
                $mensaje = "Error al registrar la baja o no existe un registro activo.";
            }
            $stmt->close();
        }
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
    <title>Altas y Bajas</title>
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
        <h1>Altas y Bajas</h1>
        <nav>
            <a href="index.html">Recursos Humanos</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Gestión de altas y bajas</h2>
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

                <label for="tipo_operacion">Tipo de operación</label>
                <select id="tipo_operacion" name="tipo_operacion" required>
                    <option value="">Selecciona una operación</option>
                    <option value="alta">Alta</option>
                    <option value="baja">Baja</option>
                </select>

                <div id="campos_alta" style="display: none;">
                    <label for="fecha_alta">Fecha de alta</label>
                    <input type="date" id="fecha_alta" name="fecha_alta">
                </div>

                <div id="campos_baja" style="display: none;">
                    <label for="fecha_baja">Fecha de baja</label>
                    <input type="date" id="fecha_baja" name="fecha_baja">
                    
                    <label for="causa_baja">Causa de la baja</label>
                    <textarea id="causa_baja" name="causa_baja" placeholder="Describe la causa de la baja..."></textarea>
                </div>

                <button type="submit">Procesar</button>
            </form>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSelect = document.getElementById('tipo_operacion');
            const camposAlta = document.getElementById('campos_alta');
            const camposBaja = document.getElementById('campos_baja');

            tipoSelect.addEventListener('change', function() {
                if (this.value === 'alta') {
                    camposAlta.style.display = 'block';
                    camposBaja.style.display = 'none';
                } else if (this.value === 'baja') {
                    camposAlta.style.display = 'none';
                    camposBaja.style.display = 'block';
                } else {
                    camposAlta.style.display = 'none';
                    camposBaja.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
