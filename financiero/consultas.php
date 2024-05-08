<?php
// Función para consuiltar fecha de cierre por modulo
function fechaCierre($vigencia, $modulo, $cx)
{
    $vigencia = $_SESSION['vigencia'];
    try {
        $sql = "SELECT fecha_cierre FROM tb_fin_periodos WHERE id_modulo = $modulo AND vigencia = $vigencia";
        $rs = $cx->query($sql);
        $cierre = $rs->fetch();
        $fecha_cierre = empty($cierre) ? date('Y-m-d') : date('Y-m-d', strtotime($cierre['fecha_cierre']));
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $fecha_cierre;
}
// Funcion para convertir numero a letras
function numeroLetras($numero)
{
    if (!is_numeric($numero)) {
        return false;
    }
    $numero_letras = '';
    $pesos = 'PESOS';
    $centavos = 'CENTAVOS';
    $parte = explode(".", $numero);
    $entero = $parte[0];
    // obtener modulo de un numero
    $modulo = $entero % 1000000;
    if ($modulo == 0) {
        $pesos = 'de pesos';
    }
    if (isset($parte[1])) {
        $decimos = strlen($parte[1]) == 1 ? $parte[1] . '0' : $parte[1];
    }
    $fmt = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    if (is_array($parte)) {
        $numero_letras = $fmt->format($entero) . ' ' . $pesos;
        if (isset($decimos) && $decimos > 0) {
            if ($parte[1] < 2) {
                $centavos = 'CENTAVO';
            }
            $numero_letras .= ' con ' . $fmt->format($decimos) . ' ' . $centavos;
        }
    }
    $numero_letras = str_replace("uno", "un", $numero_letras);
    $numero_letras = mb_strtoupper($numero_letras . ' M/CTE.');
    return $numero_letras;
}

// Función para consultar fecha de sesión del usuario
function fechaSesion($vigencia, $usuario, $cx)
{
    try {
        $sql = "SELECT fecha FROM tb_fin_fecha WHERE vigencia = $vigencia AND id_usuario = '$usuario'";
        $rs = $cx->query($sql);
        $fecha_sesion = $rs->fetch();
        if (!empty($fecha_sesion)) {
            $fecha = date('Y-m-d', strtotime($fecha_sesion['fecha']));
        } else {
            $fecha = date('Y-m-d');
        }
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $fecha;
}

// Funcion para convertir a fecha larga
function fechaLarga($fecha, $tipo)
{
    $meses = array(
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre'
    );
    $prefijo = "A LOS";
    $dia = 'días';
    $objFecha = new DateTime($fecha, new DateTimeZone('America/Mexico_City'));
    $mes = $objFecha->format('m');
    $dia_letras = numeroLetras($objFecha->format('d'));
    $numero_letras = str_replace("PESOS M/CTE.", "", $dia_letras);
    if ($objFecha->format('d') == '01') {
        $numero_letras = str_replace("UN", "PRIMER", $numero_letras);
        $prefijo = 'AL';
        $dia = 'DÍA';
    }
    if ($tipo == 0) {
        $fecha_larga = $meses[$mes] . ' ' . $objFecha->format('d') . ' de ' . $objFecha->format('Y');
    } else {
        $fecha_larga = mb_strtolower($prefijo . ' ' . $numero_letras . '(' . $objFecha->format('d') . ')' . ' ' . $dia . ' del mes de ' . $meses[$mes] . ' de ' . $objFecha->format('Y'));
    }
    return $fecha_larga;
}

// función para establecer el saldo de un rubro de gastos a cierta fecha de una vigencia
function saldoRubroGastos($vigencia, $id_cargue, $cx)
{
    $fecha_ini = $vigencia . '-01-01';
    $fecha_fin = $vigencia . '-12-31';
    try {
        $sql = "SELECT  
                    `id_tipo_mod`, SUM(`valor_deb`) AS `debito`, SUM(`valor_cred`) AS `credito` 
                FROM 
                    (SELECT
                        `pto_mod`.`id_tipo_mod`
                        , `pto_mod_detalle`.`valor_deb`
                        , `pto_mod_detalle`.`valor_cred`
                        , `pto_mod`.`fecha`
                    FROM
                        `pto_mod_detalle`
                        INNER JOIN `pto_mod` 
                            ON (`pto_mod_detalle`.`id_pto_mod` = `pto_mod`.`id_pto_mod`)
                    WHERE (`pto_mod`.`estado` = 1 AND `pto_mod_detalle`.`id_cargue`  = $id_cargue AND `pto_mod`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_fin')
                    UNION ALL
                    SELECT
                        '0' AS `id`
                        , `pto_cdp_detalle`.`valor`
                        , `pto_cdp_detalle`.`valor_liberado`
                        , `pto_cdp`.`fecha`
                    FROM
                        `pto_cdp_detalle`
                        INNER JOIN `pto_cdp`
                            ON (`pto_cdp_detalle`.`id_pto_cdp` = `pto_cdp`.`id_pto_cdp`)
                    WHERE (`pto_cdp_detalle`.`id_rubro` = $id_cargue AND `pto_cdp`.`estado` = 1 AND `pto_cdp`.`fecha` BETWEEN '$fecha_ini' AND '$fecha_fin')
                    ) AS `t1`
                    GROUP BY `id_tipo_mod`";
        $rs = $cx->query($sql);
        $saldos = $rs->fetchAll();
        $cx = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $saldos;
}

// Funcion para determinar el saldo que tiene un cdp para registrar
function saldoCdp($cdp, $rubro, $cx)
{
    try {
        $sql = "SELECT sum(valor) as total FROM pto_documento_detalles WHERE id_pto_doc = $cdp AND rubro = '$rubro'";
        $rs = $cx->query($sql);
        $saldo = $rs->fetch();
        $valor_cdp = $saldo['total'];
        //$sql = "SELECT sum(valor) as registrado FROM pto_documento_detalles WHERE id_auto_dep = $cdp AND rubro = '$rubro' AND tipo_mov='CRP'";
        $sql = "SELECT
                    SUM(pto_documento_detalles.valor) AS registrado
                FROM
                    pto_documento_detalles
                INNER JOIN pto_documento ON (pto_documento_detalles.id_pto_doc = pto_documento.id_pto_doc)
                WHERE pto_documento_detalles.rubro ='$rubro' AND (pto_documento_detalles.tipo_mov ='CRP' OR pto_documento_detalles.tipo_mov ='LRP') AND pto_documento_detalles.id_auto_dep =$cdp AND pto_documento.estado=0;;";
        $rs = $cx->query($sql);
        $saldo = $rs->fetch();

        $valor_registrado = $saldo['registrado'];
        $saldo = $valor_cdp - $valor_registrado;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $saldo;
}

function Nivel($numero)
{
    // Mapeo de los valores de entrada a los valores de salida deseados
    $mapeo = [
        1 => 1,
        2 => 2,
        4 => 3,
        6 => 4,
        8 => 5,
        10 => 6,
        12 => 7,
    ];

    $cantidad = intval(floor(log10(abs($numero))) + 1);

    if ($numero == 0) {
        return 'error';
    }
    return array_key_exists($cantidad, $mapeo) ? $mapeo[$cantidad] : 'error';
}

function GetValoresCxP($id_doc, $cmd)
{
    try {
        $sql = "SELECT
                    `ctb_fuente`.`nombre` AS `fuente`
                    , `ctb_doc`.`id_ctb_doc`
                    , `ctb_doc`.`fecha`
                    , `ctb_doc`.`id_manu`
                    , `ctb_doc`.`detalle`
                    , `ctb_doc`.`id_tercero`
                    , `ctb_doc`.`estado`
                    , `ctb_doc`.`id_crp`
                    , IFNULL(`factura`.`val_factura`,0) AS `val_factura`
                    , IFNULL(`imputacion`.`val_imputacion`,0) AS `val_imputacion`
                    , IFNULL(`centro_costo`.`val_ccosto`,0) AS `val_ccosto`
                    , IFNULL(`retencion`.`val_retencion`,0) AS `val_retencion`
                FROM
                    `ctb_doc`
                    INNER JOIN `ctb_fuente` 
                        ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
                    LEFT JOIN
                        (SELECT
                            `id_ctb_doc`
                            , SUM(`valor_base`) AS `val_factura`
                        FROM
                            `ctb_factura`
                        WHERE (`id_ctb_doc` = $id_doc)) AS `factura`
                        ON (`ctb_doc`.`id_ctb_doc` = `factura`.`id_ctb_doc`)
                    LEFT JOIN 
                        (SELECT
                            `id_ctb_doc`
                            , SUM(`valor`) AS `val_imputacion`
                        FROM
                            `pto_cop_detalle`
                        WHERE (`id_ctb_doc` = $id_doc)) AS `imputacion`
                        ON (`ctb_doc`.`id_ctb_doc` = `imputacion`.`id_ctb_doc`)
                    LEFT JOIN
                        (SELECT
                            `id_ctb_doc`
                            , SUM(`valor`) AS `val_ccosto`
                        FROM
                            `ctb_causa_costos`
                        WHERE (`id_ctb_doc` = $id_doc)) AS `centro_costo`
                        ON (`ctb_doc`.`id_ctb_doc` = `centro_costo`.`id_ctb_doc`)
                    LEFT JOIN
                        (SELECT
                            `id_ctb_doc`
                            , SUM(`valor_retencion`) AS `val_retencion`
                        FROM
                            `ctb_causa_retencion`
                        WHERE (`id_ctb_doc` = $id_doc)) AS `retencion`
                        ON (`ctb_doc`.`id_ctb_doc` = `retencion`.`id_ctb_doc`)
                WHERE (`ctb_doc`.`id_ctb_doc` = $id_doc)";
        $rs = $cmd->query($sql);
        $datosDoc = $rs->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    return $datosDoc;
}
