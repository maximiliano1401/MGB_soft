<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'conexion.php';

// Obtener estadísticas
$stats = [];

// Total de empresas
$result = $conn->query("SELECT COUNT(*) as total FROM empresas");
$stats['empresas'] = $result->fetch_assoc()['total'];

// Total de departamentos
$result = $conn->query("SELECT COUNT(*) as total FROM departamentos");
$stats['departamentos'] = $result->fetch_assoc()['total'];

// Total de empleados
$result = $conn->query("SELECT COUNT(*) as total FROM empleados");
$stats['empleados'] = $result->fetch_assoc()['total'];

// Empleados activos
$result = $conn->query("SELECT COUNT(*) as total FROM altas_bajas WHERE estado = 'activo'");
$stats['empleados_activos'] = $result->fetch_assoc()['total'];

// Ausencias del mes actual
$result = $conn->query("SELECT COUNT(*) as total FROM ausencias WHERE MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) AND YEAR(fecha_inicio) = YEAR(CURRENT_DATE())");
$stats['ausencias_mes'] = $result->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | MGB Software</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f6f8fa; color: #222; margin: 0; }
        header {
            background: linear-gradient(120deg, #005baa 60%, #00bcd4 100%);
            color: #fff;
            padding: 1.5rem 1rem;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logo-section img {
            height: 50px;
            border-radius: 8px;
            background: #fff;
            border: 2px solid #00bcd4;
        }
        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        nav {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .nav-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 0 1rem;
        }
        .nav-content a {
            color: #005baa;
            text-decoration: none;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            transition: background 0.2s;
        }
        .nav-content a:hover {
            background: #f0f7ff;
        }
        main {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #005baa;
            margin-bottom: 0.5rem;
        }
        .stat-label {
            color: #666;
            font-weight: 500;
        }
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        .action-section {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .action-section h3 {
            color: #005baa;
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .action-btn {
            background: #f8f9fa;
            border: 1px solid #e1e5e9;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            transition: all 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .action-btn:hover {
            background: #005baa;
            color: #fff;
            border-color: #005baa;
        }
        .welcome-card {
            background: linear-gradient(135deg, #005baa 0%, #00bcd4 100%);
            color: #fff;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
            }
            .nav-content {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo-section">
                <img src="IMG/logo.png" alt="Logo MGB">
                <h1>Sistema de Recursos Humanos</h1>
            </div>
            <div class="user-section">
                <span>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?></span>
                <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <nav>
        <div class="nav-content">
            <a href="registro_empresas.php">Registrar Empresa</a>
            <a href="registro_departamentos_puestos.php">Departamentos/Puestos</a>
            <a href="registro_empleados.php">Registrar Empleados</a>
            <a href="registro_nomina.php">Gestionar Nómina</a>
            <a href="Ver_Registrados/ver_Registro_Empleados.php">Ver Empleados</a>
        </div>
    </nav>

    <main>
        <div class="welcome-card">
            <h2>Panel de Administración</h2>
            <p>Gestiona todos los aspectos de recursos humanos desde un solo lugar</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $stats['empresas'] ?></div>
                <div class="stat-label">Empresas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['departamentos'] ?></div>
                <div class="stat-label">Departamentos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['empleados'] ?></div>
                <div class="stat-label">Total Empleados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['empleados_activos'] ?></div>
                <div class="stat-label">Empleados Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $stats['ausencias_mes'] ?></div>
                <div class="stat-label">Ausencias Este Mes</div>
            </div>
        </div>

        <div class="actions-grid">
            <div class="action-section">
                <h3>Gestión de Personal</h3>
                <div class="action-buttons">
                    <a href="registro_empleados.php" class="action-btn">
                        <span>Registrar Nuevo Empleado</span>
                        <span>→</span>
                    </a>
                    <a href="Ver_Registrados/ver_Registro_Empleados.php" class="action-btn">
                        <span>Ver Todos los Empleados</span>
                        <span>→</span>
                    </a>
                    <a href="registro_altas_bajas.php" class="action-btn">
                        <span>Gestionar Altas/Bajas</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h3>Estructura Organizacional</h3>
                <div class="action-buttons">
                    <a href="registro_empresas.php" class="action-btn">
                        <span>Registrar Empresa</span>
                        <span>→</span>
                    </a>
                    <a href="registro_departamentos_puestos.php" class="action-btn">
                        <span>Gestionar Departamentos</span>
                        <span>→</span>
                    </a>
                    <a href="Ver_Registrados/Ver_Registro_puestos.php" class="action-btn">
                        <span>Ver Estructura</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h3>Ausencias y Permisos</h3>
                <div class="action-buttons">
                    <a href="registro_ausencias.php" class="action-btn">
                        <span>Registrar Ausencia</span>
                        <span>→</span>
                    </a>
                    <a href="Ver_Registrados/Ver_Registro_Ausencias.php" class="action-btn">
                        <span>Ver Ausencias</span>
                        <span>→</span>
                    </a>
                </div>
            </div>

            <div class="action-section">
                <h3>Nómina</h3>
                <div class="action-buttons">
                    <a href="registro_nomina.php" class="action-btn">
                        <span>Configurar Nómina</span>
                        <span>→</span>
                    </a>
                    <a href="Ver_Registrados/Ver_Registro_Nomina.php" class="action-btn">
                        <span>Ver Nóminas</span>
                        <span>→</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
