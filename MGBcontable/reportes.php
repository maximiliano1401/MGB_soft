<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 'admin') {
    header("Location: login.php");
    exit;
}
include 'php/conexion.php';

// Consultas de resumen
$total_compras = $conn->query("SELECT SUM(monto) AS total FROM compras")->fetch_assoc()['total'] ?? 0;
$total_ventas = $conn->query("SELECT SUM(monto) AS total FROM ventas")->fetch_assoc()['total'] ?? 0;
$total_nomina = $conn->query("SELECT SUM(monto) AS total FROM nomina")->fetch_assoc()['total'] ?? 0;
$total_impuestos = $conn->query("SELECT SUM(monto) AS total FROM impuestos")->fetch_assoc()['total'] ?? 0;

// Mensaje por GET
$mensaje = isset($_GET['mensaje']) ? $_GET['mensaje'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes y Análisis Financiero | MGB Contabilidad</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .reporte-box { background: #f8fafd; border-radius: 8px; padding: 24px; margin-bottom: 24px; box-shadow: 0 2px 8px #004aad11; }
        .reporte-box h3 { margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f4fa; }
        .mensaje { margin: 12px 0; color: green; }
    </style>
</head>
<body>
    <h2>Reportes y Análisis Financiero</h2>
    <?php if ($mensaje) echo "<div class='mensaje'>$mensaje</div>"; ?>

    <div class="reporte-box">
        <h3>Totales Generales</h3>
        <table>
            <tr>
                <th>Módulo</th>
                <th>Total</th>
            </tr>
            <tr>
                <td>Compras</td>
                <td>$<?= number_format($total_compras, 2) ?></td>
            </tr>
            <tr>
                <td>Ventas</td>
                <td>$<?= number_format($total_ventas, 2) ?></td>
            </tr>
            <tr>
                <td>Nómina</td>
                <td>$<?= number_format($total_nomina, 2) ?></td>
            </tr>
            <tr>
                <td>Impuestos</td>
                <td>$<?= number_format($total_impuestos, 2) ?></td>
            </tr>
        </table>
    </div>

    <!-- Puedes agregar más reportes aquí, como balances, cuentas por cobrar/pagar, etc. -->

    <!-- Ejemplo de exportación a Excel (requiere implementación en PHP con PhpSpreadsheet) -->
    <form action="php/exportar_excel.php" method="post" style="margin-top:24px;">
        <button type="submit">Exportar Totales a Excel</button>
    </form>
</body>
</html>