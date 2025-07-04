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
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $categoria_id = (int)$_POST['categoria_id'];
                $codigo = sanitize_input($_POST['codigo']);
                $nombre = sanitize_input($_POST['nombre']);
                $descripcion = sanitize_input($_POST['descripcion']);
                $precio_venta = (float)$_POST['precio_venta'];
                $precio_compra = (float)$_POST['precio_compra'];
                $stock_inicial = (int)$_POST['stock_inicial'];
                $stock_minimo = (int)$_POST['stock_minimo'];
                
                if (empty($nombre) || $categoria_id == 0) {
                    showAlert('El nombre del producto y la categoría son requeridos', 'danger');
                } else {
                    try {
                        // Generar código si está vacío
                        if (empty($codigo)) {
                            $codigo = generateProductCode();
                        }
                        
                        $stmt = $conn->prepare("
                            INSERT INTO productos (empresa_id, categoria_id, codigo, nombre, descripcion, 
                                                 precio_venta, precio_compra, stock_actual, stock_minimo) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $_SESSION['empresa_id'], $categoria_id, $codigo, $nombre, $descripcion,
                            $precio_venta, $precio_compra, $stock_inicial, $stock_minimo
                        ]);
                        
                        showAlert('Producto agregado exitosamente', 'success');
                        
                        // Si es petición AJAX, devolver JSON
                        if (isset($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Producto agregado exitosamente']);
                            exit;
                        }
                        
                    } catch (Exception $e) {
                        $message = 'Error al agregar producto: ' . $e->getMessage();
                        showAlert($message, 'danger');
                        
                        if (isset($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => $message]);
                            exit;
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $categoria_id = (int)$_POST['categoria_id'];
                $codigo = sanitize_input($_POST['codigo']);
                $nombre = sanitize_input($_POST['nombre']);
                $descripcion = sanitize_input($_POST['descripcion']);
                $precio_venta = (float)$_POST['precio_venta'];
                $precio_compra = (float)$_POST['precio_compra'];
                $stock_minimo = (int)$_POST['stock_minimo'];
                
                if (empty($nombre) || $categoria_id == 0) {
                    showAlert('El nombre del producto y la categoría son requeridos', 'danger');
                } else {
                    try {
                        $stmt = $conn->prepare("
                            UPDATE productos 
                            SET categoria_id = ?, codigo = ?, nombre = ?, descripcion = ?, 
                                precio_venta = ?, precio_compra = ?, stock_minimo = ?
                            WHERE id = ? AND empresa_id = ?
                        ");
                        $stmt->execute([
                            $categoria_id, $codigo, $nombre, $descripcion,
                            $precio_venta, $precio_compra, $stock_minimo, $id, $_SESSION['empresa_id']
                        ]);
                        
                        showAlert('Producto actualizado exitosamente', 'success');
                        
                    } catch (Exception $e) {
                        showAlert('Error al actualizar producto: ' . $e->getMessage(), 'danger');
                    }
                }
                break;
        }
    }
}

// Procesar GET actions
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("UPDATE productos SET activo = 0 WHERE id = ? AND empresa_id = ?");
        $stmt->execute([$id, $_SESSION['empresa_id']]);
        showAlert('Producto eliminado exitosamente', 'success');
    } catch (Exception $e) {
        showAlert('Error al eliminar producto: ' . $e->getMessage(), 'danger');
    }
}

// Obtener producto para editar
$producto_edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ? AND empresa_id = ? AND activo = 1");
        $stmt->execute([$edit_id, $_SESSION['empresa_id']]);
        $producto_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        showAlert('Error al cargar producto: ' . $e->getMessage(), 'danger');
    }
}

