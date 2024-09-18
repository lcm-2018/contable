<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_compra = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');
function pesos($valor)
{
    return '$ ' . number_format($valor, 0, ',', '.');
}
include '../../../conexion.php';
include '../../../terceros.php';
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_orden` FROM `ctt_adquisiciones` WHERE `id_adquisicion` = $id_compra LIMIT 1";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch();
    if ($adquisicion['id_orden'] == '') {
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
            WHERE (`ctt_orden_compra`.`id_adq` = $id_compra)";
    } else {
        $sql = "SELECT
                `far_alm_pedido_detalle`.`id_ped_detalle` AS `id_detalle`
                , `far_medicamentos`.`nom_medicamento` AS `bien_servicio`
                , `far_alm_pedido_detalle`.`cantidad`
                , `far_alm_pedido_detalle`.`valor` AS `val_unid`
                , `far_alm_pedido_detalle`.`aprobado` AS `val_estimado_unid`
                , `far_alm_pedido_detalle`.`id_medicamento` AS `id_bn_sv`
            FROM
                `far_alm_pedido_detalle`
                INNER JOIN `far_medicamentos` 
                    ON (`far_alm_pedido_detalle`.`id_medicamento` = `far_medicamentos`.`id_med`)
            WHERE (`far_alm_pedido_detalle`.`id_pedido` = {$adquisicion['id_orden']})";
    }
    $rs = $cmd->query($sql);
    $oferta = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$cod = [];
if (!empty($oferta)) {
    foreach ($oferta as $o) {
        $cod[] = $o['id_bn_sv'];
    }
    $cod = implode(',', $cod);
} else {
    $cod = '0';
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_clasificacion_bn_sv`.`id_b_s`
                , `tb_codificacion_unspsc`.`codigo`
                , `tb_codificacion_unspsc`.`descripcion`
            FROM
                `ctt_clasificacion_bn_sv`
                LEFT JOIN  `tb_codificacion_unspsc`
                ON (`ctt_clasificacion_bn_sv`.`cod_unspsc` = `tb_codificacion_unspsc`.`codigo`)
            WHERE `ctt_clasificacion_bn_sv`.`id_b_s` IN($cod)";
    $rs = $cmd->query($sql);
    $codigo_servicio = $rs->fetchAll();
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
                , `ctt_adquisiciones`.`obligaciones`
                , `ctt_adquisiciones`.`objeto`
                , `tb_area_c`.`id_area`
                , `tb_area_c`.`area`
            FROM
                `ctt_adquisiciones`
            INNER JOIN `ctt_modalidad` 
                ON (`ctt_adquisiciones`.`id_modalidad` = `ctt_modalidad`.`id_modalidad`)
            INNER JOIN `tb_area_c` 
                ON (`ctt_adquisiciones`.`id_area` = `tb_area_c`.`id_area`)
            WHERE `id_adquisicion` = '$id_compra' LIMIT 1";
    $rs = $cmd->query($sql);
    $compra = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_bn = $compra['id_tipo_bn_sv'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_escala_honorarios`.`cod_pptal`  AS `id_pto_cargue`
                , `pto_cargue`.`cod_pptal`, `pto_cargue`.`nom_rubro`
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
                , `ctt_estudios_previos`.`garantia`
                , `ctt_estudios_previos`.`describe_valor`
                , `tb_forma_pago_compras`.`descripcion`
                , `ctt_estudios_previos`.`id_supervisor`
            FROM
                `ctt_estudios_previos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_estudios_previos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            WHERE `id_compra` = '$id_compra'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter_sup = $estudio_prev['id_supervisor'];
$terceros = getTerceros($id_ter_sup, $cmd);
$cmd = null;
$est_prev = $estudio_prev['id_est_prev'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_garantias_compra`.`id_est_prev`
                ,`seg_garantias_compra`.`id_poliza`
                , `tb_polizas`.`descripcion`
                , `tb_polizas`.`porcentaje`
            FROM
                `seg_garantias_compra`
            INNER JOIN `tb_polizas` 
                ON (`seg_garantias_compra`.`id_poliza` = `tb_polizas`.`id_poliza`)
            WHERE `seg_garantias_compra`.`id_est_prev` = '$est_prev'";
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

$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecI = explode('-', $estudio_prev['fec_ini_ejec']);
$fecF = explode('-', $estudio_prev['fec_fin_ejec']);
$fecha = mb_strtoupper($fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0]);
$valor = $estudio_prev['val_contrata'];
$val_num = pesos($valor);
$objeto = mb_strtoupper($compra['objeto']);
$supervisor = $supervisor_res[0]['nom_tercero'];
$supervisor = $id_ter_sup == '' ? 'PENDIENTE' : $supervisor;
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
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
$proyecto = mb_strtoupper($compra['area']);
$necesidades = explode('||', $estudio_prev['necesidad']);
$actividades = explode('||', $estudio_prev['act_especificas']);
$productos = explode('||', $estudio_prev['prod_entrega']);
$obligaciones = explode('||', $estudio_prev['obligaciones']);
$forma_pago = explode('||', $estudio_prev['forma_pago']);
$requisitos = explode('||', $estudio_prev['requisitos']);
$garantias = explode('||', $estudio_prev['garantia']);
$valores = explode('||', $estudio_prev['describe_valor']);
$actividad = [];
$necesidad = [];
$producto = [];
$obligacion = [];
$pago = [];
$req_min = [];
$garantia = [];
$describ_val = [];
foreach ($necesidades as $n) {
    $necesidad[] = ['necesidad' => $n];
}
foreach ($actividades as $ac) {
    $actividad[] = ['actividad' => $ac];
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
foreach ($requisitos as $rm) {
    $req_min[] = ['req_min' => $rm];
}
foreach ($garantias as $ga) {
    $garantia[] = ['garantia' => $ga];
}
foreach ($valores as $va) {
    $describ_val[] = ['describ_val' => $va];
}

$segmento = !empty($codigo_servicio) ? substr($codigo_servicio['codigo'], 0, 2) : 'XXX';
$familia = !empty($codigo_servicio) ? substr($codigo_servicio['codigo'], 0, 4) : 'XXX';
$clase = !empty($codigo_servicio) ? substr($codigo_servicio['codigo'], 0, 6) : 'XXX';
if (!empty($cod_cargue)) {
    $rubro = $cod_cargue['id_pto_cargue'] . '-' . $cod_cargue['nom_rubro'];
} else {
    $rubro = 'XXX';
    $cod_cargue['id_pto_cargue'] = 'XXX';
    $cod_cargue['nom_rubro'] = 'XXX';
}
$plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
$listServ = [];
if (!empty($oferta)) {
    foreach ($oferta as $o) {
        $key = array_search($o['id_bn_sv'], array_column($codigo_servicio, 'id_b_s'));
        $cdg = $key !== false ? $codigo_servicio[$key]['codigo'] : 'XXX';
        $listServ[] = [
            'unspsc' => 'XXX',
            'nombre' => $o['bien_servicio'],
            'cantidad' => $o['cantidad'],
            'val_unid' => pesos($o['val_estimado_unid'])
        ];
    }
} else {
    $listServ[] = [
        'unspsc' => 'XXX',
        'nombre' => 'XXX',
        'cantidad' => 'XXX',
        'val_unid' => 'XXX'
    ];
}
if ($compra['id_area'] == '5') {
    $docx = 'plantilla_est_prev_salud.docx';
} else {
    $docx = 'plantilla_est_prev.docx';
}
$plantilla = new TemplateProcessor($docx);
if ($compra['id_area'] == '5') {
    $plantilla->cloneRowAndSetValues('req_min', $req_min);
    $plantilla->cloneRowAndSetValues('garantia', $garantia);
    $plantilla->cloneRowAndSetValues('describ_val', $describ_val);
}
$plantilla->setValue('proyecto', $proyecto);
$plantilla->setValue('seg', $segmento);
$plantilla->setValue('flia', $familia);
$plantilla->setValue('clas', $clase);
$plantilla->cloneBlock('necesidades', 0, true, false, $necesidad);
$plantilla->cloneRowAndSetValues('actividad', $actividad);
$plantilla->cloneRowAndSetValues('producto', $producto);
$plantilla->cloneRowAndSetValues('obligacion', $obligacion);
$plantilla->cloneRowAndSetValues('unspsc', $listServ);
$plantilla->cloneBlock('forma_pago', 0, true, false, $pago);
$plantilla->setValue('rubro', $rubro);
$plantilla->setValue('nombre_rubro', $cod_cargue['nom_rubro']);
$plantilla->setValue('cod_rubro', $cod_cargue['id_pto_cargue']);
$plantilla->setValue('fecha', $fecha);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('objeto', $objeto);
$plantilla->setValue('supervisor', $supervisor);
$plantilla->setValue('val_letras', $val_letras);
$plantilla->setValue('plazo', $plazo);

$plantilla->saveAs('estudios_previos.docx');
header("Content-Disposition: attachment; Filename=estudios_previos.docx");
echo file_get_contents('estudios_previos.docx');
unlink('estudios_previos.docx');
