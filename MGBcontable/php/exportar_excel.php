<?php
require __DIR__ . '/../../vendor/autoload.php';
include '../php/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Recibir datos por POST
$empresa_id = isset($_POST['empresa']) ? intval($_POST['empresa']) : 0;
$anio = isset($_POST['anio']) ? intval($_POST['anio']) : 0;

if ($empresa_id > 0 && $anio > 0) {
    $datos = [];
    $total_ingresos = 0;
    $total_gastos = 0;

    // Obtener balances
    $stmt = $conn->prepare("
        SELECT bi.id
        FROM balance_inicial bi
        WHERE bi.empresa = ? AND YEAR(bi.fecha) = ?
    ");
    $stmt->bind_param("ii", $empresa_id, $anio);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $stmt->close();

    if (!empty($ids)) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));

        $query = "
            SELECT c.codigo, c.nombre, c.tipo, SUM(bid.saldo) AS saldo
            FROM balance_inicial_detalle bid
            JOIN cuentas c ON c.codigo = bid.cuenta_codigo
            WHERE bid.balance_id IN ($placeholders)
            GROUP BY c.codigo, c.nombre, c.tipo
            ORDER BY c.codigo
        ";

        $stmt = $conn->prepare($query);

        $params = [];
        $params[] = & $types;
        foreach ($ids as $key => $id) {
            $params[] = & $ids[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $params);

        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $saldo = floatval($row['saldo']);
            $tipo = $row['tipo'];

            if ($tipo === 'Patrimonio') {
                $total_ingresos += $saldo;
                $datos[] = ['tipo' => 'Ingreso', 'cuenta' => $row['nombre'], 'monto' => $saldo];
            } elseif ($tipo === 'Activo') {
                $total_gastos += $saldo;
                $datos[] = ['tipo' => 'Gasto', 'cuenta' => $row['nombre'], 'monto' => $saldo];
            }
        }
        $stmt->close();
    }

    if (!empty($datos)) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Estado de Resultados');

        // Encabezados
        $sheet->setCellValue('A1', 'Tipo');
        $sheet->setCellValue('B1', 'Cuenta');
        $sheet->setCellValue('C1', 'Monto');

        // Datos
        $rowNum = 2;
        foreach ($datos as $fila) {
            $sheet->setCellValue("A{$rowNum}", $fila['tipo']);
            $sheet->setCellValue("B{$rowNum}", $fila['cuenta']);
            $sheet->setCellValue("C{$rowNum}", $fila['monto']);
            $rowNum++;
        }

        // Totales
        $sheet->setCellValue("B{$rowNum}", 'Total Ingresos:');
        $sheet->setCellValue("C{$rowNum}", $total_ingresos);
        $rowNum++;
        $sheet->setCellValue("B{$rowNum}", 'Total Gastos:');
        $sheet->setCellValue("C{$rowNum}", $total_gastos);
        $rowNum++;
        $sheet->setCellValue("B{$rowNum}", 'Utilidad Neta:');
        $sheet->setCellValue("C{$rowNum}", $total_ingresos - $total_gastos);

        // -- Estilos --

        // Fuente general
        $sheet->getStyle('A1:C' . $rowNum)->getFont()->setName('Calibri')->setSize(12);

        // Encabezados: negrita, fondo gris claro, bordes
        $headerStyle = $sheet->getStyle('A1:C1');
        $headerStyle->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF4F81BD'); // azul ejecutivo
        $headerStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF000000'));

        // Bordes para todo el rango de datos y totales
        $dataRange = "A1:C{$rowNum}";
        $sheet->getStyle($dataRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF000000'));

        // Alineación: texto izquierda para A y B, números derecha para C
        $sheet->getStyle("A2:A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("B2:B{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle("C2:C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Formato de número con separador miles y 2 decimales para columna C
        $sheet->getStyle("C2:C{$rowNum}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2);

        // Ajustar ancho columnas
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Resaltar fila de Utilidad Neta (última fila)
        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getFont()->setBold(true)->getColor()->setARGB('FF006100'); // Verde oscuro
        $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC6EFCE'); // Verde claro fondo

        // Enviar headers y archivo
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="estado_resultados.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    } else {
        echo "No se encontraron datos para exportar.";
    }
} else {
    echo "Parámetros inválidos o faltantes.";
}
