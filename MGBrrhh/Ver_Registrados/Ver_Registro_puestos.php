<?php
require_once '../conexion.php';

$sql = "SELECT p.id, p.nombre AS puesto, d.nombre AS departamento
        FROM puestos p
        JOIN departamentos d ON p.departamento_id = d.id
        ORDER BY p.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puestos Registrados</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        main { max-width: 800px; margin: 2.5rem auto 0 auto; padding: 0 1rem 2rem 1rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #005baa; color: #fff; }
        tr:hover { background: #f1f7ff; }
        h1 { color: #005baa; }
        .volver { margin: 1rem 0; display: inline-block; color: #005baa; text-decoration: none; font-weight: 600; }
        .volver:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        <h1>Puestos Registrados</h1>
        <a class="volver" href="../index.html">&larr; Volver</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Puesto</th>
                    <th>Departamento</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['puesto']) ?></td>
                            <td><?= htmlspecialchars($row['departamento']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No hay puestos registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>