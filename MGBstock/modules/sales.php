<?php
require_once '../includes/config.php';

// Verificar que esté logueado y tenga empresa seleccionada
if (!isLoggedIn()) {
    redirect('../index.php');
}

if (!hasSelectedCompany()) {
    showAlert('Debe seleccionar una empresa primero', 'warning');
    redirect('companies.php');
}

$db = new Database();
$conn = $db->getConnection();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $producto_id = (int)$_POST['producto_id'];
        $cantidad = (int)$_POST['cantidad'];
        $precio_unitario = (float)$_POST['precio_unitario'];
        $notas = sanitize_input($_POST['notas']);
        
        if ($producto_id == 0 || $cantidad <= 0 || $precio_unitario <= 0) {
            showAlert('Todos los campos son requeridos y deben ser válidos', 'danger');
        } else {
            try {
                // Verificar stock disponible
                $stmt = $conn->prepare("SELECT nombre, stock_actual FROM productos WHERE id = ? AND empresa_id = ? AND activo = 1");
                $stmt->execute([$producto_id, $_SESSION['empresa_id']]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$producto) {
                    throw new Exception('Producto no encontrado');
                }
                
                if ($producto['stock_actual'] < $cantidad) {
                    throw new Exception('Stock insuficiente. Disponible: ' . $producto['stock_actual']);
                }
                
                // Calcular total
                $total = $cantidad * $precio_unitario;
                
                // Registrar la venta
                $stmt = $conn->prepare("
                    INSERT INTO ventas (empresa_id, producto_id, cantidad, precio_unitario, total, notas) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['empresa_id'], $producto_id, $cantidad, 
                    $precio_unitario, $total, $notas
                ]);
                
                showAlert('Venta registrada exitosamente. Total: ' . formatPrice($total), 'success');
                
                // Si es petición AJAX, devolver JSON
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Venta registrada exitosamente']);
                    exit;
                }
                
            } catch (Exception $e) {
                $message = 'Error al registrar venta: ' . $e->getMessage();
                showAlert($message, 'danger');
                
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
            }
        }
    }
}

