<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit("Acción no permitida");

include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`id_modalidad`
                , `ctt_modalidad`.`modalidad`
                , `ctt_adquisiciones`.`objeto`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `ctt_modalidad` 
                ON (`ctt_adquisiciones`.`id_modalidad` = `ctt_modalidad`.`id_modalidad`)
            WHERE `id_adquisicion` = '$id_compra' LIMIT 1";
    $rs = $cmd->query($sql);
    $compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_estudios_previos`.`id_est_prev`
                , `ctt_estudios_previos`.`id_compra`
                , `ctt_estudios_previos`.`fec_ini_ejec`
                , `ctt_estudios_previos`.`fec_fin_ejec`
                , `tb_forma_pago_compras`.`descripcion`
                , `ctt_estudios_previos`.`id_supervisor`
            FROM
                `ctt_estudios_previos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_estudios_previos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_compra'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$objeto = mb_strtoupper($compra['objeto']);
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fec = explode('-', $estudio_prev['fec_ini_ejec']);
$anio_ini = $fec[0];
$mes_ini = intval($fec[1]);
$dia_ini = $fec[2];
$fecha = mb_strtoupper($dia_ini . ' de ' . $meses[$mes_ini] . ' de ' . $anio_ini);
$plantilla = new TemplateProcessor('plantilla_matriz_riesgos_pe.docx');

$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('fecha', $fecha);

$plantilla->saveAs('matriz_riesgos.docx');
header("Content-Disposition: attachment; Filename=matriz_riesgos.docx");
echo file_get_contents('matriz_riesgos.docx');
unlink('matriz_riesgos.docx');
