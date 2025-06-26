<?php
require_once '../conexion.php';

$sql = "SELECT ab.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as empleado,
        ab.fecha_alta, ab.fecha_baja, ab.causa_baja, ab.estado
        FROM altas_bajas ab
        JOIN empleados e ON ab.empleado_id = e.id
        ORDER BY ab.fecha_alta DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Altas y Bajas Registradas</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        main { max-width: 1200px; margin: 2.5rem auto 0 auto; padding: 0 1rem 2rem 1rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #005baa; color: #fff; }
        tr:hover { background: #f1f7ff; }
        h1 { color: #005baa; }
        .volver { margin: 1rem 0; display: inline-block; color: #005baa; text-decoration: none; font-weight: 600; }
        .volver:hover { text-decoration: underline; }
        .estado-activo { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .estado-inactivo { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .causa { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <main>
        <h1>Altas y Bajas Registradas</h1>
        <a class="volver" href="../index.html">&larr; Volver</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Fecha Alta</th>
                    <th>Fecha Baja</th>
                    <th>Causa Baja</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['empleado']) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_alta'])) ?></td>
                            <td><?= $row['fecha_baja'] ? date('d/m/Y', strtotime($row['fecha_baja'])) : 'N/A' ?></td>
                            <td class="causa" title="<?= htmlspecialchars($row['causa_baja']) ?>">
                                <?= htmlspecialchars($row['causa_baja'] ?: 'N/A') ?>
                            </td>
                            <td>
                                <span class="estado-<?= $row['estado'] ?>">
                                    <?= ucfirst($row['estado']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No hay registros de altas y bajas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
