<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}
require_once 'php/conexion.php';

// Eliminar cuenta por parámetro GET
if (isset($_GET['eliminar']) && isset($_GET['balance_id']) && isset($_GET['cuenta'])) {
    $bid = intval($_GET['balance_id']);
    $cuenta = $_GET['cuenta'];
    $stmt = $conn->prepare("DELETE FROM balance_inicial_detalle WHERE balance_id = ? AND cuenta_codigo = ?");
    $stmt->bind_param("is", $bid, $cuenta);
    $stmt->execute();
    $stmt->close();
    header("Location: diario_general.php?balance_id=$bid");
    exit;
}

// Obtener balances
$balances = [];
$res = $conn->query("
  SELECT b.id, e.nombre AS empresa, b.fecha
  FROM balance_inicial b
  JOIN empresas e ON b.empresa = e.id
  ORDER BY b.fecha DESC
");
while ($row = $res->fetch_assoc()) {
    $balances[] = $row;
}

$balance_id = $_GET['balance_id'] ?? '';
$datos_balance = null;
$cuentas_existentes = [];

if ($balance_id) {
    $stmt = $conn->prepare("
  SELECT e.nombre AS empresa, b.fecha, b.notas
  FROM balance_inicial b
  JOIN empresas e ON b.empresa = e.id
  WHERE b.id = ?
");
    $stmt->bind_param("i", $balance_id);
    $stmt->execute();
    $datos_balance = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT d.cuenta_codigo, c.nombre, c.tipo, d.saldo
        FROM balance_inicial_detalle d
        JOIN cuentas c ON c.codigo = d.cuenta_codigo
        WHERE d.balance_id = ?");
    $stmt->bind_param("i", $balance_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $cuentas_existentes[] = $r;
    }
    $stmt->close();
}

// Todas las cuentas
$cuentas = [];
$result = $conn->query("SELECT codigo, nombre, tipo FROM cuentas ORDER BY tipo, codigo");
while ($row = $result->fetch_assoc()) {
    $cuentas[] = $row;
}
$cuentas_por_tipo = ['Activo' => [], 'Pasivo' => [], 'Patrimonio' => []];
foreach ($cuentas as $c) {
    $cuentas_por_tipo[$c['tipo']][] = $c;
}

// Guardado
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['final_submit']) && isset($_POST['cuentas_json'])) {
    $balance_id = intval($_POST['balance_id']);
    $cuentas_json = json_decode($_POST['cuentas_json'], true);
    if (!is_array($cuentas_json) || empty($cuentas_json)) {
        $mensaje = '<span style="color:red;">Debes ingresar cuentas válidas.</span>';
    } else {
        $total_activo = $total_pasivo = $total_patrimonio = 0;
        foreach ($cuentas_json as $c) {
            $saldo = floatval($c['saldo']);
            if ($c['tipo'] === 'Activo') $total_activo += $saldo;
            elseif ($c['tipo'] === 'Pasivo') $total_pasivo += $saldo;
            elseif ($c['tipo'] === 'Patrimonio') $total_patrimonio += $saldo;
        }
        if (round($total_activo, 2) !== round($total_pasivo + $total_patrimonio, 2)) {
            $mensaje = '<span style="color:red;">La suma de Activos debe ser igual a la suma de Pasivos + Patrimonio.</span>';
        } else {
            $conn->begin_transaction();
            try {
                $stmt_upd = $conn->prepare("UPDATE balance_inicial_detalle SET saldo = ? WHERE balance_id = ? AND cuenta_codigo = ?");
                $stmt_ins = $conn->prepare("INSERT INTO balance_inicial_detalle (balance_id, cuenta_codigo, saldo) VALUES (?, ?, ?)");
                foreach ($cuentas_json as $c) {
                    $codigo = $c['codigo'];
                    $saldo = floatval($c['saldo']);
                    $check = $conn->query("SELECT 1 FROM balance_inicial_detalle WHERE balance_id = $balance_id AND cuenta_codigo = '$codigo'");
                    if ($check->num_rows > 0) {
                        $stmt_upd->bind_param("dis", $saldo, $balance_id, $codigo);
                        $stmt_upd->execute();
                    } else {
                        $stmt_ins->bind_param("isd", $balance_id, $codigo, $saldo);
                        $stmt_ins->execute();
                    }
                }
                $stmt_upd->close();
                $stmt_ins->close();
                $conn->commit();
                $mensaje = '<span style="color:green;">Cuentas guardadas correctamente.</span>';
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
    <title>Diario General</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .tabs {
            display: flex;
            margin-bottom: 10px;
        }

        .tab-btn {
            padding: 8px 18px;
            border: 1px solid #004aad;
            background: #f5f5f5;
            cursor: pointer;
            border-bottom: none;
            font-weight: 600;
            color: #004aad;
        }

        .tab-btn.active {
            background: #fff;
            color: #222;
            border-top: 2px solid #004aad;
        }

        .tab-content {
            border: 1px solid #004aad;
            padding: 18px 16px 10px 16px;
            background: #fff;
        }

        .form-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        .form-row label {
            min-width: 80px;
        }

        .form-row select,
        .form-row input[type="number"] {
            min-width: 160px;
        }

        .form-row button {
            margin-left: 16px;
        }

        .added-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        .added-table th,
        .added-table td {
            border: 1px solid #bbb;
            padding: 6px 8px;
        }

        .added-table th {
            background: #e3eaff;
        }

        .remove-btn {
            color: #e53935;
            cursor: pointer;
            font-weight: bold;
            border: none;
            background: none;
        }

        .balance-section {
            margin-bottom: 18px;
        }
    </style>
</head>

<body>
    <header>
        <h2 style="text-align:center;">Diario General - Continuar Balance</h2>
    </header>
    <main>
        <section style="max-width:800px;margin:0 auto;">
            <?= $mensaje ?>
            <form id="diarioForm" method="post" action="">
                <div class="balance-section">
                    <label>Seleccionar Balance:
                        <select name="balance_id" onchange="location.href='diario_general.php?balance_id=' + this.value;" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($balances as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= $b['id'] == $balance_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['empresa']) ?> (<?= $b['fecha'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                <?php if ($datos_balance): ?>
                    <div class="balance-section">
                        <strong>Empresa:</strong> <?= htmlspecialchars($datos_balance['empresa']) ?><br>
                        <strong>Fecha:</strong> <?= htmlspecialchars($datos_balance['fecha']) ?><br>
                        <strong>Notas:</strong> <?= nl2br(htmlspecialchars($datos_balance['notas'])) ?>
                    </div>
                <?php endif; ?>

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

                <!-- Cambiar esta parte dentro de la tabla de cuentas agregadas -->
                <div class="balance-section">
                    <h3>Cuentas del balance</h3>
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
                        <tbody></tbody>
                    </table>
                </div>


                <input type="hidden" name="final_submit" value="1">
                <input type="hidden" name="cuentas_json" id="cuentasJsonInput">
                <div style="text-align:right;">
                    <button type="submit">Guardar Cuentas</button>
                </div>
            </form>
        </section>
    </main>
    <script>
        const cuentasPorTipo = <?= json_encode($cuentas_por_tipo) ?>;
        const cuentasExistentes = <?= json_encode($cuentas_existentes) ?>;
        let tipoActual = 'Activo';
        let cuentasAgregadas = cuentasExistentes.map(c => ({
            tipo: c.tipo,
            codigo: c.cuenta_codigo,
            nombre: c.nombre,
            saldo: parseFloat(c.saldo)
        }));
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

        // Reemplaza toda la función renderTabla() por esta
        function renderTabla() {
            const tbody = document.querySelector("#tablaAgregados tbody");
            tbody.innerHTML = '';

            let totalActivo = 0;
            let totalPasivo = 0;
            let totalPatrimonio = 0;

            cuentasAgregadas.forEach((c, idx) => {
                const saldo = parseFloat(c.saldo);
                let debe = '',
                    haber = '';

                if (c.tipo === 'Activo') {
                    debe = saldo.toFixed(2);
                    totalActivo += saldo;
                } else if (c.tipo === 'Pasivo') {
                    haber = saldo.toFixed(2);
                    totalPasivo += saldo;
                } else if (c.tipo === 'Patrimonio') {
                    haber = saldo.toFixed(2);
                    totalPatrimonio += saldo;
                }

                const eliminar = `<a href="?balance_id=<?= $balance_id ?>&eliminar=1&cuenta=${c.codigo}" onclick="return confirm('¿Eliminar esta cuenta?')" class="remove-btn">🗑️</a>`;
                const tr = document.createElement('tr');
                tr.innerHTML = `
 <td>${c.codigo}</td>
<td>${c.nombre}</td>
<td>${debe}</td>
<td>${haber}</td>
<td>${eliminar}</td>
`;
                tbody.appendChild(tr);
            });

            // Fila de totales
            const trTotales = document.createElement('tr');
            trTotales.innerHTML = `
<td colspan="2" style="text-align:right;font-weight:bold;">Totales:</td>
<td style="font-weight:bold;">${totalActivo.toFixed(2)}</td>
<td style="font-weight:bold;">${(totalPasivo + totalPatrimonio).toFixed(2)}</td>
<td></td>
`;
            tbody.appendChild(trTotales);
        }



        function editarCuenta(idx) {
            const c = cuentasAgregadas[idx];
            tipoActual = c.tipo;
            document.querySelectorAll('.tab-btn').forEach(b => {
                if (b.dataset.tab === tipoActual) b.classList.add('active');
                else b.classList.remove('active');
            });
            renderSelect();
            document.getElementById('cuentaSelect').value = c.codigo;
            document.getElementById('saldoInput').value = c.saldo;
            editIndex = idx;
        }

        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                tipoActual = btn.dataset.tab;
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                renderSelect();
                editIndex = null;
                document.getElementById('saldoInput').value = '';
            });
        });

        document.getElementById('agregarBtn').addEventListener('click', () => {
            const codigo = document.getElementById('cuentaSelect').value;
            const saldo = parseFloat(document.getElementById('saldoInput').value || 0);
            if (!codigo || saldo < 0) return alert('Datos inválidos.');

            const cuenta = cuentasPorTipo[tipoActual].find(c => c.codigo === codigo);
            if (!cuenta) return;

            if (editIndex !== null) {
                cuentasAgregadas[editIndex].saldo = saldo;
                editIndex = null;
            } else {
                if (cuentasAgregadas.some(c => c.codigo === codigo)) {
                    alert('Cuenta ya agregada.');
                    return;
                }
                cuentasAgregadas.push({
                    tipo: tipoActual,
                    codigo,
                    nombre: cuenta.nombre,
                    saldo
                });
            }

            document.getElementById('saldoInput').value = '';
            renderTabla();
        });

        document.getElementById('diarioForm').addEventListener('submit', e => {
            let total = {
                Activo: 0,
                Pasivo: 0,
                Patrimonio: 0
            };
            cuentasAgregadas.forEach(c => total[c.tipo] += parseFloat(c.saldo));
            if (Math.round(total.Activo * 100) / 100 !== Math.round((total.Pasivo + total.Patrimonio) * 100) / 100) {
                alert('Debe = Haber no cuadra.');
                e.preventDefault();
            } else {
                document.getElementById('cuentasJsonInput').value = JSON.stringify(cuentasAgregadas);
            }
        });

        renderSelect();
        renderTabla();
    </script>
</body>

</html>