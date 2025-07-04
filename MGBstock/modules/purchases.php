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
        $proveedor = sanitize_input($_POST['proveedor']);
        $notas = sanitize_input($_POST['notas']);
        
        if ($producto_id == 0 || $cantidad <= 0 || $precio_unitario <= 0) {
            showAlert('Todos los campos son requeridos y deben ser válidos', 'danger');
        } else {
            try {
                // Verificar que el producto existe
                $stmt = $conn->prepare("SELECT nombre FROM productos WHERE id = ? AND empresa_id = ? AND activo = 1");
                $stmt->execute([$producto_id, $_SESSION['empresa_id']]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$producto) {
                    throw new Exception('Producto no encontrado');
                }
                
                // Calcular total
                $total = $cantidad * $precio_unitario;
                
                // Registrar la compra
                $stmt = $conn->prepare("
                    INSERT INTO compras (empresa_id, producto_id, cantidad, precio_unitario, total, proveedor, notas) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_SESSION['empresa_id'], $producto_id, $cantidad, 
                    $precio_unitario, $total, $proveedor, $notas
                ]);
                
                showAlert('Compra registrada exitosamente. Total: ' . formatPrice($total), 'success');
                
                // Si es petición AJAX, devolver JSON
                if (isset($_POST['ajax'])) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Compra registrada exitosamente']);
                    exit;
                }
                
            } catch (Exception $e) {
                $message = 'Error al registrar compra: ' . $e->getMessage();
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
        SELECT p.id, p.nombre, p.codigo, p.precio_compra, p.stock_actual, c.nombre as categoria_nombre
        FROM productos p 
        INNER JOIN categorias c ON p.categoria_id = c.id
        WHERE p.empresa_id = ? AND p.activo = 1
        ORDER BY p.nombre
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar productos: ' . $e->getMessage(), 'danger');
}

