<?php
require_once 'auth.php';
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $empleado_id = (int)$_POST['empleado_id'];
    $motivo = trim($_POST['motivo'] ?? 'Reactivación');
    $fecha_alta = date('Y-m-d');

    // Verificar que el empleado existe y está inactivo
    $check = $conn->prepare("SELECT e.id FROM empleados e LEFT JOIN (
        SELECT empleado_id, estado, ROW_NUMBER() OVER (PARTITION BY empleado_id ORDER BY fecha_movimiento DESC) as rn
        FROM altas_bajas
    ) ab ON e.id = ab.empleado_id AND ab.rn = 1 WHERE e.id = ? AND (ab.estado = 'inactivo' OR ab.estado IS NULL)");
    $check->bind_param("i", $empleado_id);
    $check->execute();
    $result_check = $check->get_result();

    if ($result_check->num_rows > 0) {
        // Insertar el registro de alta
        $stmt = $conn->prepare("INSERT INTO altas_bajas (empleado_id, tipo, estado, fecha_movimiento, motivo) VALUES (?, 'alta', 'activo', ?, ?)");
        $stmt->bind_param("iss", $empleado_id, $fecha_alta, $motivo);
        if ($stmt->execute()) {
            $_SESSION['mensaje'] = "Empleado reactivado exitosamente";
            $_SESSION['tipo_mensaje'] = "success";
        } else {
            $_SESSION['mensaje'] = "Error al reactivar al empleado: " . $conn->error;
            $_SESSION['tipo_mensaje'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['mensaje'] = "Empleado no encontrado o ya está activo";
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