// Obtener productos para el select
$productos = [];
try {
    $stmt = $conn->prepare("
        SELECT p.id, p.nombre, p.codigo, p.precio_venta, p.stock_actual, c.nombre as categoria_nombre
        FROM productos p 
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE p.empresa_id = ? AND p.activo = 1 AND p.stock_actual > 0
        ORDER BY p.nombre
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar productos: ' . $e->getMessage(), 'danger');
}

// Obtener lista de ventas
$ventas = [];
try {
    $stmt = $conn->prepare("
        SELECT v.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM ventas v 
        INNER JOIN productos p ON v.producto_id = p.id
        WHERE v.empresa_id = ? 
        ORDER BY v.fecha_venta DESC
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar ventas: ' . $e->getMessage(), 'danger');
}

// Calcular estadísticas de ventas
$stats = ['total_ventas_hoy' => 0, 'cantidad_ventas_hoy' => 0, 'total_ventas_mes' => 0];
try {
    // Ventas de hoy
    $stmt = $conn->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM ventas 
        WHERE empresa_id = ? AND DATE(fecha_venta) = CURDATE()
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $hoy = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['cantidad_ventas_hoy'] = $hoy['cantidad'];
    $stats['total_ventas_hoy'] = $hoy['total'];
    
    // Ventas del mes
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total
        FROM ventas 
        WHERE empresa_id = ? AND MONTH(fecha_venta) = MONTH(CURDATE()) AND YEAR(fecha_venta) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $stats['total_ventas_mes'] = $stmt->fetchColumn();
    
} catch (Exception $e) {
    // Error al cargar estadísticas
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Ventas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">MGBStock</div>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['admin_nombre']; ?></span>
                <span>Empresa: <?php echo $_SESSION['empresa_nombre']; ?></span>
                <a href="../logout.php" class="btn btn-sm" style="background: rgba(255,255,255,0.2);">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-content">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="../dashboard.php" class="nav-link">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="companies.php" class="nav-link">Empresas</a>
                </li>
                <li class="nav-item">
                    <a href="categories.php" class="nav-link">Categorías</a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">Productos</a>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link active">Ventas</a>
                </li>
                <li class="nav-item">
                    <a href="purchases.php" class="nav-link">Compras</a>
                </li>
                <li class="nav-item">
                    <a href="statistics.php" class="nav-link">Estadísticas</a>
                </li>
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

        <!-- Estadísticas de ventas -->
        <div class="dashboard-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #27ae60, #2ed573);">
                <div class="stat-number"><?php echo $stats['cantidad_ventas_hoy']; ?></div>
                <div class="stat-label">Ventas Hoy</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #3498db, #74b9ff);">
                <div class="stat-number"><?php echo formatPrice($stats['total_ventas_hoy']); ?></div>
                <div class="stat-label">Total Hoy</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #f39c12, #fdcb6e);">
                <div class="stat-number"><?php echo formatPrice($stats['total_ventas_mes']); ?></div>
                <div class="stat-label">Total Este Mes</div>
            </div>
        </div>

        <?php if (empty($productos)): ?>
            <div class="alert alert-warning">
                <strong>¡Atención!</strong> No hay productos con stock disponible para vender.
                <a href="products.php" class="btn btn-sm btn-primary">Ir a Productos</a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Gestión de Ventas</h1>
                <?php if (!empty($productos)): ?>
                    <button onclick="SalesManager.showAddForm()" class="btn btn-primary">
                        🛒 Registrar Venta
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($ventas)): ?>
                    <div class="alert alert-info">
                        No hay ventas registradas para esta empresa. Registre la primera venta para comenzar.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Total</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $venta): ?>
                                    <tr>
                                        <td><?php echo formatDate($venta['fecha_venta']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($venta['producto_nombre']); ?></strong>
                                            <br><small style="color: #666;">Código: <?php echo htmlspecialchars($venta['producto_codigo']); ?></small>
                                        </td>
                                        <td><?php echo $venta['cantidad']; ?></td>
                                        <td><?php echo formatPrice($venta['precio_unitario']); ?></td>
                                        <td><strong><?php echo formatPrice($venta['total']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($venta['notas']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($ventas) == 50): ?>
                        <div class="alert alert-info">
                            Mostrando las últimas 50 ventas. Para ver más registros, use el módulo de estadísticas.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal para registrar venta -->
    <div id="addSaleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Registrar Nueva Venta</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addSaleForm" onsubmit="event.preventDefault(); SalesManager.add();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="form-group">
                        <label for="venta_producto" class="form-label">Producto *</label>
                        <select id="venta_producto" name="producto_id" class="form-control" required onchange="SalesManager.updateProductInfo()">
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" 
                                        data-precio="<?php echo $producto['precio_venta']; ?>"
                                        data-stock="<?php echo $producto['stock_actual']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?> 
                                    (Stock: <?php echo $producto['stock_actual']; ?>) - 
                                    <?php echo formatPrice($producto['precio_venta']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="stock_info" class="mt-1" style="font-size: 0.9rem; color: #666;"></div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="venta_cantidad" class="form-label">Cantidad *</label>
                            <input type="number" id="venta_cantidad" name="cantidad" 
                                   class="form-control" min="1" required
                                   onchange="SalesManager.checkStock(); SalesManager.updateTotal();">
                        </div>
                        
                        <div class="form-group">
                            <label for="venta_precio" class="form-label">Precio Unitario *</label>
                            <input type="number" id="venta_precio" name="precio_unitario" 
                                   class="form-control" step="0.01" min="0.01" required
                                   onchange="SalesManager.updateTotal();">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="venta_total" class="form-label">Total</label>
                        <input type="number" id="venta_total" class="form-control" 
                               step="0.01" readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label for="venta_notas" class="form-label">Notas</label>
                        <textarea id="venta_notas" name="notas" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn" style="background: #95a5a6;" onclick="MGBStock.hideModal('addSaleModal')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Registrar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Funciones adicionales para ventas
        SalesManager.updateProductInfo = function() {
            const productSelect = document.getElementById('venta_producto');
            const priceField = document.getElementById('venta_precio');
            const stockInfo = document.getElementById('stock_info');
            
            if (productSelect.value) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const precio = selectedOption.dataset.precio;
                const stock = selectedOption.dataset.stock;
                
                priceField.value = precio;
                stockInfo.innerHTML = `Stock disponible: ${stock} unidades`;
                
                this.updateTotal();
            } else {
                priceField.value = '';
                stockInfo.innerHTML = '';
            }
        };
    </script>
</body>
</html>
