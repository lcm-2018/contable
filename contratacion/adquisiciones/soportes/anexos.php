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
    return '$ ' . number_format($valor, 0, ',', '.');
}
$vigencia = $_SESSION['vigencia'];
include '../../../conexion.php';
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
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
                , `ctt_adquisiciones`.`id_modalidad`
                , `ctt_modalidad`.`modalidad`
                , `ctt_adquisiciones`.`objeto`
                , `tb_terceros`.`id_tercero_api`
                , `tb_area_c`.`id_area`
                , `tb_area_c`.`area`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `ctt_modalidad` 
                ON (`ctt_adquisiciones`.`id_modalidad` = `ctt_modalidad`.`id_modalidad`)
            INNER JOIN `tb_area_c` 
                ON (`ctt_adquisiciones`.`id_area` = `tb_area_c`.`id_area`)
            LEFT JOIN `tb_terceros`
                ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE `id_adquisicion` = $id_adqi LIMIT 1";
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
                `ctt_bien_servicio`.`bien_servicio`
                , `ctt_orden_compra_detalle`.`cantidad`
                , `ctt_orden_compra_detalle`.`val_unid` AS `val_estimado_unid`
                , `ctt_orden_compra_detalle`.`id_detalle`
                , `ctt_orden_compra_detalle`.`id_servicio` AS `id_bn_sv`
            FROM
                `ctt_orden_compra_detalle`
                INNER JOIN `ctt_orden_compra` 
                    ON (`ctt_orden_compra_detalle`.`id_oc` = `ctt_orden_compra`.`id_oc`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`ctt_orden_compra_detalle`.`id_servicio` = `ctt_bien_servicio`.`id_b_s`)
            WHERE (`ctt_orden_compra`.`id_adq` = $id_adqi)";
    $rs = $cmd->query($sql);
    $oferta = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_cdp`.`id_pto_cdp` AS `id_cdp`
                , `pto_cdp`.`fecha` AS `fecha_cdp`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `pto_cdp` 
                ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE `ctt_adquisiciones`.`id_adquisicion` = '$id_adqi'";
    $rs = $cmd->query($sql);
    $data_cdp = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$tipo_bn = $compra['id_tipo_bn_sv'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_escala_honorarios`.`cod_pptal`, `pto_cargue`.`cod_pptal`, `pto_cargue`.`nom_rubro`
            FROM
                `ctt_escala_honorarios`
                INNER JOIN`pto_cargue`
                ON (`ctt_escala_honorarios`.`cod_pptal` = `pto_cargue`.`cod_pptal`)
            WHERE `ctt_escala_honorarios`.`id_tipo_b_s` = $tipo_bn AND `ctt_escala_honorarios`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $cod_cargue = $rs->fetch();
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
                , `ctt_estudios_previos`.`val_contrata`
                , `ctt_estudios_previos`.`necesidad`
                , `ctt_estudios_previos`.`act_especificas`
                , `ctt_estudios_previos`.`prod_entrega`
                , `ctt_estudios_previos`.`obligaciones`
                , `ctt_estudios_previos`.`forma_pago`
                , `ctt_estudios_previos`.`requisitos`
                , `ctt_estudios_previos`.`describe_valor`
                , `ctt_estudios_previos`.`num_ds`
                , `tb_forma_pago_compras`.`descripcion`
                , `ctt_estudios_previos`.`id_supervisor`
            FROM
                `ctt_estudios_previos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_estudios_previos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_adqi'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($compra['id_tercero_api'] > 0) {
    $key = array_search($compra['id_tercero_api'], array_column($terceros, 'id_tercero_api'));
    $tercero = ltrim($terceros[$key]['nom_tercero']);
    $cedula = $terceros[$key]['nit_tercero'];
    $dir_tercero = $terceros[$key]['direccion'] ? 'XXXXX' : $tercer[0]['direccion'];
    $tel_tercero = $terceros[$key]['telefono'] ? 'XXXXX' : $tercer[0]['telefono'];
    $id_ciudad = $terceros[$key]['municipio'];
} else {
    $tercero = 'XXXXX';
    $cedula = 'XXXXX';
    $dir_tercero = 'XXXXX';
    $tel_tercero = 'XXXXX';
    $id_ciudad = 'XXXXX';
}

$actividades = explode('||', $estudio_prev['act_especificas']);
$productos = explode('||', $estudio_prev['prod_entrega']);
$obligaciones = explode('||', $estudio_prev['obligaciones']);
$forma_pago = explode('||', $estudio_prev['forma_pago']);
$requisitos = explode('||', $estudio_prev['requisitos']);
$valores = explode('||', $estudio_prev['describe_valor']);
$actividad = [];
$actividad1 = [];
$necesidad = [];
$producto = [];
$obligacion = [];
$pago = [];
$pago_s = [];
$req_min = [];
$req_min1 = [];
$describ_val = [];
foreach ($actividades as $ac) {
    $actividad[] = ['actividad' => $ac];
}
foreach ($actividades as $ac) {
    $actividad1[] = ['actividad1' => $ac];
}
foreach ($productos as $pr) {
    $producto[] = ['producto' => $pr];
}
foreach ($obligaciones as $ob) {
    $obligacion[] = ['obligacion' => $ob];
}
foreach ($forma_pago as $fp) {
    $pago[] = ['pago' => $fp];
}
foreach ($forma_pago as $fp) {
    $pago_s[] = ['pago_s' => $fp];
}
foreach ($requisitos as $rm) {
    $req_min[] = ['req_min' => $rm];
}
foreach ($requisitos as $rm) {
    $req_min1[] = ['req_min1' => $rm];
}
foreach ($valores as $vl) {
    $describ_val[] = ['describ_val' => $vl];
}
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecI = explode('-', $estudio_prev['fec_ini_ejec']);
$fecF = explode('-', $estudio_prev['fec_fin_ejec']);
$fecha = $fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$diaI = $fecI[2] == '01' ? 'primero' : $letras->format($fecI[2]);
$diaF = $fecF[2] == '01' ? 'primero' : $letras->format($fecF[2]);
$fecI_let =  $diaI . ' (' . $fecI[2] . ')' . ' de ' . mb_strtoupper($meses[intval($fecI[1])]) . ' de ' . $fecI[0];
$fecF_let = $diaF . ' (' . $fecF[2] . ')' . ' de ' . mb_strtoupper($meses[intval($fecF[1])]) . ' de ' . $fecF[0];
$fecha2 = mb_strtoupper($fecha);
$valor = $estudio_prev['val_contrata'];
$val_num = pesos($valor);
$objeto = mb_strtoupper($compra['objeto']);
$val_letras = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));
$start = new DateTime($estudio_prev['fec_ini_ejec']);
$end = new DateTime($estudio_prev['fec_fin_ejec']);
$plazo = $start->diff($end);
$p_mes = $plazo->format('%m');
$p_dia = $plazo->format('%d');
if ($p_dia >= 29) {
    $p_mes++;
    $p_dia = 0;
}
if ($p_mes < 1) {
    $p_mes = '';
} else if ($p_mes == 1) {
    $p_mes = 'UN (01) MES';
} else {
    $p_mes = mb_strtoupper($letras->format($p_mes)) . ' (' . str_pad($p_mes, 2, '0', STR_PAD_LEFT) . ') MESES';
}
$y = ' Y ';
if ($p_dia < 1) {
    $y = '';
    $p_dia = '';
} else if ($p_dia == 1) {
    $p_dia = 'UN DÍA';
} else {
    $p_dia = mb_strtoupper($letras->format($p_dia)) . ' (' . str_pad($p_dia, 2, '0', STR_PAD_LEFT) . ') DÍAS';
}
$plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
if (intval($fecI[2]) == 1) {
    $expedicion = 'el primer (01) día del mes de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
} else {
    $expedicion = 'a los ' . $fecI[2] . ' días del mes de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
}
$rubro = !empty($cod_cargue) ? $cod_cargue['nom_rubro'] : 'XXX';
$cod_presupuesto = !empty($cod_cargue) ? $cod_cargue['id_pto_cargue'] : 'XXX';
$cpd = !empty($data_cdp) ? $data_cdp['id_cdp'] : 'XXX';
$fec_cdp = !empty($data_cdp) ? $data_cdp['fecha_cdp'] : 'XXX';
$servicio = mb_strtoupper($oferta[0]['bien_servicio']);
/*
$key = array_search($id_ter_sup, array_column($terceros, 'id_tercero_api'));
$supervisor = $terceros[$key]['nom_tercero'];
$supervisor = $id_ter_sup == '' ? 'XXXXX' : $supervisor;*/
$solicitante = $compra['area']; //area solicitante
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_departamentos`.`nom_departamento`, `tb_municipios`.`nom_municipio`
            FROM
                `tb_municipios`
                INNER JOIN `tb_departamentos` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)
            WHERE `tb_municipios`.`id_municipio` = '$id_ciudad'";
    $rs = $cmd->query($sql);
    $reside = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$mun_tercero = ucfirst(strtolower($reside['nom_municipio']));
