<?php
function pesos($valor)
{
    return '$ ' . number_format($valor, 0, ',', '.');
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `nombre1`,`nombre2`,`apellido1`,`apellido2` FROM `seg_usuarios_sistema` WHERE `id_usuario` = {$id_user}";
    $rs = $cmd->query($sql);
    $usuario = $rs->fetch(PDO::FETCH_ASSOC);
    $usuario = !empty($usuario) ? trim($usuario['nombre1'] . ' ' . $usuario['nombre2'] . ' ' . $usuario['apellido1'] . ' ' . $usuario['apellido2']) : 'XXXXXX';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `variable`, `tipo`, `contexto`, `ejemplo` FROM `ctt_variables_forms`";
    $rs = $cmd->query($sql);
    $variables = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `ctt_adquisiciones`.`id_orden`,`pto_cdp`.`id_manu`, `pto_cdp`.`id_pto_cdp` AS `id_cdp`, `pto_cdp`.`fecha` AS `fecha_cdp`
            FROM `ctt_adquisiciones`
            LEFT JOIN `pto_cdp` 
                ON (`ctt_adquisiciones`.`id_cdp` = `pto_cdp`.`id_pto_cdp`)
            WHERE `id_adquisicion` = $id_adqi LIMIT 1";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch(PDO::FETCH_ASSOC);
    $id_orden = $adquisicion['id_orden'];
    if ($id_orden == '') {
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
            WHERE (`far_alm_pedido_detalle`.`id_pedido` = {$id_orden})";
    }
    $rs = $cmd->query($sql);
    $oferta = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE `ctt_clasificacion_bn_sv`.`id_b_s` IN($cod) AND `ctt_clasificacion_bn_sv`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $codigo_servicio = $rs->fetch(PDO::FETCH_ASSOC);
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
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
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
    $compra = $rs->fetch(PDO::FETCH_ASSOC);
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
                , `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
            FROM
                `ctt_escala_honorarios`
                INNER JOIN`pto_cargue`
                ON (`ctt_escala_honorarios`.`cod_pptal` = `pto_cargue`.`id_cargue`)
            WHERE `ctt_escala_honorarios`.`id_tipo_b_s` = $tipo_bn AND `ctt_escala_honorarios`.`vigencia` = '$vigencia'";
    $rs = $cmd->query($sql);
    $cod_cargue = $rs->fetch(PDO::FETCH_ASSOC);
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
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctt_estudios_previos`
            INNER JOIN `tb_forma_pago_compras` 
                ON (`ctt_estudios_previos`.`id_forma_pago` = `tb_forma_pago_compras`.`id_form_pago`)
            LEFT JOIN `tb_terceros` 
                ON (`ctt_estudios_previos`.`id_supervisor` = `tb_terceros`.`id_tercero_api`)
            WHERE `id_compra` = '$id_adqi'";
    $rs = $cmd->query($sql);
    $estudio_prev = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$cmd = null;
$id_ep = $estudio_prev['id_est_prev'];
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
            WHERE `seg_garantias_compra`.`id_est_prev` = $id_ep";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE `id_compra` = '$id_adqi'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_datos_ips`.`nit_ips` AS `nit`
                , `tb_datos_ips`.`dv` AS `dig_ver`
                , `tb_datos_ips`.`razon_social_ips` AS `nombre`
                , `tb_municipios`.`nom_municipio`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`) LIMIT 1";
    $rs = $cmd->query($sql);
    $compania = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$servicio = $id_orden == '' ? $oferta[0]['bien_servicio'] : 'XXXXXXXXX';
$empresa = $compania['nombre'];
$municipio = $compania['nom_municipio'];
$n_cdp = $adquisicion['id_manu'] == '' ? 'XXXXXXXXX' : $adquisicion['id_manu'];
$meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
$fecI = explode('-', $estudio_prev['fec_ini_ejec']);
$fecF = explode('-', $estudio_prev['fec_fin_ejec']);
$fecha = mb_strtoupper($fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0]);
$fecha_anexo = $fecI[2] . ' de ' . $meses[intval($fecI[1])] . ' de ' . $fecI[0];
$letras = new NumberFormatter("es", NumberFormatter::SPELLOUT);
$diaI = $fecI[2] == '01' ? 'PRIMERO' : mb_strtoupper($letras->format($fecI[2]));
$diaF = $fecF[2] == '01' ? 'PRIMERO' : mb_strtoupper($letras->format($fecF[2]));
$fecI_let =  $diaI . ' (' . $fecI[2] . ')' . ' DE ' . mb_strtoupper($meses[intval($fecI[1])]) . ' DE ' . $fecI[0];
$fecF_let = $diaF . ' (' . $fecF[2] . ')' . ' DE ' . mb_strtoupper($meses[intval($fecF[1])]) . ' DE ' . $fecF[0];
$fec_inicia = mb_strtolower($fecI_let);
$fec_fin = mb_strtolower($fecF_let);
$valor = $estudio_prev['val_contrata'];
$val_num = pesos($valor);
$objeto_ep = mb_strtoupper($compra['objeto']);
$supervisor = $estudio_prev['nom_tercero'];
$supervisor = $estudio_prev['id_supervisor'] == '' ? 'PENDIENTE' : $supervisor;
$val_ep = str_replace('-', '', mb_strtoupper($letras->format($valor, 2)));
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
$plazo = $p_mes == '' ? $p_dia : $p_mes . $y . $p_dia;
$forma_pago = $contrato['descripcion'];

/*
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
*/
