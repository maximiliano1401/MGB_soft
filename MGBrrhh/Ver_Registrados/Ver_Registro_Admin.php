<?php
require_once '../auth.php';
require_once '../conexion.php';

$sql = "SELECT id, usuario FROM usuarios ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios Registrados</title>
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
        <h1>Usuarios Registrados</h1>
        <a class="volver" href="../dashboard.php">&larr; Volver al Dashboard</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['usuario']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2">No hay usuarios registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>