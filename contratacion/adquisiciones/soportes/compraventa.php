<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');

include '../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_datos_ips`.`nit`
                , `tb_datos_ips`.`dig_ver`
                , `tb_datos_ips`.`nombre`
                , `tb_municipios`.`nom_municipio`
            FROM
            `tb_datos_ips`
            INNER JOIN `tb_municipios` 
                ON (`tb_datos_ips`.`id_ciudad` = `tb_municipios`.`id_municipio`) LIMIT 1";
    $rs = $cmd->query($sql);
    $compania = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
                `ctt_contratos`.`id_contrato_compra`
                , `ctt_contratos`.`id_compra`
                , `ctt_contratos`.`fec_ini`
                , `ctt_contratos`.`fec_fin`
                , `tb_forma_pago_compras`.`descripcion`
                , `ctt_contratos`.`id_supervisor`
            FROM
                `ctt_contratos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_contratos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_compra'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter_sup = $contrato['id_supervisor'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `no_doc` FROM `seg_terceros` WHERE `id_tercero_api` = '$id_ter_sup'";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetch();
    //API URL
    $url = $api . 'terceros/datos/res/lista/' . $terceros_sup['no_doc'];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor_res = json_decode($result, true);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$contra = $contrato['id_contrato_compra'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_garantias_compra`.`id_contrato_compra`
                ,`ctt_garantias_compra`.`id_poliza`
                , `tb_polizas`.`descripcion`
                , `tb_polizas`.`porcentaje`
            FROM
                `ctt_garantias_compra`
            INNER JOIN `tb_polizas` 
                ON (`ctt_garantias_compra`.`id_poliza` = `tb_polizas`.`id_poliza`)
            WHERE `ctt_garantias_compra`.`id_contrato_compra` = '$contra'";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$polizas = '';
$num = 1;
foreach ($garantias as $g) {
    $polizas .=  $num . '. ' . ucfirst(strtolower($g['descripcion']) . ' por el ' . $g['porcentaje'] . '%. ');
    $num++;
}
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$empresa = $compania['nombre'];
$municipio = $compania['nom_municipio'];
$objeto = $compra['objeto'];
$modalidad_contratacion = $compra['modalidad'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fec_ini = explode('-', $contrato['fec_ini']);
$fec_fin = explode('-', $contrato['fec_fin']);
$anio_ini = $fec_ini[0];
$mes_ini = intval($fec_ini[1]);
$dia_ini = $fec_ini[2];
$anio_fin = $fec_fin[0];
$mes_fin = intval($fec_fin[1]);
$dia_fin = $fec_fin[2];
$val_letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$dia_ini_l = $val_letras->format($dia_ini, 2);
$dia_fin_l = $val_letras->format($dia_fin, 2);
$mes_ini_l = $meses[$mes_ini];
$mes_fin_l = $meses[$mes_fin];
$forma_pago = $contrato['descripcion'];
$supervisor = $supervisor_res[0]['apellido1'] . ' ' . $supervisor_res[0]['apellido2'] . ' ' . $supervisor_res[0]['nombre1'] . ' ' . $supervisor_res[0]['nombre2'];
$fec_inicio = $dia_ini_l . ' (' . $dia_ini . ') de ' . $mes_ini_l . ' de ' . $anio_ini;
$fec_final = $dia_fin_l . ' (' . $dia_fin . ') de ' . $mes_fin_l . ' de ' . $anio_fin;
//echo $fec_ini_contrato . ' hasta ' . $fec_fin_contrato; 
$plantilla = new TemplateProcessor('plantilla_compraventa.docx');

$plantilla->setValue('id_contrato', $contra);
$plantilla->setValue('fec_inicio', $fec_inicio);
$plantilla->setValue('fec_final', $fec_final);

$values = [
    ['id' => 1, 'descripcion' => 'Batman', 'cantidad' => 3, 'val_total' => '$250'],
    ['id' => 2, 'descripcion' => 'Superman', 'cantidad' => 4, 'val_total' => '$150'],
];
$plantilla->cloneRowAndSetValues('id', $values);


$plantilla->saveAs('plantilla_contrato_compraventa.docx');
header("Content-Disposition: attachment; Filename=contrato_compraventa.docx");
echo file_get_contents('plantilla_contrato_compraventa.docx');
unlink('plantilla_contrato_compraventa.docx');
