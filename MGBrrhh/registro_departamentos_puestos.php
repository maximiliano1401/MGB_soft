<?php
require_once 'auth.php';
require_once 'conexion.php';

$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tipo = $_POST['tipo'] ?? '';
    $nombre = trim($_POST['nombre'] ?? '');
    $departamento_id = intval($_POST['departamento_id'] ?? 0);
    $empresa_id = intval($_POST['empresa_id'] ?? 0);

    if ($tipo === 'departamento') {
        if ($nombre === '') {
            $mensaje = "El nombre del departamento es obligatorio.";
        } else {
            // Preparar el valor de empresa_id para bind_param
            $empresa_param = ($empresa_id > 0) ? $empresa_id : null;
            
            $stmt = $conn->prepare("INSERT INTO departamentos (nombre, empresa_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $empresa_param);
            if ($stmt->execute()) {
                $mensaje = "Departamento registrado correctamente.";
            } else {
                $mensaje = "Error al registrar el departamento.";
            }
            $stmt->close();
        }
    } elseif ($tipo === 'puesto') {
        if ($nombre === '' || !$departamento_id) {
            $mensaje = "El nombre del puesto y el departamento son obligatorios.";
        } else {
            $stmt = $conn->prepare("INSERT INTO puestos (nombre, departamento_id) VALUES (?, ?)");
            $stmt->bind_param("si", $nombre, $departamento_id);
            if ($stmt->execute()) {
                $mensaje = "Puesto registrado correctamente.";
            } else {
                $mensaje = "Error al registrar el puesto.";
            }
            $stmt->close();
        }
    } else {
        $mensaje = "Selecciona un tipo válido.";
    }
}

// Obtener departamentos para el select de puestos
$departamentos = [];
$result = $conn->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $departamentos[] = $row;
}

// Obtener empresas para el select de departamentos
$empresas = [];
$result = $conn->query("SELECT id, nombre FROM empresas ORDER BY nombre");
while ($row = $result->fetch_assoc()) {
    $empresas[] = $row;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Departamento o Puesto</title>
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
        .mensaje {
            margin-top: 18px; color: #20734b; background: #e0f7e9;
            border: 1px solid #b2dfdb; border-radius: 6px; padding: 10px; text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="IMG/4.png" alt="Logo MGB">
        </div>
        <h1>Registrar Departamento o Puesto</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="Ver_Registrados/Ver_Registro_puestos.php">Ver Departamentos</a>
        </nav>
    </header>
    <main>
        <div class="form-container">
            <h2>Formulario de registro</h2>
            <?php if ($mensaje): ?>
                <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>
            <form method="post">
                <label for="tipo">Tipo</label>
                <select name="tipo" id="tipo" required>
                    <option value="">Selecciona una opción</option>
                    <option value="departamento">Departamento</option>
                    <option value="puesto">Puesto</option>
                </select>

                <label for="nombre">Nombre</label>
                <input type="text" name="nombre" id="nombre" required>

                <div id="campo-empresa" style="display: none;">
                    <label for="empresa_id">Empresa (opcional para departamento)</label>
                    <select name="empresa_id" id="empresa_id">
                        <option value="">Selecciona una empresa</option>
                        <?php foreach ($empresas as $emp): ?>
                            <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="campo-dep" style="display: none;">
                    <label for="departamento_id">Departamento (para el puesto)</label>
                    <select name="departamento_id" id="departamento_id">
                        <option value="">Selecciona un departamento</option>
                        <?php foreach ($departamentos as $dep): ?>
                            <option value="<?= $dep['id'] ?>"><?= htmlspecialchars($dep['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit">Registrar</button>
            </form>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tipoSelect = document.getElementById('tipo');
            const campoEmpresa = document.getElementById('campo-empresa');
            const campoDep = document.getElementById('campo-dep');

            tipoSelect.addEventListener('change', function() {
                if (this.value === 'departamento') {
                    campoEmpresa.style.display = 'block';
                    campoDep.style.display = 'none';
                } else if (this.value === 'puesto') {
                    campoEmpresa.style.display = 'none';
                    campoDep.style.display = 'block';
                } else {
                    campoEmpresa.style.display = 'none';
                    campoDep.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>