<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_adqi = isset($_POST['id']) ? $_POST['id'] : exit('Acción no pemitida');
function pesos($valor)
{
    return '$ ' . number_format($valor, 2, ',', '.');
}
include '../../../conexion.php';

$vigencia = $_SESSION['vigencia'];
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
                , `ctt_estudios_previos`.`act_especificas`
                , `ctt_estudios_previos`.`obligaciones`
                , `cdp`.`val_cdp`
                , `cdp`.`cod_pptal`
                , `cdp`.`nom_rubro`
            FROM
                `ctt_contratos`
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_contratos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN `tb_terceros` 
                    ON (`ctt_adquisiciones`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
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
                INNER JOIN `ctt_estudios_previos` 
                    ON (`ctt_estudios_previos`.`id_compra` = `ctt_adquisiciones`.`id_adquisicion`)
                INNER JOIN 
                    (SELECT
                        `ctt_adquisiciones`.`id_adquisicion`
                        , SUM(`pto_cdp_detalle`.`valor`) - SUM(`pto_cdp_detalle`.`valor_liberado`) AS `val_cdp`
                        , `pto_cargue`.`cod_pptal`
                        , `pto_cargue`.`nom_rubro`
                    FROM
                        `ctt_adquisiciones`
                        INNER JOIN `pto_cdp_detalle` 
                            ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp_detalle`.`id_pto_cdp`)
                        INNER JOIN `pto_cargue` 
                            ON (`pto_cdp_detalle`.`id_rubro` = `pto_cargue`.`id_cargue`)
                    GROUP BY `ctt_adquisiciones`.`id_adquisicion`, `pto_cdp_detalle`.`id_pto_cdp`, `pto_cdp_detalle`.`id_rubro`) AS `cdp`
                    ON (`cdp`.`id_adquisicion` = `ctt_adquisiciones`.`id_adquisicion`)
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
$servicio = $contrato['bien_servicio'];
$salario = pesos($contrato['val_contrato'] / $contrato['cantidad']);
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
$actividades = explode('||', $contrato['act_especificas']);
$obligaciones = explode('||', $contrato['obligaciones']);
$actividad = [];
$obligacion = [];
foreach ($actividades as $n) {
    if ($n != '') {
        $actividad[] = ['actividad' => $n];
    }
}
foreach ($obligaciones as $n) {
    if ($n != '') {
        $obligacion[] = ['obligacion' => $n];
    }
}
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$vigencia = $_SESSION['vigencia'];
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$valor = $contrato['val_contrato'];
$val_num = pesos($valor);
$val_letras = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));
$rubro = $contrato['nom_rubro'];
$cod_rubro = $contrato['cod_pptal'];
$val_cdp = pesos($contrato['val_cdp']);
$val_cdp_letras = str_replace('-', '', mb_strtoupper($letras->format($contrato['val_cdp'], 2)));
$start = new DateTime($contrato['fec_ini']);
$end = new DateTime($contrato['fec_fin']);
$fecI = explode('-', $contrato['fec_ini']);
$fecF = explode('-', $contrato['fec_fin']);
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

$diaI = $fecI[2] == '01' ? 'primero' : $letras->format($fecI[2]);
$diaF = $fecF[2] == '01' ? 'primero' : $letras->format($fecF[2]);
$fecI_let =  $diaI . ' (' . $fecI[2] . ')' . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
$fecF_let = $diaF . ' (' . $fecF[2] . ')' . ' de ' . $meses[intval($fecF[1])] . ' de ' . $fecF[0];

require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
/*
$servicio = $id_orden == '' ? $oferta[0]['bien_servicio'] : 'XXXXXXXXX';
$cdp = $adquisicion['id_manu'] == '' ? 'XXXXXXXXX' : $vigencia . str_pad($adquisicion['id_manu'], 6, "0", STR_PAD_LEFT);
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecI = explode('-', $estudio_prev['fec_ini_ejec']);
$fecF = explode('-', $estudio_prev['fec_fin_ejec']);
$fecha = mb_strtoupper($fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0]);
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$diaI = $fecI[2] == '01' ? 'PRIMERO' : mb_strtoupper($letras->format($fecI[2]));
$diaF = $fecF[2] == '01' ? 'PRIMERO' : mb_strtoupper($letras->format($fecF[2]));
$fecI_let =  $diaI . ' (' . $fecI[2] . ')' . ' DE ' . mb_strtoupper($meses[intval($fecI[1])]) . ' DE ' . $fecI[0];
$fecF_let = $diaF . ' (' . $fecF[2] . ')' . ' DE ' . mb_strtoupper($meses[intval($fecF[1])]) . ' DE ' . $fecF[0];
$valor = $estudio_prev['val_contrata'];
$val_num = pesos($valor);
$objeto = mb_strtoupper($compra['objeto']);
$supervisor = $estudio_prev['nom_tercero'];
$supervisor = $estudio_prev['id_supervisor'] == '' ? 'PENDIENTE' : $supervisor;
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

$segmento = !empty($codigo_servicio) ? ($codigo_servicio['codigo'] != '' ? substr($codigo_servicio['codigo'], 0, 2) : 'XX') : 'XX';
$familia = !empty($codigo_servicio) ? ($codigo_servicio['codigo'] != '' ? substr($codigo_servicio['codigo'], 0, 4) : 'XXXX') : 'XXXX';
$clase = !empty($codigo_servicio) ? ($codigo_servicio['codigo'] != '' ? substr($codigo_servicio['codigo'], 0, 6) : 'XXXXXX') : 'XXXXXX';
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
    $plantilla->cloneRowAndSetValues('req_min', $req_min);
    $plantilla->cloneRowAndSetValues('garantia', $garantia);
    $plantilla->cloneRowAndSetValues('describ_val', $describ_val);
    }*/

$docx = 'plantilla_contrato.docx';

$plantilla = new TemplateProcessor($docx);
$plantilla->setValue('no_contrato', $no_contrato);
$plantilla->setValue('empresa', $empresa);
$plantilla->setValue('contratista', $contratista);
$plantilla->setValue('cedula', $cedula);
$plantilla->setValue('servicio', $servicio);
$plantilla->setValue('salario', $salario);
$plantilla->setValue('inicia', $inicia);
$plantilla->setValue('termina', $termina);
$plantilla->setValue('distincion', $distincion);
$plantilla->setValue('distincionM', $distincionM);
$plantilla->setValue('terminacion', $terminacion);
$plantilla->setValue('terminacionM', $terminacionM);
$plantilla->cloneRowAndSetValues('actividad', $actividad);
$plantilla->setValue('vigencia', $vigencia);
$plantilla->setValue('val_num', $val_num);
$plantilla->setValue('val_letras', $val_letras);
$plantilla->setValue('val_cdp', $val_cdp);
$plantilla->setValue('val_cdp_letras', $val_cdp_letras);
$plantilla->setValue('rubro', $rubro);
$plantilla->setValue('cod_rubro', $cod_rubro);
$plantilla->setValue('fecI_let', $fecI_let);
$plantilla->setValue('fecF_let', $fecF_let);
$plantilla->setValue('plazo', $plazo);
$plantilla->cloneRowAndSetValues('obligacion', $obligacion);

if (false) {
    $plantilla->setValue('cdp', $cdp);
    $plantilla->setValue('objeto', $objeto);
    $plantilla->setValue('val_num', $val_num);
    $plantilla->setValue('val_letras', $val_letras);
    $plantilla->cloneRowAndSetValues('actividad', $actividad);
    $plantilla->setValue('inicia', $fecI_let);
    $plantilla->setValue('termina', $fecF_let);
    $plantilla->setValue('proyecto', $proyecto);
    $plantilla->setValue('seg', $segmento);
    $plantilla->setValue('flia', $familia);
    $plantilla->setValue('clas', $clase);
    $plantilla->cloneBlock('necesidades', 0, true, false, $necesidad);
    $plantilla->cloneRowAndSetValues('producto', $producto);
    $plantilla->cloneRowAndSetValues('obligacion', $obligacion);
    $plantilla->cloneRowAndSetValues('unspsc', $listServ);
    $plantilla->cloneBlock('forma_pago', 0, true, false, $pago);
    $plantilla->setValue('rubro', $rubro);
    $plantilla->setValue('nombre_rubro', $cod_cargue['nom_rubro']);
    $plantilla->setValue('cod_rubro', $cod_cargue['id_pto_cargue']);
    $plantilla->setValue('fecha', $fecha);
    $plantilla->setValue('supervisor', $supervisor);
}
$plantilla->saveAs('contrato.docx');
header("Content-Disposition: attachment; Filename=contrato-CHT-" . $no_contrato . ".docx");
echo file_get_contents('contrato.docx');
unlink('contrato.docx');
