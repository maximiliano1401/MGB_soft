<?php
require_once 'auth.php';
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empleado_id = (int)($_POST['empleado_id'] ?? $_GET['id'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? $_GET['motivo'] ?? '');
    $fecha_baja = date('Y-m-d');

    // Verificar que el empleado existe
    $check = $conn->prepare("SELECT id FROM empleados WHERE id = ?");
    $check->bind_param("i", $empleado_id);
    $check->execute();
    $result_check = $check->get_result();

    if ($result_check->num_rows > 0) {
        // Insertar el registro de baja
        $stmt = $conn->prepare("INSERT INTO altas_bajas (empleado_id, tipo, estado, fecha_movimiento, motivo) VALUES (?, 'baja', 'inactivo', ?, ?)");
        $stmt->bind_param("iss", $empleado_id, $fecha_baja, $motivo);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Empleado dado de baja exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al dar de baja al empleado: " . $conn->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje'] = "Empleado no encontrado";
        $_SESSION['tipo_mensaje'] = "error";
    }
    $check->close();
} else {
    $_SESSION['mensaje'] = "Acceso no válido";
    $_SESSION['tipo_mensaje'] = "error";
}

header("Location: Ver_Registrados/ver_Registro_Empleados.php");
exit();
?>
