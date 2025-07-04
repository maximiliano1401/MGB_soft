<?php
require_once '../includes/config.php';

// Verificar que esté logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

$db = new Database();
$conn = $db->getConnection();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $nombre = sanitize_input($_POST['nombre']);
                $direccion = sanitize_input($_POST['direccion']);
                $telefono = sanitize_input($_POST['telefono']);
                $email = sanitize_input($_POST['email']);
                $ruc = sanitize_input($_POST['ruc']);
                
                if (empty($nombre)) {
                    showAlert('El nombre de la empresa es requerido', 'danger');
                } else {
                    try {
                        $stmt = $conn->prepare("INSERT INTO empresas (nombre, direccion, telefono, email, ruc) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$nombre, $direccion, $telefono, $email, $ruc]);
                        
                        showAlert('Empresa agregada exitosamente', 'success');
                        
                        // Si es petición AJAX, devolver JSON
                        if (isset($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => true, 'message' => 'Empresa agregada exitosamente']);
                            exit;
                        }
                        
                    } catch (Exception $e) {
                        $message = 'Error al agregar empresa: ' . $e->getMessage();
                        showAlert($message, 'danger');
                        
                        if (isset($_POST['ajax'])) {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => $message]);
                            exit;
                        }
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
        $stmt = $conn->prepare("UPDATE empresas SET activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
        showAlert('Empresa eliminada exitosamente', 'success');
    } catch (Exception $e) {
        showAlert('Error al eliminar empresa: ' . $e->getMessage(), 'danger');
    }
}

// Obtener lista de empresas
$empresas = [];
try {
    $stmt = $conn->query("SELECT * FROM empresas WHERE activo = 1 ORDER BY nombre");
    $empresas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    showAlert('Error al cargar empresas: ' . $e->getMessage(), 'danger');
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empresas - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">MGBStock</div>
            <div class="user-info">
                <span>Bienvenido, <?php echo $_SESSION['admin_nombre']; ?></span>
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
                    <a href="companies.php" class="nav-link active">Empresas</a>
                </li>
                <?php if (hasSelectedCompany()): ?>
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
                        <a href="purchases.php" class="nav-link">Compras</a>
                    </li>
                    <li class="nav-item">
                        <a href="statistics.php" class="nav-link">Estadísticas</a>
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
                <h1 class="card-title">Gestión de Empresas</h1>
                <button onclick="CompanyManager.showAddForm()" class="btn btn-primary">
                    ➕ Agregar Empresa
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($empresas)): ?>
                    <div class="alert alert-info">
                        No hay empresas registradas. Agregue la primera empresa para comenzar.
                    </div>
                <?php else: ?>
                    <div class="company-grid">
                        <?php foreach ($empresas as $empresa): ?>
                            <div class="company-card" onclick="CompanyManager.select(<?php echo $empresa['id']; ?>)">
                                <div class="company-name"><?php echo htmlspecialchars($empresa['nombre']); ?></div>
                                <div class="company-info">
                                    <?php if ($empresa['direccion']): ?>
                                        <p><strong>Dirección:</strong> <?php echo htmlspecialchars($empresa['direccion']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($empresa['telefono']): ?>
                                        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($empresa['telefono']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($empresa['email']): ?>
                                        <p><strong>Email:</strong> <?php echo htmlspecialchars($empresa['email']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($empresa['ruc']): ?>
                                        <p><strong>RUC:</strong> <?php echo htmlspecialchars($empresa['ruc']); ?></p>
                                    <?php endif; ?>
                                    <p><strong>Registrada:</strong> <?php echo formatDate($empresa['fecha_registro']); ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); CompanyManager.select(<?php echo $empresa['id']; ?>)">
                                        Seleccionar
                                    </button>
                                    <button class="btn btn-danger btn-sm btn-delete" onclick="event.stopPropagation(); window.location.href='companies.php?delete=<?php echo $empresa['id']; ?>'">
                                        Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Modal para agregar empresa -->
    <div id="addCompanyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Agregar Nueva Empresa</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="addCompanyForm" onsubmit="event.preventDefault(); CompanyManager.add();">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="ajax" value="1">
                    
                    <div class="form-group">
                        <label for="nombre" class="form-label">Nombre de la Empresa *</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea id="direccion" name="direccion" class="form-control" rows="3"></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" id="telefono" name="telefono" class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="ruc" class="form-label">RUC</label>
                            <input type="text" id="ruc" name="ruc" class="form-control">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control">
                    </div>
                    
                    <div class="d-flex justify-content-between gap-2">
                        <button type="button" class="btn" style="background: #95a5a6;" onclick="MGBStock.hideModal('addCompanyModal')">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Agregar Empresa
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