$dpto_tercero = ucfirst(strtolower($reside['nom_departamento']));
$numds = str_pad($estudio_prev['num_ds'], 3, "0", STR_PAD_LEFT) . '-' . $_SESSION['vigencia'];
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$docx = "plantilla_anexos.docx";

$plantilla = new TemplateProcessor($docx);
/*
if ($compra['id_area'] == '5') {
    $docx = "plantilla_anexos_salud.docx";
} else {
}

if ($compra['id_area'] == '5') {
    $plantilla->cloneRowAndSetValues('req_min', $req_min);
    $plantilla->cloneRowAndSetValues('req_min1', $req_min1);
    $plantilla->cloneRowAndSetValues('describ_val', $describ_val);
}*/
$plantilla->setValue('fecha', $fecha);
$plantilla->setValue('servicio', $servicio);
$plantilla->setValue('inicia', $fecI_let);
$plantilla->setValue('termina', $fecF_let);
$plantilla->setValue('usuario', $usuario);
if (false) {
    $plantilla->setValue('val_num', $val_num);
    $plantilla->setValue('objeto', $objeto);
    $plantilla->setValue('expedicion', $expedicion);
    $plantilla->setValue('val_letras', $val_letras);
    $plantilla->cloneRowAndSetValues('actividad', $actividad);
    $markerToCheck = 'actividad1';
    $placeholders = $plantilla->getVariables();
    $marker_exists = in_array($markerToCheck, $placeholders);
    if ($marker_exists) {
        $plantilla->cloneRowAndSetValues('actividad1', $actividad1);
    }
    $plantilla->cloneRowAndSetValues('producto', $producto);
    $plantilla->cloneRowAndSetValues('obligacion', $obligacion);
    $plantilla->cloneRowAndSetValues('pago', $pago);
    $plantilla->cloneRowAndSetValues('pago_s', $pago_s);
    $plantilla->setValue('plazo', $plazo);
    $plantilla->setValue('rubro', $rubro);
    $plantilla->setValue('cod_presupuesto', $cod_presupuesto);
    $plantilla->setValue('vigencia', $vigencia);
    $plantilla->setValue('cpd', $cpd);
    $plantilla->setValue('fec_cdp', $fec_cdp);
    $plantilla->setValue('tercero', $tercero);
    $plantilla->setValue('cedula', number_format($cedula, 0, '', '.'));
    $plantilla->setValue('supervisor', $supervisor);
    $plantilla->setValue('solicitante', $solicitante);
    $plantilla->setValue('dir_tercero', $dir_tercero);
    $plantilla->setValue('tel_tercero', $tel_tercero);
    $plantilla->setValue('mun_tercero', $mun_tercero);
    $plantilla->setValue('dpto_tercero', $dpto_tercero);
    $plantilla->setValue('numds', $numds);
}

$plantilla->saveAs('anexos.docx');
header("Content-Disposition: attachment; Filename=anexos.docx");
echo file_get_contents('anexos.docx');
unlink('anexos.docx');
