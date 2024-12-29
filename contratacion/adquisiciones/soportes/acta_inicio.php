<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_adqi = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');
$id_user = $_SESSION['id_user'];

function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
include '../../../conexion.php';

$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre1`,`nombre2`,`apellido1`,`apellido2` FROM `seg_usuarios_sistema` WHERE `id_usuario` = {$id_user}";
    $rs = $cmd->query($sql);
    $usuario = $rs->fetch();
    $usuario = trim($usuario['nombre1'] . ' ' . $usuario['nombre2'] . ' ' . $usuario['apellido1'] . ' ' . $usuario['apellido2']);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `razon_social_ips` FROM `tb_datos_ips` LIMIT 1";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $empresa = mb_strtoupper($empresa['razon_social_ips']);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_contratos`.`fec_ini`
                , `ctt_contratos`.`fec_fin`
                , `ctt_contratos`.`val_contrato`
                , `ctt_contratos`.`num_contrato`
                , `tb_terceros`.`genero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `adq`.`cantidad`
                , `ctt_bien_servicio`.`bien_servicio`
                , `ctt_adquisiciones`.`objeto`
                , `supervisor`.`nom_tercero` AS `supervisor`
                , `supervisor`.`nit_tercero` AS `cc_super`
            FROM
                `ctt_contratos`
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `tb_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `tb_terceros`  AS `supervisor`
                    ON (`ctt_contratos`.`id_supervisor` = `supervisor`.`id_tercero_api`)
                INNER JOIN
                    (SELECT
                        `ctt_adquisiciones`.`id_adquisicion`
                        , SUM(`ctt_orden_compra_detalle`.`cantidad`) AS `cantidad`
                        , `ctt_orden_compra_detalle`.`id_servicio`
                    FROM
                        `ctt_orden_compra_detalle`
                        INNER JOIN `ctt_orden_compra` 
                            ON (`ctt_orden_compra_detalle`.`id_oc` = `ctt_orden_compra`.`id_oc`)
                        INNER JOIN `ctt_adquisiciones` 
                            ON (`ctt_orden_compra`.`id_adq` = `ctt_adquisiciones`.`id_adquisicion`)
                    GROUP BY `ctt_adquisiciones`.`id_adquisicion`)  AS `adq`
                    ON (`adq`.`id_adquisicion` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`adq`.`id_servicio` = `ctt_bien_servicio`.`id_b_s`)
            WHERE (`ctt_contratos`.`id_compra` = $id_adqi)";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$no_contrato = $contrato['num_contrato'];
$contratista = mb_strtoupper($contrato['nom_tercero']);
$nit = $contrato['nit_tercero'];
$cedula = number_format($nit, 0, '', '.');
$supervisor = mb_strtoupper($contrato['supervisor']);
$cc_super = number_format($contrato['cc_super'], 0, '', '.');
$servicio = $contrato['bien_servicio'];
$inicia = $contrato['fec_ini'];
$termina = $contrato['fec_fin'];
if ($contrato['genero'] == 'M') {
    $distincion = 'el';
    $distincionM = 'EL';
    $terminacion = 'o';
    $terminacionM = 'O';
} else {
    $distincion = 'la';
    $distincionM = 'LA';
    $terminacion = 'a';
    $terminacionM = 'A';
}

$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$vigencia = $_SESSION['vigencia'];
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$valor = $contrato['val_contrato'];
$val_num = pesos($valor);
$val_letras = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));

$start = new DateTime($contrato['fec_ini']);
$fecI = explode('-', $contrato['fec_ini']);
$diaI = $fecI[2] == '01' ? 'primero' : $letras->format($fecI[2]);
$fecI_let =  $diaI . ' (' . $fecI[2] . ')' . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
$objeto = $contrato['objeto'];

require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$docx = 'plantilla_acta_inicio.docx';

$plantilla = new TemplateProcessor($docx);
$plantilla->setValue('no_contrato', $no_contrato);
$plantilla->setValue('empresa', $empresa);
$plantilla->setValue('contratista', $contratista);
$plantilla->setValue('cedula', $cedula);
$plantilla->setValue('servicio', $servicio);
$plantilla->setValue('inicia', $inicia);
$plantilla->setValue('termina', $termina);
$plantilla->setValue('distincion', $distincion);
$plantilla->setValue('terminacion', $terminacion);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('fecI_let', $fecI_let);
$plantilla->setValue('usuario', $usuario);
$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('supervisor', $supervisor);
$plantilla->setValue('cc_super', $cc_super);
$plantilla->saveAs('acta.docx');
header("Content-Disposition: attachment; Filename=Acta-contrato-CHT-" . $no_contrato . ".docx");
echo file_get_contents('acta.docx');
unlink('acta.docx');
