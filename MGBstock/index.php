<?php
require_once 'includes/config.php';

// Si ya está logueado, redirigir al dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Procesar login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = sanitize_input($_POST['usuario']);
    $password = $_POST['password'];
    
    if (empty($usuario) || empty($password)) {
        showAlert('Por favor ingrese usuario y contraseña', 'danger');
    } else {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            
            $stmt = $conn->prepare("SELECT id, usuario, nombre, email FROM administradores WHERE usuario = ? AND password = MD5(?) AND activo = 1");
            $stmt->execute([$usuario, $password]);
            
            if ($admin = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_usuario'] = $admin['usuario'];
                $_SESSION['admin_nombre'] = $admin['nombre'];
                $_SESSION['admin_email'] = $admin['email'];
                
                showAlert('Bienvenido ' . $admin['nombre'], 'success');
                redirect('dashboard.php');
            } else {
                showAlert('Usuario o contraseña incorrectos', 'danger');
            }
            
        } catch (Exception $e) {
            showAlert('Error en el sistema: ' . $e->getMessage(), 'danger');
        }
    }
}

$alert = getAlert();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h1>MGBStock</h1>
                    <a href="../index.html" class="back-btn">
        ← Volver al Inicio
    </a>
                <p>Sistema de Inventario</p>
            </div>
            <div class="login-body">
                <?php if ($alert): ?>
                    <div class="alert alert-<?php echo $alert['type']; ?>">
                        <?php echo $alert['message']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" id="usuario" name="usuario" class="form-control" required autofocus>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        Iniciar Sesión
                    </button>
                </form>
                
                <div style="margin-top: 2rem; text-align: center; color: #666; font-size: 0.9rem;">
                    <p><strong>Datos de acceso por defecto:</strong></p>
                    <p>Usuario: <code>admin</code></p>
                    <p>Contraseña: <code>admin123</code></p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
