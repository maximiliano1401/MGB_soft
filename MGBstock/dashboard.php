<?php
require_once 'includes/config.php';

// Verificar que esté logueado
if (!isLoggedIn()) {
    redirect('index.php');
}

// Obtener estadísticas generales
$db = new Database();
$conn = $db->getConnection();

$stats = [
    'empresas' => 0,
    'productos' => 0,
    'ventas_hoy' => 0,
    'stock_bajo' => 0
];

try {
    // Total de empresas
    $stmt = $conn->query("SELECT COUNT(*) FROM empresas WHERE activo = 1");
    $stats['empresas'] = $stmt->fetchColumn();
    
    // Total de productos (si hay empresa seleccionada)
    if (hasSelectedCompany()) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND activo = 1");
        $stmt->execute([$_SESSION['empresa_id']]);
        $stats['productos'] = $stmt->fetchColumn();
        
        // Ventas de hoy
        $stmt = $conn->prepare("SELECT COUNT(*) FROM ventas WHERE empresa_id = ? AND DATE(fecha_venta) = CURDATE()");
        $stmt->execute([$_SESSION['empresa_id']]);
        $stats['ventas_hoy'] = $stmt->fetchColumn();
        
        // Productos con stock bajo
        $stmt = $conn->prepare("SELECT COUNT(*) FROM productos WHERE empresa_id = ? AND stock_actual <= stock_minimo AND activo = 1");
        $stmt->execute([$_SESSION['empresa_id']]);
        $stats['stock_bajo'] = $stmt->fetchColumn();
    }
    
} catch (Exception $e) {
    showAlert('Error al cargar estadísticas: ' . $e->getMessage(), 'danger');
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">MGBStock</div>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['admin_nombre']; ?></span>
                <?php if (hasSelectedCompany()): ?>
                    <span>Empresa ID: <?php echo $_SESSION['empresa_id']; ?></span>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-sm" style="background: rgba(255,255,255,0.2);">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="modules/companies.php" class="nav-link">Empresas</a>
                </li>
                <?php if (hasSelectedCompany()): ?>
                    <li class="nav-item">
                        <a href="modules/categories.php" class="nav-link">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/products.php" class="nav-link">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/sales.php" class="nav-link">Ventas</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/purchases.php" class="nav-link">Compras</a>
                    </li>
                    <li class="nav-item">
                        <a href="modules/statistics.php" class="nav-link">Estadísticas</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <?php if ($alert): ?>
            <div class="alert alert-<?php echo $alert['type']; ?>">
                <?php echo $alert['message']; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Panel de Control</h1>
            </div>
            <div class="card-body">
                <?php if (!hasSelectedCompany()): ?>
                    <div class="alert alert-info">
                        <strong>¡Atención!</strong> Para acceder a todas las funcionalidades del sistema, 
                        primero debe <a href="modules/companies.php">seleccionar una empresa</a> con la cual trabajar.
                    </div>
                <?php endif; ?>

                <!-- Estadísticas -->
                <div class="dashboard-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $stats['empresas']; ?></div>
                        <div class="stat-label">Empresas Registradas</div>
                    </div>
                    
                    <?php if (hasSelectedCompany()): ?>
                        <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #2ed573);">
                            <div class="stat-number"><?php echo $stats['productos']; ?></div>
                            <div class="stat-label">Productos</div>
                        </div>
                        
                        <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #f1c40f);">
                            <div class="stat-number"><?php echo $stats['ventas_hoy']; ?></div>
                            <div class="stat-label">Ventas Hoy</div>
                        </div>
                        
                        <div class="stat-card" style="background: linear-gradient(135deg, #e74c3c, #ff6b6b);">
                            <div class="stat-number"><?php echo $stats['stock_bajo']; ?></div>
                            <div class="stat-label">Stock Bajo</div>
                        </div>
                    <?php else: ?>
                        <div class="stat-card" style="background: linear-gradient(135deg, #95a5a6, #bdc3c7);">
                            <div class="stat-number">-</div>
                            <div class="stat-label">Seleccione Empresa</div>
                        </div>
                        
                        <div class="stat-card" style="background: linear-gradient(135deg, #95a5a6, #bdc3c7);">
                            <div class="stat-number">-</div>
                            <div class="stat-label">Seleccione Empresa</div>
                        </div>
                        
                        <div class="stat-card" style="background: linear-gradient(135deg, #95a5a6, #bdc3c7);">
                            <div class="stat-number">-</div>
                            <div class="stat-label">Seleccione Empresa</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Acciones Rápidas -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Acciones Rápidas</h2>
                    </div>
                    <div class="card-body">
                        <div class="dashboard-grid">
                            <a href="modules/companies.php" class="btn btn-primary btn-lg">
                                📢 Gestionar Empresas
                            </a>
                            
                            <?php if (hasSelectedCompany()): ?>
                                <a href="modules/products.php" class="btn btn-success btn-lg">
                                    📦 Gestionar Productos
                                </a>
                                
                                <a href="modules/sales.php" class="btn btn-warning btn-lg">
                                    🛒 Registrar Venta
                                </a>
                                
                                <a href="modules/purchases.php" class="btn btn-info btn-lg">
                                    📥 Registrar Compra
                                </a>
                            <?php else: ?>
                                <button class="btn btn-lg" style="background: #95a5a6;" disabled>
                                    📦 Gestionar Productos
                                </button>
                                
                                <button class="btn btn-lg" style="background: #95a5a6;" disabled>
                                    🛒 Registrar Venta
                                </button>
                                
                                <button class="btn btn-lg" style="background: #95a5a6;" disabled>
                                    📥 Registrar Compra
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Información del Sistema -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Información del Sistema</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div>
                                <h3>MGBStock v1.0</h3>
                                <p>Sistema completo de gestión de inventario desarrollado con PHP, MySQL y JavaScript.</p>
                                <ul style="margin-top: 1rem;">
                                    <li>✅ Gestión de múltiples empresas</li>
                                    <li>✅ Control de categorías y productos</li>
                                    <li>✅ Registro de ventas y compras</li>
                                    <li>✅ Control automático de stock</li>
                                    <li>✅ Estadísticas con gráficos Python</li>
                                </ul>
                            </div>
                            <div>
                                <h3>Soporte Técnico</h3>
                                <p>Para soporte técnico o consultas, contacte con el administrador del sistema.</p>
                                <p><strong>Desarrollado por:</strong> MGBSoft</p>
                                <p><strong>Fecha:</strong> Julio 2025</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/main.js"></script>
</body>
</html>
