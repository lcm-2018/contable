<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `variable`, `tipo`, `contexto`, `ejemplo`
            FROM `ctt_variables_forms`";
    $rs = $cmd->query($sql);
    $variables = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
// Define the output file name and headers for CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=variables_contratacion.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, ['Variable', 'Tipo', 'Descripcion', 'ejemplo'], ';');

// Output rows
foreach ($variables as $fila) {
    fputcsv($output, [
        $fila['variable'],
        $fila['tipo'] == '1' ? 'Texto' : 'Fila',
        mb_convert_encoding($fila['contexto'], 'ISO-8859-1', 'UTF-8'),
        mb_convert_encoding($fila['ejemplo'], 'ISO-8859-1', 'UTF-8'),
    ], ';');
}

// Close the output stream
fclose($output);
exit();