// Obtener lista de compras
$compras = [];
try {
    $stmt = $conn->prepare("
        SELECT c.*, p.nombre as producto_nombre, p.codigo as producto_codigo
        FROM compras c 
        INNER JOIN productos p ON c.producto_id = p.id
        WHERE c.empresa_id = ? 
        ORDER BY c.fecha_compra DESC
        LIMIT 50
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar compras: ' . $e->getMessage(), 'danger');
}

// Calcular estadísticas de compras
$stats = ['total_compras_hoy' => 0, 'cantidad_compras_hoy' => 0, 'total_compras_mes' => 0];
try {
    // Compras de hoy
    $stmt = $conn->prepare("
        SELECT COUNT(*) as cantidad, COALESCE(SUM(total), 0) as total
        FROM compras 
        WHERE empresa_id = ? AND DATE(fecha_compra) = CURDATE()
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $hoy = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['cantidad_compras_hoy'] = $hoy['cantidad'];
    $stats['total_compras_hoy'] = $hoy['total'];
    
    // Compras del mes
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(total), 0) as total
        FROM compras 
        WHERE empresa_id = ? AND MONTH(fecha_compra) = MONTH(CURDATE()) AND YEAR(fecha_compra) = YEAR(CURDATE())
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $stats['total_compras_mes'] = $stmt->fetchColumn();
    
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
    <title>Gestión de Compras - <?php echo SITE_NAME; ?></title>
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
                    <a href="sales.php" class="nav-link">Ventas</a>
                </li>
                <li class="nav-item">
                    <a href="purchases.php" class="nav-link active">Compras</a>
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

        <!-- Estadísticas de compras -->
        <div class="dashboard-grid">
            <div class="stat-card" style="background: linear-gradient(135deg, #8e44ad, #9b59b6);">
                <div class="stat-number"><?php echo $stats['cantidad_compras_hoy']; ?></div>
                <div class="stat-label">Compras Hoy</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #e67e22, #f39c12);">
                <div class="stat-number"><?php echo formatPrice($stats['total_compras_hoy']); ?></div>
                <div class="stat-label">Total Hoy</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #16a085, #1abc9c);">
                <div class="stat-number"><?php echo formatPrice($stats['total_compras_mes']); ?></div>
                <div class="stat-label">Total Este Mes</div>
            </div>
        </div>

        <?php if (empty($productos)): ?>
            <div class="alert alert-warning">
                <strong>¡Atención!</strong> Debe crear productos antes de registrar compras.
                <a href="products.php" class="btn btn-sm btn-primary">Ir a Productos</a>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Gestión de Compras</h1>
                <?php if (!empty($productos)): ?>
                    <button onclick="PurchaseManager.showAddForm()" class="btn btn-primary">
                        📥 Registrar Compra
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($compras)): ?>
                    <div class="alert alert-info">
                        No hay compras registradas para esta empresa. Registre la primera compra para comenzar.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Producto</th>
                                    <th>Proveedor</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Total</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($compras as $compra): ?>
                                    <tr>
                                        <td><?php echo formatDate($compra['fecha_compra']); ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($compra['producto_nombre']); ?></strong>
                                            <br><small style="color: #666;">Código: <?php echo htmlspecialchars($compra['producto_codigo']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($compra['proveedor'] ?: 'No especificado'); ?></td>
                                        <td><?php echo $compra['cantidad']; ?></td>
                                        <td><?php echo formatPrice($compra['precio_unitario']); ?></td>
                                        <td><strong><?php echo formatPrice($compra['total']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($compra['notas']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($compras) == 50): ?>
                        <div class="alert alert-info">
                            Mostrando las últimas 50 compras. Para ver más registros, use el módulo de estadísticas.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal para registrar compra -->
    <div id="addPurchaseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Registrar Nueva Compra</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addPurchaseForm" onsubmit="event.preventDefault(); PurchaseManager.add();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="form-group">
                        <label for="compra_producto" class="form-label">Producto *</label>
                        <select id="compra_producto" name="producto_id" class="form-control" required onchange="PurchaseManager.updateProductInfo()">
                            <option value="">Seleccionar producto</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo $producto['id']; ?>" 
                                        data-precio="<?php echo $producto['precio_compra']; ?>"
                                        data-stock="<?php echo $producto['stock_actual']; ?>">
                                    <?php echo htmlspecialchars($producto['nombre']); ?> 
                                    (Stock actual: <?php echo $producto['stock_actual']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="product_info" class="mt-1" style="font-size: 0.9rem; color: #666;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="compra_proveedor" class="form-label">Proveedor</label>
                        <input type="text" id="compra_proveedor" name="proveedor" class="form-control" 
                               placeholder="Nombre del proveedor">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="compra_cantidad" class="form-label">Cantidad *</label>
                            <input type="number" id="compra_cantidad" name="cantidad" 
                                   class="form-control" min="1" required
                                   onchange="PurchaseManager.updateTotal();">
                        </div>
                        
                        <div class="form-group">
                            <label for="compra_precio" class="form-label">Precio Unitario *</label>
                            <input type="number" id="compra_precio" name="precio_unitario" 
                                   class="form-control" step="0.01" min="0.01" required
                                   onchange="PurchaseManager.updateTotal();">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="compra_total" class="form-label">Total</label>
                        <input type="number" id="compra_total" class="form-control" 
                               step="0.01" readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label for="compra_notas" class="form-label">Notas</label>
                        <textarea id="compra_notas" name="notas" class="form-control" rows="3" 
                                  placeholder="Información adicional sobre la compra"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn" style="background: #95a5a6;" onclick="MGBStock.hideModal('addPurchaseModal')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Registrar Compra
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Funciones adicionales para compras
        PurchaseManager.updateProductInfo = function() {
            const productSelect = document.getElementById('compra_producto');
            const priceField = document.getElementById('compra_precio');
            const productInfo = document.getElementById('product_info');
            
            if (productSelect.value) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const precio = selectedOption.dataset.precio;
                const stock = selectedOption.dataset.stock;
                
                if (precio && precio > 0) {
                    priceField.value = precio;
                }
                
                productInfo.innerHTML = `Stock actual: ${stock} unidades`;
                
                this.updateTotal();
            } else {
                priceField.value = '';
                productInfo.innerHTML = '';
            }
        };
    </script>
</body>
</html>
