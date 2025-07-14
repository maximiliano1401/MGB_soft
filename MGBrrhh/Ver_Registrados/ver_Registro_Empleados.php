<?php
require_once '../auth.php';
require_once '../conexion.php';

// Consulta corregida para mostrar el estado real de los empleados
$sql = "SELECT e.id, e.nombre, e.apellido_paterno, e.apellido_materno, e.fecha_nacimiento, 
        e.sexo, e.lugar_nacimiento, e.imss, e.rfc, e.curp, 
        d.nombre AS departamento, p.nombre AS puesto,
        CASE 
            WHEN ab.estado = 'activo' THEN 'Activo'
            WHEN ab.estado = 'inactivo' THEN 'Inactivo'
            ELSE 'Sin registro'
        END as estado_laboral,
        ab.fecha_movimiento,
        ab.motivo
        FROM empleados e
        LEFT JOIN departamentos d ON e.departamento_id = d.id
        LEFT JOIN puestos p ON e.puesto_id = p.id
        LEFT JOIN (
            SELECT empleado_id, estado, fecha_movimiento, motivo,
                   ROW_NUMBER() OVER (PARTITION BY empleado_id ORDER BY fecha_movimiento DESC) as rn
            FROM altas_bajas
        ) ab ON e.id = ab.empleado_id AND ab.rn = 1
        ORDER BY e.id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Empleados Registrados</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        header {
            background: linear-gradient(120deg, #005baa 60%, #00bcd4 100%);
            color: #fff;
            padding: 1.5rem 1rem;
            text-align: center;
        }
        .logo-container img {
            height: 50px;
            border-radius: 8px;
            background: #fff;
            border: 2px solid #00bcd4;
            margin-bottom: 0.5rem;
        }
        h1 { margin: 0.5rem 0; font-size: 1.8rem; }
        nav { margin-top: 1rem; }
        nav a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 6px;
            background: rgba(0,0,0,0.1);
            margin: 0 0.5rem;
        }
        nav a:hover { background: rgba(0,0,0,0.2); }
        main { max-width: 1400px; margin: 2rem auto; padding: 0 1rem; }
        .actions-bar {
            background: #fff;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary { background: #005baa; color: #fff; }
        .btn-primary:hover { background: #004080; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #1e7e34; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th, td { padding: 12px 8px; border-bottom: 1px solid #e0e0e0; text-align: left; }
        th { background: #005baa; color: #fff; font-weight: 600; }
        tr:hover { background: #f8f9fa; }
        .acciones-btns { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .btn-sm {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            font-size: 0.85rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        .btn-editar { background: #ffc107; color: #212529; }
        .btn-editar:hover { background: #ffb300; }
        .btn-baja { background: #dc3545; color: #fff; }
        .btn-baja:hover { background: #c82333; }
        .btn-vacaciones { background: #17a2b8; color: #fff; }
        .btn-vacaciones:hover { background: #138496; }
        .btn-incapacidad { background: #fd7e14; color: #fff; }
        .btn-incapacidad:hover { background: #e55100; }
        .btn-falta { background: #6f42c1; color: #fff; }
        .btn-falta:hover { background: #5a2d8f; }
        .estado-activo { color: #28a745; font-weight: bold; }
        .estado-inactivo { color: #dc3545; font-weight: bold; }
        .estado-sin { color: #6c757d; font-style: italic; }
        @media (max-width: 1200px) {
            th, td { padding: 8px 4px; font-size: 0.9rem; }
            .acciones-btns { flex-direction: column; }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="../IMG/recursos.jpeg" alt="Logo MGB - Recursos Humanos">
        </div>
        <h1>Gestión de Empleados</h1>
        <nav>
            <a href="../dashboard.php">Dashboard</a>
            <a href="../registro_empleados.php">Nuevo Empleado</a>
        </nav>
    </header>
    <main>
        <?php if (isset($_GET['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                ✅ <?= htmlspecialchars($_GET['success']) ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 6px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                ❌ <?= htmlspecialchars($_GET['error']) ?>
            </div>
        <?php endif; ?>

        <div class="actions-bar">
            <a href="../registro_empleados.php" class="btn btn-primary">➕ Registrar Empleado</a>
            <a href="../registro_altas_bajas.php" class="btn btn-success">📋 Gestionar Altas/Bajas</a>
            <a href="../Ver_Registrados/Ver_Registro_Ausencias.php" class="btn btn-primary">📅 Ver Ausencias</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>RFC</th>
                    <th>IMSS</th>
                    <th>Departamento</th>
                    <th>Puesto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno'] . ' ' . $row['apellido_materno']) ?></td>
                            <td><?= htmlspecialchars($row['rfc']) ?></td>
                            <td><?= htmlspecialchars($row['imss']) ?></td>
                            <td><?= htmlspecialchars($row['departamento'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($row['puesto'] ?? 'N/A') ?></td>
                            <td class="estado-<?= strtolower(str_replace(' ', '-', $row['estado_laboral'])) ?>">
                                <?= $row['estado_laboral'] ?>
                            </td>
                            <td>
                                <div class="acciones-btns">
                                    <a href="../editar/empleados/editar_empleado.php?id=<?= $row['id'] ?>" class="btn-sm btn-editar" title="Editar">✏️</a>
                                    
                                    <?php if ($row['estado_laboral'] == 'Activo'): ?>
                                        <a href="javascript:void(0)" onclick="darDeBaja(<?= $row['id'] ?>)" class="btn-sm btn-baja" title="Dar de baja">❌</a>
                                    <?php elseif ($row['estado_laboral'] == 'Inactivo'): ?>
                                        <form method="post" action="../proceso_reactivar.php" style="display:inline;">
                                            <input type="hidden" name="empleado_id" value="<?= $row['id'] ?>">
                                            <input type="hidden" name="motivo" value="Reactivación">
                                            <button type="submit" class="btn-sm btn-success" title="Reactivar" onclick="return confirm('¿Seguro que deseas reactivar a este empleado?')">🔄 Reactivar</button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <a href="javascript:void(0)" onclick="registrarVacaciones(<?= $row['id'] ?>)" class="btn-sm btn-vacaciones" title="Vacaciones">🏖️</a>
                                    <a href="javascript:void(0)" onclick="registrarIncapacidad(<?= $row['id'] ?>)" class="btn-sm btn-incapacidad" title="Incapacidad">🏥</a>
                                    <a href="javascript:void(0)" onclick="registrarFalta(<?= $row['id'] ?>)" class="btn-sm btn-falta" title="Falta">⚠️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">No hay empleados registrados.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

    <!-- Modales para acciones rápidas -->
    <div id="modalAusencia" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
            <h3 id="modalTitle">Registrar Ausencia</h3>
            <form id="formAusencia" method="post" action="../proceso_ausencia.php">
                <input type="hidden" name="empleado_id" id="modalEmpleadoId">
                <input type="hidden" name="tipo" id="modalTipo">
                
                <div style="margin-bottom: 1rem;">
                    <label for="modalFechaInicio">Fecha de inicio:</label>
                    <input type="date" name="fecha_inicio" id="modalFechaInicio" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label for="modalFechaFin">Fecha de fin:</label>
                    <input type="date" name="fecha_fin" id="modalFechaFin" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px;">
                </div>
                
                <div style="margin-bottom: 1rem;">
                    <label for="modalMotivo">Motivo (opcional):</label>
                    <textarea name="motivo" id="modalMotivo" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-top: 5px; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="cerrarModal()" style="padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="padding: 8px 16px; border: none; background: #005baa; color: #fff; border-radius: 4px; cursor: pointer;">Registrar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function darDeBaja(empleadoId) {
            if (confirm('¿Estás seguro de dar de baja a este empleado?')) {
                const motivo = prompt('Ingresa el motivo de la baja:');
                if (motivo) {
                    window.location.href = `../proceso_baja.php?id=${empleadoId}&motivo=${encodeURIComponent(motivo)}`;
                }
            }
        }

        function registrarVacaciones(empleadoId) {
            abrirModal(empleadoId, 'vacaciones', 'Registrar Vacaciones');
        }

        function registrarIncapacidad(empleadoId) {
            abrirModal(empleadoId, 'incapacidad', 'Registrar Incapacidad');
        }

        function registrarFalta(empleadoId) {
            abrirModal(empleadoId, 'falta', 'Registrar Falta');
        }

        function abrirModal(empleadoId, tipo, titulo) {
            document.getElementById('modalEmpleadoId').value = empleadoId;
            document.getElementById('modalTipo').value = tipo;
            document.getElementById('modalTitle').textContent = titulo;
            document.getElementById('modalAusencia').style.display = 'block';
            
            // Establecer fecha de inicio como hoy
            const hoy = new Date().toISOString().split('T')[0];
            document.getElementById('modalFechaInicio').value = hoy;
            document.getElementById('modalFechaFin').value = hoy;
        }

        function cerrarModal() {
            document.getElementById('modalAusencia').style.display = 'none';
            document.getElementById('formAusencia').reset();
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalAusencia').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModal();
            }
        });
    </script>
</body>
</html>