// Obtener categorías para el select
$categorias = [];
try {
    $stmt = $conn->prepare("SELECT * FROM categorias WHERE empresa_id = ? AND activo = 1 ORDER BY nombre");
    $stmt->execute([$_SESSION['empresa_id']]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar categorías: ' . $e->getMessage(), 'danger');
}

// Obtener lista de productos
$productos = [];
try {
    $stmt = $conn->prepare("
        SELECT p.*, c.nombre as categoria_nombre 
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

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - <?php echo SITE_NAME; ?></title>
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
                    <a href="products.php" class="nav-link active">Productos</a>
                </li>
                <li class="nav-item">
                    <a href="sales.php" class="nav-link">Ventas</a>
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

        <?php if (empty($categorias)): ?>
            <div class="alert alert-warning">
                <strong>¡Atención!</strong> Debe crear al menos una categoría antes de agregar productos.
                <a href="categories.php" class="btn btn-sm btn-primary">Ir a Categorías</a>
            </div>
        <?php endif; ?>

        <!-- Formulario de edición (si se está editando) -->
        <?php if ($producto_edit): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Editar Producto</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $producto_edit['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_categoria_id" class="form-label">Categoría *</label>
                                <select id="edit_categoria_id" name="categoria_id" class="form-control" required>
                                    <option value="">Seleccionar categoría</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?php echo $categoria['id']; ?>" 
                                                <?php echo ($producto_edit['categoria_id'] == $categoria['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($categoria['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_codigo" class="form-label">Código</label>
                                <input type="text" id="edit_codigo" name="codigo" class="form-control" 
                                       value="<?php echo htmlspecialchars($producto_edit['codigo']); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_nombre" class="form-label">Nombre del Producto *</label>
                            <input type="text" id="edit_nombre" name="nombre" class="form-control" 
                                   value="<?php echo htmlspecialchars($producto_edit['nombre']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea id="edit_descripcion" name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($producto_edit['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_precio_venta" class="form-label">Precio de Venta</label>
                                <input type="number" id="edit_precio_venta" name="precio_venta" 
                                       class="form-control" step="0.01" min="0" 
                                       value="<?php echo $producto_edit['precio_venta']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_precio_compra" class="form-label">Precio de Compra</label>
                                <input type="number" id="edit_precio_compra" name="precio_compra" 
                                       class="form-control" step="0.01" min="0" 
                                       value="<?php echo $producto_edit['precio_compra']; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_stock_minimo" class="form-label">Stock Mínimo</label>
                                <input type="number" id="edit_stock_minimo" name="stock_minimo" 
                                       class="form-control" min="0" 
                                       value="<?php echo $producto_edit['stock_minimo']; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Stock Actual</label>
                                <input type="text" class="form-control" 
                                       value="<?php echo $producto_edit['stock_actual']; ?>" readonly>
                                <small style="color: #666;">El stock se modifica con ventas y compras</small>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between gap-2">
                            <a href="products.php" class="btn" style="background: #95a5a6;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Actualizar Producto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Gestión de Productos</h1>
                <?php if (!$producto_edit && !empty($categorias)): ?>
                    <button onclick="ProductManager.showAddForm()" class="btn btn-primary">
                        ➕ Agregar Producto
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($productos)): ?>
                    <div class="alert alert-info">
                        No hay productos registrados para esta empresa. Agregue el primer producto para comenzar.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio Venta</th>
                                    <th>Stock</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td><code><?php echo htmlspecialchars($producto['codigo']); ?></code></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                            <?php if ($producto['descripcion']): ?>
                                                <br><small style="color: #666;"><?php echo htmlspecialchars($producto['descripcion']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                                        <td><?php echo formatPrice($producto['precio_venta']); ?></td>
                                        <td>
                                            <?php 
                                            $stock_class = 'btn-success';
                                            if ($producto['stock_actual'] <= $producto['stock_minimo']) {
                                                $stock_class = 'btn-danger';
                                            } elseif ($producto['stock_actual'] <= ($producto['stock_minimo'] * 1.5)) {
                                                $stock_class = 'btn-warning';
                                            }
                                            ?>
                                            <span class="btn btn-sm <?php echo $stock_class; ?>">
                                                <?php echo $producto['stock_actual']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($producto['stock_actual'] <= $producto['stock_minimo']): ?>
                                                <span style="color: #e74c3c; font-weight: bold;">⚠️ Stock Bajo</span>
                                            <?php else: ?>
                                                <span style="color: #27ae60;">✅ Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="products.php?edit=<?php echo $producto['id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    ✏️ Editar
                                                </a>
                                                <a href="products.php?delete=<?php echo $producto['id']; ?>" 
                                                   class="btn btn-sm btn-danger btn-delete">
                                                    🗑️ Eliminar
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal para agregar producto -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Nuevo Producto</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addProductForm" onsubmit="event.preventDefault(); ProductManager.add();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="categoria_id" class="form-label">Categoría *</label>
                            <select id="categoria_id" name="categoria_id" class="form-control" required>
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>">
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="producto_codigo" class="form-label">Código</label>
                            <div class="d-flex gap-1">
                                <input type="text" id="producto_codigo" name="codigo" class="form-control">
                                <button type="button" class="btn btn-sm btn-info" onclick="ProductManager.generateCode()">
                                    🎲 Generar
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="producto_nombre" class="form-label">Nombre del Producto *</label>
                        <input type="text" id="producto_nombre" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="producto_descripcion" class="form-label">Descripción</label>
                        <textarea id="producto_descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="precio_venta" class="form-label">Precio de Venta</label>
                            <input type="number" id="precio_venta" name="precio_venta" 
                                   class="form-control" step="0.01" min="0" value="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="precio_compra" class="form-label">Precio de Compra</label>
                            <input type="number" id="precio_compra" name="precio_compra" 
                                   class="form-control" step="0.01" min="0" value="0.00">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="stock_inicial" class="form-label">Stock Inicial</label>
                            <input type="number" id="stock_inicial" name="stock_inicial" 
                                   class="form-control" min="0" value="0">
                        </div>
                        
                        <div class="form-group">
                            <label for="stock_minimo" class="form-label">Stock Mínimo</label>
                            <input type="number" id="stock_minimo" name="stock_minimo" 
                                   class="form-control" min="0" value="0">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn" style="background: #95a5a6;" onclick="MGBStock.hideModal('addProductModal')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Agregar Producto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
