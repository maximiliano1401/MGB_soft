<?php
include 'php/conexion.php';

if (!isset($_GET['id'])) {
    // Mostrar selector si no se ha seleccionado ningún balance
    $res = $conn->query("SELECT id, empresa, fecha FROM balance_inicial ORDER BY fecha DESC");

    echo "<h3>Balance General</h3>";
    echo "<label for='fecha_balance'>Seleccionar fecha:</label> ";
    echo "<select id='fecha_balance' onchange='cargarVista(\"visualizacion_balance.php?id=\" + this.value)'>";
    echo "<option value=''>-- Seleccione --</option>";
    while ($row = $res->fetch_assoc()) {
        $label = $row['empresa'] . " - " . $row['fecha'];
        echo "<option value='{$row['id']}'>{$label}</option>";
    }
    echo "</select><hr>";
    echo "<div id='detalle_balance'></div>";
    exit;
}

// Si se envía un ID, se muestra el detalle
$balance_id = intval($_GET['id']);

$sql = "
    SELECT 
        d.cuenta_codigo,
        c.nombre AS nombre_cuenta,
        c.tipo,
        d.saldo
    FROM balance_inicial_detalle d
    INNER JOIN cuentas c ON d.cuenta_codigo = c.codigo
    WHERE d.balance_id = $balance_id
    ORDER BY FIELD(c.tipo, 'Activo', 'Pasivo', 'Patrimonio'), c.codigo
";

$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    echo "<table>
            <thead>
                <tr><th>Tipo</th><th>Código</th><th>Cuenta</th><th>Saldo</th></tr>
            </thead>
            <tbody>";
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
                <td>{$row['tipo']}</td>
                <td>{$row['cuenta_codigo']}</td>
                <td>{$row['nombre_cuenta']}</td>
                <td>" . number_format($row['saldo'], 2) . "</td>
            </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "No hay detalles para este balance.";
}
?>
