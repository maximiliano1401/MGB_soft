<?php
require_once 'auth.php';
require_once 'conexion.php';

// Registrar asistencia si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empleado_id'])) {
    $empleado_id = (int)$_POST['empleado_id'];
    $fecha = date('Y-m-d');
    // Registrar asistencia (puedes crear la tabla asistencias si no existe)
    $stmt = $conn->prepare("INSERT INTO asistencias (empleado_id, fecha) VALUES (?, ?)");
    $stmt->bind_param("is", $empleado_id, $fecha);
    $stmt->execute();
    $stmt->close();
    header('Location: registrar_asistencias.php?success=1');
    exit();
}

// Obtener empleados activos
$sql = "SELECT e.id, CONCAT(e.nombre, ' ', e.apellido_paterno, ' ', e.apellido_materno) as nombre
        FROM empleados e
        LEFT JOIN (
            SELECT empleado_id, estado, ROW_NUMBER() OVER (PARTITION BY empleado_id ORDER BY fecha_movimiento DESC) as rn
            FROM altas_bajas
        ) ab ON e.id = ab.empleado_id AND ab.rn = 1
        WHERE ab.estado = 'activo' OR ab.estado IS NULL
        ORDER BY nombre ASC";
$result = $conn->query($sql);

// Obtener asistencias de hoy
$asistencias = [];
$res = $conn->query("SELECT empleado_id FROM asistencias WHERE fecha = '" . date('Y-m-d') . "'");
while ($row = $res->fetch_assoc()) {
    $asistencias[] = $row['empleado_id'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Asistencias</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        main { max-width: 700px; margin: 2.5rem auto 0 auto; padding: 0 1rem 2rem 1rem; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #005baa; color: #fff; }
        tr:hover { background: #f1f7ff; }
        h1 { color: #005baa; }
        .btn-asistencia { background: #28a745; color: #fff; border: none; border-radius: 6px; padding: 6px 16px; font-size: 1.1rem; cursor: pointer; font-weight: bold; }
        .btn-asistencia:disabled { background: #ccc; cursor: not-allowed; }
        .asistido { color: #28a745; font-weight: bold; }
        .volver { display: inline-block; margin-bottom: 1rem; font-size: 1rem; color: #005baa; text-decoration: none; }
        .volver:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main>
        <h1>Registrar Asistencias</h1>
        <a class="volver" href="dashboard.php">&larr; Volver al Dashboard</a>
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                ✅ Asistencia registrada correctamente
            </div>
        <?php endif; ?>
        <table>
            <thead>
                <tr>
                    <th>Empleado</th>
                    <th>Asistencia</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td>
                                <?php if (in_array($row['id'], $asistencias)): ?>
                                    <span class="asistido">✔ Asistió</span>
                                <?php else: ?>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="empleado_id" value="<?= $row['id'] ?>">
                                        <button type="submit" class="btn-asistencia">+</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No hay empleados activos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
