<?php
require_once 'auth.php';
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id']) && isset($_GET['motivo'])) {
    $empleado_id = intval($_GET['id']);
    $motivo = trim($_GET['motivo']);
    $fecha_baja = date('Y-m-d');

    if (!$empleado_id || !$motivo) {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?error=Datos incompletos para la baja");
        exit();
    }

    // Buscar registro activo y actualizarlo
    $stmt = $conn->prepare("UPDATE altas_bajas SET fecha_baja = ?, causa_baja = ?, estado = 'inactivo' WHERE empleado_id = ? AND estado = 'activo'");
    $stmt->bind_param("ssi", $fecha_baja, $motivo, $empleado_id);
    
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?success=Empleado dado de baja correctamente");
    } else {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?error=Error al dar de baja o empleado ya inactivo");
    }
    $stmt->close();
} else {
    header("Location: Ver_Registrados/ver_Registro_Empleados.php");
}
exit();
?>
