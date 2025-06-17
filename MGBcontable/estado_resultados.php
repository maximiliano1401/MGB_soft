<?php
require_once 'php/conexion.php';

// Obtener años disponibles
$anios = [];
$res = $conn->query("SELECT DISTINCT YEAR(fecha) AS anio FROM balance_inicial ORDER BY anio DESC");
while ($row = $res->fetch_assoc()) {
    $anios[] = $row['anio'];
}

// Obtener empresas (ID y nombre)
$empresas = [];
$res = $conn->query("SELECT id, nombre FROM empresas ORDER BY nombre ASC");
while ($row = $res->fetch_assoc()) {
    $empresas[$row['id']] = $row['nombre'];  // Guardamos id => nombre
}

// Filtros
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
$empresa_id = isset($_GET['empresa']) ? intval($_GET['empresa']) : 0;

$datos = [];
$total_ingresos = 0;
$total_gastos = 0;

if ($anio > 0 && $empresa_id > 0) {
    // Obtener balances de esa empresa (por id) y año
    $stmt = $conn->prepare("
        SELECT bi.id
        FROM balance_inicial bi
        WHERE bi.empresa = ? AND YEAR(bi.fecha) = ?
    ");
    $stmt->bind_param("ii", $empresa_id, $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        // Consultar saldos por cuenta para estos balances
        $query = "
            SELECT c.codigo, c.nombre, c.tipo, SUM(bid.saldo) AS saldo
            FROM balance_inicial_detalle bid
            JOIN cuentas c ON c.codigo = bid.cuenta_codigo
            WHERE bid.balance_id IN ($placeholders)
            GROUP BY c.codigo, c.nombre, c.tipo
            ORDER BY c.codigo
        ";

        $stmt = $conn->prepare($query);
        // Necesitamos hacer bind_param dinámicamente para los ids
        $stmt->bind_param($types, ...$ids);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $saldo = floatval($row['saldo']);
            $tipo = $row['tipo'];

            // Clasificación temporal: Ingreso = Patrimonio, Gasto = Activo (hasta que definas tipos nuevos)
            if ($tipo === 'Patrimonio') {
                $total_ingresos += $saldo;
                $datos[] = ['tipo' => 'Ingreso', 'cuenta' => $row['nombre'], 'monto' => $saldo];
            } elseif ($tipo === 'Activo') {
                $total_gastos += $saldo;
                $datos[] = ['tipo' => 'Gasto', 'cuenta' => $row['nombre'], 'monto' => $saldo];
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Estado de Resultados</title>
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
            color: #004aad;
        }
        form {
            background: #fff;
            max-width: 600px;
            margin: 32px auto 24px auto;
            padding: 22px 32px 12px 32px;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(44,62,80,0.09);
        }
        label {
            font-weight: 500;
            color: #2c3e50;
            margin-right: 8px;
        }
        select {
            padding: 7px 12px;
            border-radius: 5px;
            border: 1px solid #bfc9d1;
            font-size: 1em;
            background: #f9fbfc;
            color: #222;
            margin-right: 16px;
            transition: border 0.2s;
        }
        select:focus {
            border-color: #004aad;
            outline: none;
        }
        button[type="submit"] {
            padding: 9px 26px;
            background: #004aad;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #003366;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 90%;
            margin: 30px auto 0 auto;
            background: #fff;
            box-shadow: 0 2px 12px rgba(44,62,80,0.08);
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e9f2;
            font-size: 1em;
        }
        th {
            background: #004aad;
            color: #fff;
            font-weight: 600;
            text-align: left;
        }
        td {
            text-align: left;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) td {
            background: #f4f6fa;
        }
        tr.totales {
            font-weight: bold;
            background-color: #e3f0ff !important;
            color: #004aad;
        }
        tr.utilidad {
            font-weight: bold;
            background-color: #d0ffd0 !important;
            color: #1b5e20;
        }
        .exportar-btn {
            margin-top: 24px;
            padding: 10px 28px;
            background: #004aad;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        .exportar-btn:hover {
            background: #003366;
        }
        .filtros-form {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 24px;
            flex-wrap: wrap;
            background: linear-gradient(90deg, #e3eefd 60%, #f6f8fa 100%);
            border-radius: 10px;
            padding: 18px 18px 10px 18px;
            box-shadow: 0 2px 8px #004aad11;
            margin-bottom: 18px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .filtros-form label {
            display: block;
            margin-bottom: 6px;
        }
        .filtros-form select, .filtros-form button {
            width: 180px;
        }
        .tabla-estado {
            max-width: 900px;
            margin: 0 auto;
        }
        .volver-btn:hover {
            background: linear-gradient(90deg,#003366 80%,#2563eb 100%) !important;
            color: #e3eefd !important;
        }
        @media (max-width: 900px) {
            .tabla-estado table { width: 99%; font-size: 0.98em; }
            .filtros-form { max-width: 99%; padding: 12px 4px; gap: 10px; }
            .filtros-form select, .filtros-form button { width: 100%; }
        }
        @media (max-width: 600px) {
            .filtros-form { flex-direction: column; align-items: stretch; }
            .tabla-estado { width: 99%; }
        }
    </style>
</head>

<body>

    <h2>Estado de Resultados</h2>

    <form method="get" action="">
        <div class="filtros-form">
            <div>
                <label for="empresa">Empresa:</label>
                <select name="empresa" id="empresa">
                    <option value="0">-- Seleccionar --</option>
                    <?php foreach ($empresas as $id => $nombre): ?>
                        <option value="<?= $id ?>" <?= $empresa_id === $id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($nombre) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="anio">Año:</label>
                <select name="anio" id="anio">
                    <option value="0">-- Seleccionar --</option>
                    <?php foreach ($anios as $a): ?>
                        <option value="<?= $a ?>" <?= $anio == $a ? 'selected' : '' ?>>
                            <?= $a ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit">Ver</button>
            </div>
        </div>
    </form>

    <?php if (!empty($datos)): ?>
        <div class="tabla-estado">
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Cuenta</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($datos as $fila): ?>
                        <tr>
                            <td><?= htmlspecialchars($fila['tipo']) ?></td>
                            <td><?= htmlspecialchars($fila['cuenta']) ?></td>
                            <td><?= number_format($fila['monto'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="totales">
                        <td colspan="2">Total Ingresos:</td>
                        <td><?= number_format($total_ingresos, 2) ?></td>
                    </tr>
                    <tr class="totales">
                        <td colspan="2">Total Gastos:</td>
                        <td><?= number_format($total_gastos, 2) ?></td>
                    </tr>
                    <tr class="utilidad">
                        <td colspan="2">Utilidad Neta:</td>
                        <td><?= number_format($total_ingresos - $total_gastos, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <form action="php/exportar_excel.php" method="post" style="margin-top:24px; text-align: center;">
            <input type="hidden" name="empresa" value="<?= htmlspecialchars($empresa_id) ?>">
            <input type="hidden" name="anio" value="<?= htmlspecialchars($anio) ?>">
            <button type="submit" class="exportar-btn">Exportar Totales a Excel</button>
        </form>
    <?php elseif ($anio > 0 && $empresa_id > 0): ?>
        <p style="text-align:center; color:red;">No se encontraron datos para la empresa y año seleccionados.</p>
    <?php endif; ?>

    <!-- Botón volver al menú principal -->
    <div style="text-align:center; margin:40px 0 30px 0;">
        <a href="index.php" class="volver-btn"
            style="display:inline-block;
                padding:13px 38px;
                background:linear-gradient(90deg,#004aad 80%,#2563eb 100%);
                color:#fff;
                border:none;
                border-radius:32px;
                font-size:1.18em;
                font-weight:700;
                text-align:center;
                text-decoration:none;
                box-shadow:0 4px 18px rgba(0,74,173,0.13);
                letter-spacing:0.7px;
                transition:background 0.2s,box-shadow 0.2s,color 0.2s;"
            onmouseover="this.style.background='linear-gradient(90deg,#003366 80%,#2563eb 100%)';this.style.color='#e3eefd';"
            onmouseout="this.style.background='linear-gradient(90deg,#004aad 80%,#2563eb 100%)';this.style.color='#fff';"
        >Volver al menú principal</a>
    </div>

</body>
</html>