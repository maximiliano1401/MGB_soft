<?php
require_once '../conexion.php';

$sql = "SELECT a.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as empleado,
        a.tipo, a.fecha_inicio, a.fecha_fin, a.motivo,
        DATEDIFF(a.fecha_fin, a.fecha_inicio) + 1 as dias_ausencia
        FROM ausencias a
        JOIN empleados e ON a.empleado_id = e.id
        ORDER BY a.fecha_inicio DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ausencias Registradas</title>
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
        .tipo-vacaciones { background: #e8f5e8; color: #155724; padding: 2px 6px; border-radius: 4px; }
        .tipo-incapacidad { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; }
        .tipo-falta { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 4px; }
        .motivo { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dias { text-align: center; font-weight: bold; }
    </style>
</head>
<body>
    <main>
        <h1>Ausencias Registradas</h1>
        <a class="volver" href="../index.html">&larr; Volver</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Empleado</th>
                    <th>Tipo</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Días</th>
                    <th>Motivo</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['empleado']) ?></td>
                            <td>
                                <span class="tipo-<?= $row['tipo'] ?>">
                                    <?= ucfirst($row['tipo']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_inicio'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($row['fecha_fin'])) ?></td>
                            <td class="dias"><?= $row['dias_ausencia'] ?></td>
                            <td class="motivo" title="<?= htmlspecialchars($row['motivo']) ?>">
                                <?= htmlspecialchars($row['motivo'] ?: 'Sin motivo especificado') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No hay ausencias registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
