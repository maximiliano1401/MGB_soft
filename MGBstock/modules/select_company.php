<?php
require_once '../includes/config.php';

// Verificar que esté logueado
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Verificar que se haya pasado un ID de empresa
if (!isset($_GET['id']) || empty($_GET['id'])) {
    showAlert('ID de empresa no válido', 'danger');
    redirect('companies.php');
}

$empresa_id = (int)$_GET['id'];

// Verificar que la empresa existe y está activa
$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->prepare("SELECT * FROM empresas WHERE id = ? AND activo = 1");
    $stmt->execute([$empresa_id]);
    
    if ($empresa = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Establecer la empresa en la sesión
        $_SESSION['empresa_id'] = $empresa['id'];
        $_SESSION['empresa_nombre'] = $empresa['nombre'];
        
        showAlert('Ahora está trabajando con: ' . $empresa['nombre'], 'success');
        redirect('../dashboard.php');
    } else {
        showAlert('Empresa no encontrada o inactiva', 'danger');
        redirect('companies.php');
    }
    
} catch (Exception $e) {
    showAlert('Error al seleccionar empresa: ' . $e->getMessage(), 'danger');
    redirect('companies.php');
}
?>
