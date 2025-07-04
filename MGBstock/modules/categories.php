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
                $nombre = sanitize_input($_POST['nombre']);
                $descripcion = sanitize_input($_POST['descripcion']);
                
                if (empty($nombre)) {
                    showAlert('El nombre de la categoría es requerido', 'danger');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO categorias (empresa_id, nombre, descripcion) VALUES (?, ?, ?)");
                        $stmt->execute([$_SESSION['empresa_id'], $nombre, $descripcion]);
                        
                        showAlert('Categoría agregada exitosamente', 'success');
                        
                        // Si es petición AJAX, devolver JSON
                        if (isset($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Categoría agregada exitosamente']);
                            exit;
                        }
                        
                    } catch (Exception $e) {
                        $message = 'Error al agregar categoría: ' . $e->getMessage();
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
                $nombre = sanitize_input($_POST['nombre']);
                $descripcion = sanitize_input($_POST['descripcion']);
                
                if (empty($nombre)) {
                    showAlert('El nombre de la categoría es requerido', 'danger');
                } else {
                    try {
                        $stmt = $conn->prepare("UPDATE categorias SET nombre = ?, descripcion = ? WHERE id = ? AND empresa_id = ?");
                        $stmt->execute([$nombre, $descripcion, $id, $_SESSION['empresa_id']]);
                        
                        showAlert('Categoría actualizada exitosamente', 'success');
                        
                    } catch (Exception $e) {
                        showAlert('Error al actualizar categoría: ' . $e->getMessage(), 'danger');
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
        // Verificar si hay productos en esta categoría
        $stmt = $conn->prepare("SELECT COUNT(*) FROM productos WHERE categoria_id = ? AND activo = 1");
        $stmt->execute([$id]);
        $productos_count = $stmt->fetchColumn();
        
        if ($productos_count > 0) {
            showAlert('No se puede eliminar la categoría porque tiene productos asociados', 'danger');
        } else {
            $stmt = $conn->prepare("UPDATE categorias SET activo = 0 WHERE id = ? AND empresa_id = ?");
            $stmt->execute([$id, $_SESSION['empresa_id']]);
            showAlert('Categoría eliminada exitosamente', 'success');
        }
    } catch (Exception $e) {
        showAlert('Error al eliminar categoría: ' . $e->getMessage(), 'danger');
    }
}

// Obtener categoría para editar
$categoria_edit = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    try {
        $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ? AND empresa_id = ? AND activo = 1");
        $stmt->execute([$edit_id, $_SESSION['empresa_id']]);
        $categoria_edit = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        showAlert('Error al cargar categoría: ' . $e->getMessage(), 'danger');
    }
}

// Obtener lista de categorías
$categorias = [];
try {
    $stmt = $conn->prepare("
        SELECT c.*, COUNT(p.id) as productos_count 
        FROM categorias c 
        LEFT JOIN productos p ON c.id = p.categoria_id AND p.activo = 1
        WHERE c.empresa_id = ? AND c.activo = 1 
        GROUP BY c.id 
        ORDER BY c.nombre
    ");
    $stmt->execute([$_SESSION['empresa_id']]);
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar categorías: ' . $e->getMessage(), 'danger');
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías - <?php echo SITE_NAME; ?></title>
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
                    <a href="categories.php" class="nav-link active">Categorías</a>
                </li>
                <li class="nav-item">
                    <a href="products.php" class="nav-link">Productos</a>
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

        <!-- Formulario de edición (si se está editando) -->
        <?php if ($categoria_edit): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Editar Categoría</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?php echo $categoria_edit['id']; ?>">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="edit_nombre" class="form-label">Nombre de la Categoría *</label>
                                <input type="text" id="edit_nombre" name="nombre" class="form-control" 
                                       value="<?php echo htmlspecialchars($categoria_edit['nombre']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea id="edit_descripcion" name="descripcion" class="form-control" rows="3"><?php echo htmlspecialchars($categoria_edit['descripcion']); ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between gap-2">
                            <a href="categories.php" class="btn" style="background: #95a5a6;">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                Actualizar Categoría
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Gestión de Categorías</h1>
                <?php if (!$categoria_edit): ?>
                    <button onclick="CategoryManager.showAddForm()" class="btn btn-primary">
                        ➕ Agregar Categoría
                    </button>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($categorias)): ?>
                    <div class="alert alert-info">
                        No hay categorías registradas para esta empresa. Agregue la primera categoría para comenzar.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Productos</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($categoria['nombre']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($categoria['descripcion']); ?></td>
                                        <td>
                                            <span class="btn btn-sm btn-info">
                                                <?php echo $categoria['productos_count']; ?> productos
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($categoria['fecha_creacion']); ?></td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="categories.php?edit=<?php echo $categoria['id']; ?>" 
                                                   class="btn btn-sm btn-warning">
                                                    ✏️ Editar
                                                </a>
                                                <?php if ($categoria['productos_count'] == 0): ?>
                                                    <a href="categories.php?delete=<?php echo $categoria['id']; ?>" 
                                                       class="btn btn-sm btn-danger btn-delete">
                                                        🗑️ Eliminar
                                                    </a>
                                                <?php else: ?>
                                                    <button class="btn btn-sm" style="background: #95a5a6;" disabled title="No se puede eliminar porque tiene productos">
                                                        🗑️ Eliminar
                                                    </button>
                                                <?php endif; ?>
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

    <!-- Modal para agregar categoría -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Nueva Categoría</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addCategoryForm" onsubmit="event.preventDefault(); CategoryManager.add();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre de la Categoría *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn" style="background: #95a5a6;" onclick="MGBStock.hideModal('addCategoryModal')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Agregar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
