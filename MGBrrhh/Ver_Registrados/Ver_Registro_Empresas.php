<?php
require_once '../conexion.php';

$sql = "SELECT id, nombre, razon_social, rfc, regimen_fiscal, 
        CONCAT_WS(', ', calle, numero_ext, numero_int, colonia, municipio, estado) as direccion_completa 
        FROM empresas ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empresas Registradas</title>
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
        .direccion { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <main>
        <h1>Empresas Registradas</h1>
        <a class="volver" href="../index.html">&larr; Volver</a>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Razón Social</th>
                    <th>RFC</th>
                    <th>Régimen Fiscal</th>
                    <th>Dirección</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['razon_social']) ?></td>
                            <td><?= htmlspecialchars($row['rfc']) ?></td>
                            <td><?= htmlspecialchars($row['regimen_fiscal'] ?? 'N/A') ?></td>
                            <td class="direccion" title="<?= htmlspecialchars($row['direccion_completa']) ?>">
                                <?= htmlspecialchars($row['direccion_completa'] ?: 'N/A') ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No hay empresas registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
