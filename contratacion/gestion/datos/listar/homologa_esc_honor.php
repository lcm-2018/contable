<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_tipo_bien_servicio`.`id_tipo_b_s`
                , `tb_tipo_compra`.`tipo_compra`
                , `tb_tipo_contratacion`.`tipo_contrato`
                , `tb_tipo_bien_servicio`.`tipo_bn_sv`
                , `ctt_escala_honorarios`.`cod_pptal`
                , `ctt_escala_honorarios`.`val_honorarios`
                , `ctt_escala_honorarios`.`val_hora`
                , `ctt_escala_honorarios`.`vigencia`
            FROM
                `tb_tipo_bien_servicio`
            INNER JOIN `tb_tipo_contratacion` 
                ON (`tb_tipo_bien_servicio`.`id_tipo_cotrato` = `tb_tipo_contratacion`.`id_tipo`)
            INNER JOIN `tb_tipo_compra` 
                ON (`tb_tipo_contratacion`.`id_tipo_compra` = `tb_tipo_compra`.`id_tipo`)
            LEFT JOIN `ctt_escala_honorarios`
                ON(`tb_tipo_bien_servicio`.`id_tipo_b_s` = `ctt_escala_honorarios`.`id_tipo_b_s`)
            ORDER BY `tb_tipo_compra`.`tipo_compra`,`tb_tipo_contratacion`.`tipo_contrato`, `tb_tipo_bien_servicio`.`tipo_bn_sv` ASC";
    $rs = $cmd->query($sql);
    $tipo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
// Define the output file name and headers for CSV
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename=homologacion_escala_honorarios.csv');

// Open output stream
$output = fopen('php://output', 'w');

// Output column headers
fputcsv($output, ['id_tipo_servicio', 'tipo_compra', 'tipo_contrato', 'tipo_servicio', 'codigo_presupuestal', 'Vigencia'], ';');

foreach ($tipo as $fila) {
    fputcsv($output, [
        $fila['id_tipo_b_s'],
        mb_convert_encoding($fila['tipo_compra'], 'ISO-8859-1', 'UTF-8'),
        mb_convert_encoding($fila['tipo_contrato'], 'ISO-8859-1', 'UTF-8'),
        mb_convert_encoding($fila['tipo_bn_sv'], 'ISO-8859-1', 'UTF-8'),
        $fila['cod_pptal'],
        $fila['vigencia']
    ], ';');
}
fclose($output);
exit();
