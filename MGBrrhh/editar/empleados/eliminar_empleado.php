<?php
// filepath: c:\xampp\htdocs\xampp\angel\recursos humanos\editar\empleados\eliminar_empleado.php
require_once '../../conexion.php';

$id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
}

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM empleados WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../../Ver_Registrados/ver_Registro_Empleados.php");
exit;