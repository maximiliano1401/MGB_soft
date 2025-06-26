<?php
require_once 'auth.php';
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $empleado_id = intval($_POST['empleado_id'] ?? 0);
    $tipo = $_POST['tipo'] ?? '';
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    if (!$empleado_id || $tipo === '' || $fecha_inicio === '' || $fecha_fin === '') {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?error=Datos incompletos");
        exit();
    }

    if ($fecha_inicio > $fecha_fin) {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?error=Fechas inválidas");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO ausencias (empleado_id, tipo, fecha_inicio, fecha_fin, motivo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $empleado_id, $tipo, $fecha_inicio, $fecha_fin, $motivo);
    
    if ($stmt->execute()) {
        $tipo_msg = ucfirst($tipo);
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?success={$tipo_msg} registrada correctamente");
    } else {
        header("Location: Ver_Registrados/ver_Registro_Empleados.php?error=Error al registrar la ausencia");
    }
    $stmt->close();
} else {
    header("Location: Ver_Registrados/ver_Registro_Empleados.php");
}
exit();
?>
