<?php
require_once '../conexion.php';

$sql = "SELECT n.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as empleado,
        n.sueldo_base, n.periodicidad_pago, n.fecha_inicio, n.activo
        FROM nomina n
        JOIN empleados e ON n.empleado_id = e.id
        ORDER BY n.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nóminas Registradas</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        main { max-width: 1100px; margin: 2.5rem auto 0 auto; padding: 0 1rem 2rem 1rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #005baa; color: #fff; }
        tr:hover { background: #f1f7ff; }
        h1 { color: #005baa; }
        .volver { margin: 1rem 0; display: inline-block; color: #005baa; text-decoration: none; font-weight: 600; }
        .volver:hover { text-decoration: underline; }
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
        .sueldo { text-align: right; }
    </style>
</head>
<body>
    <main>
        <h1>Nóminas Registradas</h1>
        <a class="volver" href="../index.html">&larr; Volver</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Sueldo Base</th>
                    <th>Periodicidad</th>
                    <th>Fecha Inicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['empleado']) ?></td>
                            <td class="sueldo">$<?= number_format($row['sueldo_base'], 2) ?></td>
                            <td><?= ucfirst($row['periodicidad_pago']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td class="<?= $row['activo'] ? 'estado-activo' : 'estado-inactivo' ?>">
                                <?= $row['activo'] ? 'Activo' : 'Inactivo' ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No hay nóminas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
