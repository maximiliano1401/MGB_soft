<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'php/conexion.php';

// Obtener cuentas desde la BD
$cuentas = [];
$result = $conn->query("SELECT codigo, nombre, tipo FROM cuentas ORDER BY tipo, codigo");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cuentas[] = $row;
    }
}

// Agrupar cuentas por tipo para JS
$cuentas_por_tipo = [
    'Activo' => [],
    'Pasivo' => [],
    'Patrimonio' => []
];
foreach ($cuentas as $c) {
    $cuentas_por_tipo[$c['tipo']][] = $c;
}

// Manejo del formulario final (guardar en BD)
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit'])) {
    $empresa = trim($_POST['empresa'] ?? '');
    $fecha = $_POST['fecha'] ?? '';
    $notas = trim($_POST['notas'] ?? '');
    // Decodificar el JSON de cuentas agregadas
    $cuentas_post = [];
    if (!empty($_POST['cuentas_agregadas'])) {
        $cuentas_post = json_decode($_POST['cuentas_agregadas'], true);
        if (!is_array($cuentas_post)) $cuentas_post = [];
    }

    if (!$empresa || !$fecha || empty($cuentas_post)) {
        $mensaje = '<span style="color:red;">Todos los campos son obligatorios.</span>';
    } else {
        $total_activo = 0;
        $total_pasivo = 0;
        $total_patrimonio = 0;
        foreach ($cuentas_post as $c) {
            $saldo = floatval($c['saldo']);
            if ($c['tipo'] === 'Activo') $total_activo += $saldo;
            if ($c['tipo'] === 'Pasivo') $total_pasivo += $saldo;
            if ($c['tipo'] === 'Patrimonio') $total_patrimonio += $saldo;
        }
        if (round($total_activo, 2) !== round($total_pasivo + $total_patrimonio, 2)) {
            $mensaje = '<span style="color:red;">La suma de Activos debe ser igual a la suma de Pasivos + Patrimonio.</span>';
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("INSERT INTO balance_inicial (empresa, fecha, notas, creado_por) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $empresa, $fecha, $notas, $_SESSION['usuario_id']);
                $stmt->execute();
                $balance_id = $conn->insert_id;
                $stmt->close();

                $stmt_det = $conn->prepare("INSERT INTO balance_inicial_detalle (balance_id, cuenta_codigo, saldo) VALUES (?, ?, ?)");
                foreach ($cuentas_post as $c) {
                    $codigo = $c['codigo'];
                    $saldo = floatval($c['saldo']);
                    $stmt_det->bind_param("isd", $balance_id, $codigo, $saldo);
                    $stmt_det->execute();
                }
                $stmt_det->close();

                $conn->commit();
                $mensaje = '<span style="color:green;">Balance inicial guardado correctamente.</span>';
            } catch (Exception $e) {
                $conn->rollback();
                $mensaje = '<span style="color:red;">Error al guardar: ' . htmlspecialchars($e->getMessage()) . '</span>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Balance General Inicial</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <style>
    

        header {
            background: #fff;
            color: #004aad;
            padding: 36px 0 24px 0;
            text-align: center;
            box-shadow: 0 2px 8px #004aad22;
            margin-bottom: 32px;
        }

        .empresa-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: #004aad;
            letter-spacing: 2px;
            margin-bottom: 8px;
            text-shadow: 1px 2px 8px #004aad22;
        }

        .empresa-sub {
            font-size: 1.1rem;
            color: #1976d2;
            font-weight: 400;
            letter-spacing: 1px;
            margin-bottom: 0;
        }

        main {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 16px 40px 16px;
        }

        section {
            background: #fff;
            padding: 36px 32px;
            margin-bottom: 36px;
            border-radius: 14px;
            box-shadow: 0 4px 18px #004aad18;
            border-left: 6px solid #004aad;
            transition: box-shadow 0.2s, border-color 0.2s;
        }

        section:hover {
            box-shadow: 0 8px 32px #004aad33;
            border-left: 6px solid #1976d2;
        }

        /* --- MEJORA: Bloque de selección empresa y fecha --- */
        .balance-section {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
            margin-bottom: 18px;
            background: linear-gradient(90deg, #e3eefd 60%, #f6f8fa 100%);
            border-radius: 10px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 2px 8px #004aad11;
            align-items: flex-end;
        }

        .balance-section label {
            display: flex;
            flex-direction: column;
            font-weight: 600;
            color: #004aad;
            font-size: 1.08em;
            margin-bottom: 0;
            flex: 1 1 220px;
        }

        .balance-section select,
        .balance-section input[type="date"] {
            margin-top: 7px;
            padding: 10px 12px;
            border-radius: 7px;
            border: 1.5px solid #b0c4de;
            background: #f9fbfc;
            font-size: 1.07em;
            color: #222;
            transition: border 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 4px #004aad11;
        }

        .balance-section select:focus,
        .balance-section input[type="date"]:focus {
            border-color: #004aad;
            outline: none;
            box-shadow: 0 0 0 2px #e3eefd;
        }

        .tabs {
            display: flex;
            margin-bottom: 10px;
        }

        .tab-btn {
            padding: 10px 28px;
            border: 1px solid #004aad;
            background: #f5faff;
            cursor: pointer;
            border-bottom: none;
            font-weight: 600;
            color: #004aad;
            outline: none;
            border-radius: 8px 8px 0 0;
            font-size: 1.08rem;
            margin-right: 2px;
            transition: background 0.2s, color 0.2s;
        }

        .tab-btn.active {
            background: #fff;
            color: #222;
            border-top: 3px solid #004aad;
            font-weight: 700;
        }

        .tab-content {
            border: 1px solid #004aad;
            padding: 18px 16px 10px 16px;
            background: #fff;
            border-radius: 0 0 8px 8px;
        }

        .form-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .form-row label {
            min-width: 80px;
            font-weight: 500;
            color: #004aad;
        }

        .form-row select,
        .form-row input[type="number"] {
            min-width: 160px;
            border-radius: 6px;
            border: 1px solid #b0c4de;
            padding: 8px;
            font-size: 1rem;
            background: #f8fafd;
        }

        .form-row button {
            margin-left: 16px;
            background: #27ae60;
            color: #fff;
            padding: 8px 22px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            font-size: 1rem;
            transition: background 0.2s;
        }

        .form-row button:hover {
            background: #219150;
        }

        .added-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
            background: #f8fafd;
            border-radius: 8px;
            overflow: hidden;
        }

        .added-table th,
        .added-table td {
            border: 1px solid #d0d7e6;
            padding: 10px 12px;
            font-size: 1rem;
        }

        .added-table th {
            background: #e3eaff;
            color: #004aad;
            font-weight: 600;
        }

        .added-table tr:nth-child(even) {
            background: #f4f8ff;
        }

        .remove-btn {
            color: #e53935;
            cursor: pointer;
            font-weight: bold;
            border: none;
            background: none;
            font-size: 1.1rem;
            margin-right: 4px;
        }

        .remove-btn[title="Editar"] {
            color: #1976d2;
        }

        select,
        input,
        textarea {
            font-family: inherit;
        }

        textarea {
            border-radius: 6px;
            border: 1px solid #b0c4de;
            padding: 8px;
            font-size: 1rem;
            background: #f8fafd;
        }

        button[type="submit"] {
            background: linear-gradient(90deg, #004aad 70%, #1976d2 100%);
            color: #fff;
            font-size: 1.12rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            padding: 14px 36px;
            cursor: pointer;
            box-shadow: 0 2px 8px #004aad22;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            letter-spacing: 0.5px;
        }

        button[type="submit"]:hover {
            background: linear-gradient(90deg, #1976d2 60%, #004aad 100%);
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 6px 18px #004aad33;
        }

        h3 {
            color: #004aad;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        /* --- BOTÓN VOLVER --- */
        .volver-btn {
            display: inline-block;
            margin: 32px auto 0 auto;
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
            main {
                padding: 0 8px 40px 8px;
            }

            section {
                padding: 18px 8px;
            }

            .empresa-title {
                font-size: 1.5rem;
            }

            .balance-section {
                flex-direction: column;
                gap: 12px;
                padding: 12px 6px 6px 6px;
            }
        }

        @media (max-width: 600px) {
            .tabs {
                flex-direction: column;
            }

            .tab-btn {
                width: 100%;
                margin-bottom: 4px;
            }

            .form-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="empresa-title">MGB Contabilidad</div>
        <div class="empresa-sub">Balance General Inicial</div>
    </header>
    <main>
        <section>
            <?php if ($mensaje) echo "<div style='margin-bottom:18px;'>$mensaje</div>"; ?>
            <form id="balanceForm" method="post" action="">
                <div class="balance-section" style="display:flex;gap:24px;flex-wrap:wrap;">
                    <?php
                    // Obtener lista de empresas
                    $empresas = $conn->query("SELECT id, nombre FROM empresas ORDER BY nombre");
                    ?>
                    <label>Empresa:
                        <select name="empresa" required>
                            <option value="">-- Seleccionar --</option>
                            <?php while ($e = $empresas->fetch_assoc()): ?>
                                <option value="<?= $e['id'] ?>" <?= (isset($_POST['empresa']) && $_POST['empresa'] == $e['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($e['nombre']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </label>
                    <label>Fecha:
                        <input type="date" name="fecha" required value="<?= htmlspecialchars($_POST['fecha'] ?? '') ?>">
                    </label>
                </div>
                <div class="tabs">
                    <button type="button" class="tab-btn active" data-tab="Activo">Activo</button>
                    <button type="button" class="tab-btn" data-tab="Pasivo">Pasivo</button>
                    <button type="button" class="tab-btn" data-tab="Patrimonio">Patrimonio</button>
                </div>
                <div class="tab-content">
                    <div class="form-row">
                        <label>Cuenta</label>
                        <select id="cuentaSelect"></select>
                        <label>Saldo</label>
                        <input type="number" id="saldoInput" min="0" step="0.01">
                        <button type="button" id="agregarBtn">Agregar</button>
                    </div>
                </div>
                <input type="hidden" name="notas" value="">
                <input type="hidden" name="final_submit" value="1">
                <div class="balance-section">
                    <h3>Cuentas agregadas</h3>
                    <table class="added-table" id="tablaAgregados">
                        <thead>
                            <tr>
                                <th>No. de cuenta</th>
                                <th>Cuenta</th>
                                <th>Debe</th>
                                <th>Haber</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- JS llenará aquí -->
                        </tbody>
                    </table>
                </div>
                <div style="margin-bottom:18px;">
                    <label>Notas adicionales:<br>
                        <textarea name="notas" rows="3" style="width:100%;"><?= htmlspecialchars($_POST['notas'] ?? '') ?></textarea>
                    </label>
                </div>
                <input type="hidden" name="cuentas_agregadas" id="cuentasAgregadasInput">
                <div style="text-align:right;">
                    <button type="submit">Guardar Balance Inicial</button>
                </div>
            </form>
            <a href="index.php" class="volver-btn">Volver al menú principal</a>
            <p style="margin-top:20px;color:#888;">* Agrega cuentas al balance y revisa la tabla antes de guardar. Se validará la partida doble antes de guardar.</p>
        </section>
    </main>
    <script>
        // Cuentas agrupadas por tipo para JS
        const cuentasPorTipo = <?= json_encode($cuentas_por_tipo) ?>;
        let tipoActual = 'Activo';
        let cuentasAgregadas = [];
        let editIndex = null;

        function renderSelect() {
            const select = document.getElementById('cuentaSelect');
            select.innerHTML = '';
            cuentasPorTipo[tipoActual].forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.codigo;
                opt.textContent = c.nombre + ' (' + c.codigo + ')';
                select.appendChild(opt);
            });
        }

        function renderTabla() {
            const tbody = document.getElementById('tablaAgregados').querySelector('tbody');
            tbody.innerHTML = '';
            cuentasAgregadas.forEach((c, idx) => {
                const debe = (c.tipo === 'Activo') ? parseFloat(c.saldo).toFixed(2) : '';
                const haber = (c.tipo === 'Pasivo' || c.tipo === 'Patrimonio') ? parseFloat(c.saldo).toFixed(2) : '';
                const tr = document.createElement('tr');
                tr.innerHTML = `
            <td>${c.codigo}</td>
            <td>${c.nombre}</td>
            <td>${debe}</td>
            <td>${haber}</td>
            <td>
                <button type="button" class="remove-btn" onclick="editarCuenta(${idx})" title="Editar">&#9998;</button>
                <button type="button" class="remove-btn" onclick="eliminarCuenta(${idx})" title="Eliminar">X</button>
            </td>
        `;
                tbody.appendChild(tr);
            });
            document.getElementById('cuentasAgregadasInput').value = JSON.stringify(cuentasAgregadas);
        }

        function eliminarCuenta(idx) {
            cuentasAgregadas.splice(idx, 1);
            renderTabla();
        }

        function editarCuenta(idx) {
            const c = cuentasAgregadas[idx];
            tipoActual = c.tipo;
            document.querySelectorAll('.tab-btn').forEach(b => {
                if (b.getAttribute('data-tab') === tipoActual) b.classList.add('active');
                else b.classList.remove('active');
            });
            renderSelect();
            document.getElementById('cuentaSelect').value = c.codigo;
            document.getElementById('saldoInput').value = c.saldo;
            editIndex = idx;
            document.getElementById('agregarBtn').textContent = 'Actualizar';
            document.getElementById('agregarBtn').style.background = '#f39c12';
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                tipoActual = this.getAttribute('data-tab');
                renderSelect();
                editIndex = null;
                document.getElementById('agregarBtn').textContent = 'Agregar';
                document.getElementById('agregarBtn').style.background = '#27ae60';
                document.getElementById('saldoInput').value = '';
            });
        });

        document.getElementById('agregarBtn').addEventListener('click', function() {
            const select = document.getElementById('cuentaSelect');
            const saldo = document.getElementById('saldoInput').value;
            if (!select.value || saldo === '' || isNaN(saldo) || parseFloat(saldo) < 0) {
                alert('Selecciona una cuenta y un saldo válido.');
                return;
            }
            const cuenta = cuentasPorTipo[tipoActual].find(c => c.codigo === select.value);

            if (editIndex !== null) {
                if (cuentasAgregadas.some((c, idx) => c.codigo === select.value && idx !== editIndex)) {
                    alert('Esta cuenta ya fue agregada.');
                    return;
                }
                cuentasAgregadas[editIndex] = {
                    tipo: tipoActual,
                    codigo: cuenta.codigo,
                    nombre: cuenta.nombre,
                    saldo: parseFloat(saldo)
                };
                editIndex = null;
                this.textContent = 'Agregar';
                this.style.background = '#27ae60';
            } else {
                if (cuentasAgregadas.some(c => c.codigo === select.value)) {
                    alert('Esta cuenta ya fue agregada.');
                    return;
                }
                cuentasAgregadas.push({
                    tipo: tipoActual,
                    codigo: cuenta.codigo,
                    nombre: cuenta.nombre,
                    saldo: parseFloat(saldo)
                });
            }
            renderTabla();
            document.getElementById('saldoInput').value = '';
        });

        // Validación JS antes de enviar el formulario
        document.getElementById('balanceForm').addEventListener('submit', function(e) {
            const empresa = document.querySelector('select[name="empresa"]').value.trim();
            const fecha = document.querySelector('input[name="fecha"]').value.trim();
            const notas = document.querySelector('textarea[name="notas"]').value.trim();

            if (!empresa || !fecha) {
                alert('Debes completar los campos de Empresa y Fecha.');
                e.preventDefault();
                return false;
            }
            if (cuentasAgregadas.length === 0) {
                alert('Debes agregar al menos una cuenta.');
                e.preventDefault();
                return false;
            }
            // Validar partida doble antes de enviar
            let totalActivo = 0,
                totalPasivo = 0,
                totalPatrimonio = 0;
            cuentasAgregadas.forEach(c => {
                if (c.tipo === 'Activo') totalActivo += parseFloat(c.saldo);
                if (c.tipo === 'Pasivo') totalPasivo += parseFloat(c.saldo);
                if (c.tipo === 'Patrimonio') totalPatrimonio += parseFloat(c.saldo);
            });
            if (Math.round((totalActivo - (totalPasivo + totalPatrimonio)) * 100) / 100 !== 0) {
                alert('La suma de Activos debe ser igual a la suma de Pasivos + Patrimonio.');
                e.preventDefault();
                return false;
            }
            document.getElementById('cuentasAgregadasInput').value = JSON.stringify(cuentasAgregadas);
        });

        // Inicializar
        renderSelect();
        renderTabla();
    </script>
</body>

</html>