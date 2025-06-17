<?php
require_once 'php/conexion.php';

// Obtener todos los balances con el nombre de la empresa para el selector
$balances = [];
$sql = "
    SELECT bi.id, e.nombre AS empresa_nombre, bi.fecha
    FROM balance_inicial bi
    JOIN empresas e ON e.id = bi.empresa
    ORDER BY bi.fecha DESC
";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $balances[] = $row;
}

// Obtener el balance_id desde GET
$balance_id = isset($_GET['balance_id']) ? intval($_GET['balance_id']) : 0;

// Inicializar variables
$balance_titulo = '';
$cuentas = [];
$total_debe = 0;
$total_haber = 0;

if ($balance_id > 0) {
    // Obtener información del balance seleccionado con el nombre de la empresa
    $stmt = $conn->prepare("
        SELECT e.nombre AS empresa_nombre, bi.fecha
        FROM balance_inicial bi
        JOIN empresas e ON e.id = bi.empresa
        WHERE bi.id = ?
    ");
    $stmt->bind_param("i", $balance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $balance = $result->fetch_assoc();
    $balance_titulo = $balance ? ($balance['empresa_nombre'] . ' - ' . $balance['fecha']) : 'Sin título';

    // Consultar cuentas con saldos
    $query = "
        SELECT c.codigo, c.nombre, c.tipo, SUM(bid.saldo) AS saldo
        FROM balance_inicial_detalle bid
        JOIN cuentas c ON c.codigo = bid.cuenta_codigo
        WHERE bid.balance_id = ?
        GROUP BY c.codigo, c.nombre, c.tipo
        ORDER BY c.codigo
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $balance_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $saldo = floatval($row['saldo']);
        $debe = 0;
        $haber = 0;

        if (in_array($row['tipo'], ['Activo'])) {
            $debe = $saldo;
            $total_debe += $saldo;
        } else {
            $haber = $saldo;
            $total_haber += $saldo;
        }

        $cuentas[] = [
            'codigo' => $row['codigo'],
            'nombre' => $row['nombre'],
            'debe' => $debe,
            'haber' => $haber
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balanza de Comprobación <?= $balance_titulo ? '- ' . htmlspecialchars($balance_titulo) : '' ?></title>
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
        select {
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
        select:focus {
            border-color: #004aad;
            outline: none;
        }
        button {
            margin-top: 22px;
            padding: 10px 24px;
            background: #004aad;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1.08em;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        button:hover {
            background: #003366;
        }
        a {
            color: #004aad;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
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
            text-align: left;
            font-size: 1em;
        }
        th {
            background: #004aad;
            color: #fff;
            font-weight: 600;
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
        @media (max-width: 900px) {
            table { width: 99%; font-size: 0.98em; }
            form { max-width: 98%; padding: 18px 8px; }
        }
    </style>
</head>
<body>

<h2>Balanza de Comprobación</h2>

<form method="get" action="">
    <label for="balance_id">Seleccione un balance:</label>
    <select name="balance_id" id="balance_id" onchange="this.form.submit()">
        <option value="0">-- Seleccionar --</option>
        <?php foreach ($balances as $b): ?>
            <option value="<?= $b['id'] ?>" <?= $b['id'] == $balance_id ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['empresa_nombre'] . ' - ' . $b['fecha']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($balance_id > 0): ?>
    <h3><?= htmlspecialchars($balance_titulo) ?></h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Cuenta</th>
                <th>Debe</th>
                <th>Haber</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cuentas as $cuenta): ?>
                <tr>
                    <td><?= htmlspecialchars($cuenta['codigo']) ?></td>
                    <td><?= htmlspecialchars($cuenta['nombre']) ?></td>
                    <td><?= number_format($cuenta['debe'], 2) ?></td>
                    <td><?= number_format($cuenta['haber'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background-color: #e0e0e0;">
                <td colspan="2" style="text-align: right;">Totales:</td>
                <td><?= number_format($total_debe, 2) ?></td>
                <td><?= number_format($total_haber, 2) ?></td>
            </tr>
        </tbody>
    </table>
<?php endif; ?>

<!-- Botón volver al menú principal -->
<div style="text-align:center; margin:40px 0 30px 0;">
    <a href="index.php" style="
        display:inline-block;
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
        transition:background 0.2s,box-shadow 0.2s,color 0.2s;
    "
    onmouseover="this.style.background='linear-gradient(90deg,#003366 80%,#2563eb 100%)';this.style.color='#e3eefd';"
    onmouseout="this.style.background='linear-gradient(90deg,#004aad 80%,#2563eb 100%)';this.style.color='#fff';"
    >Volver al menú principal</a>
</div>

</body>
</html>
