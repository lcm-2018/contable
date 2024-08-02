<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_bien_servicio`.`id_b_s`, `tb_tipo_bien_servicio`.`tipo_bn_sv`, `ctt_bien_servicio`.`bien_servicio`,
                `ctt_clasificacion_bn_sv`.`cod_unspsc`, `ctt_clasificacion_bn_sv`.`cod_cuipo`, `ctt_clasificacion_bn_sv`.`cod_siho`
            FROM
                `ctt_bien_servicio`
                INNER JOIN `tb_tipo_bien_servicio` 
                    ON (`ctt_bien_servicio`.`id_tipo_bn_sv` = `tb_tipo_bien_servicio`.`id_tipo_b_s`)
                LEFT JOIN `ctt_clasificacion_bn_sv`
                    ON (`ctt_clasificacion_bn_sv`.`id_b_s` = `ctt_bien_servicio`.`id_b_s`)
            ORDER BY `tb_tipo_bien_servicio`.`tipo_bn_sv`, `ctt_bien_servicio`.`bien_servicio` ASC";
    $rs = $cmd->query($sql);
    $bien_servicio = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    exit();
}

// Define the output file name and headers for CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=homologacion.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, ['id_b_s', 'tipo_bn_sv', 'bien_servicio', 'cod_unspsc', 'cod_cuipo', 'cod_siho'], ';');

// Output rows
foreach ($bien_servicio as $fila) {
    fputcsv($output, [
        $fila['id_b_s'],
        mb_convert_encoding($fila['tipo_bn_sv'], 'ISO-8859-1', 'UTF-8'),
        mb_convert_encoding($fila['bien_servicio'], 'ISO-8859-1', 'UTF-8'),
        $fila['cod_unspsc'],
        $fila['cod_cuipo'],
        $fila['cod_siho']
    ], ';');
}

// Close the output stream
fclose($output);
exit();
