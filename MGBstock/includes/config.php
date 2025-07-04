<?php
/**
 * Configuración de Base de Datos - MGBStock
 * Archivo de configuración para la conexión a MySQL
 */

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'mgbstock');
define('DB_USER', 'root');
define('DB_PASS', ''); // Cambiar según tu configuración de XAMPP

// Configuración de la aplicación
define('SITE_URL', 'http://localhost/MGB_soft/MGBstock/');
define('SITE_NAME', 'MGBStock - Sistema de Inventario');

// Configuración de sesiones
ini_set('session.cookie_lifetime', 3600); // 1 hora
session_start();

/**
 * Clase para manejo de conexión a base de datos
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn = null;

    /**
     * Obtener conexión a la base de datos
     */
    public function getConnection() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8",
                $this->username,
                $this->password
            );
            
            // Establecer el modo de error de PDO a excepción
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }

    /**
     * Cerrar conexión
     */
    public function closeConnection() {
        $this->conn = null;
    }
}

/**
 * Funciones auxiliares
 */

/**
 * Sanitizar entrada de datos
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Verificar si el usuario está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Verificar si hay una empresa seleccionada
 */
function hasSelectedCompany() {
    return isset($_SESSION['empresa_id']) && !empty($_SESSION['empresa_id']);
}

/**
 * Redireccionar
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Mostrar mensaje de alerta
 */
function showAlert($message, $type = 'info') {
    $_SESSION['alert_message'] = $message;
    $_SESSION['alert_type'] = $type;
}

/**
 * Obtener y limpiar mensaje de alerta
 */
function getAlert() {
    if (isset($_SESSION['alert_message'])) {
        $alert = [
            'message' => $_SESSION['alert_message'],
            'type' => $_SESSION['alert_type'] ?? 'info'
        ];
        unset($_SESSION['alert_message']);
        unset($_SESSION['alert_type']);
        return $alert;
    }
    return null;
}

/**
 * Formatear precio
 */
function formatPrice($price) {
    return 'S/ ' . number_format($price, 2);
}

/**
 * Formatear fecha
 */
function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

/**
 * Generar código único para productos
 */
function generateProductCode($prefix = 'PROD') {
    return $prefix . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
}
?>
