<?php

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';

function redondeo($value, $places)
{
    $mult = pow(10, abs($places));
    return $places < 0 ? ceil($value / $mult) * $mult : ceil($value * $mult) / $mult;
}
$anio = $_SESSION['vigencia'];
$er = '';
$er .= '
  <div class="table-responsive w-100">
  <table class="table table-striped table-bordered table-sm">
  <thead>
    <tr>
      <th scope="col">No. Doc.</th>
      <th scope="col">Empleado</th>
      <th scope="col">Estado</th>
    </tr>
  </thead>
  <tbody>';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM
                nom_valxvigencia
            INNER JOIN nom_conceptosxvigencia 
                ON (nom_valxvigencia.id_concepto = nom_conceptosxvigencia.id_concp)
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            WHERE anio = '$anio';";
    $rs = $cmd->query($sql);
    $valxvig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

foreach ($valxvig as $vxv) {
    if ($vxv['id_concepto'] == '1') {
        $smmlv = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '2') {
        $auxiliotranporte = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '3') {
        $auxalim = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '6') {
        $uvt = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '7') {
        $bbs = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '8') {
        $representacion = floatval($vxv['valor']);
    }
    if ($vxv['id_concepto'] == '9') {
        $basealim = floatval($vxv['valor']);
    }
}
$dia = '01';
$mes = $_POST['slcMesLiqNom'];
$id_user = $_SESSION['id_user'];
switch ($mes) {
    case '01':
    case '03':
    case '05':
    case '07':
    case '08':
    case '10':
    case '12':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-31';
        break;
    case '02':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        if (date('L', strtotime("$anio-01-01")) == '1') {
            $bis = '29';
        } else {
            $bis = '28';
        }
        $fec_f = $anio . '-' . $mes . '-' . $bis;
        break;
    case '04':
    case '06':
    case '09':
    case '11':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-30';
        break;
    default:
        echo 'Error Fatal';
        exit();
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_he_trab, id_empleado, nom_horas_ex_trab.id_he, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, codigo, factor
            FROM
                nom_horas_ex_trab
            INNER JOIN nom_tipo_horaex
                ON (nom_horas_ex_trab.id_he = nom_tipo_horaex.id_he)
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f' AND `tipo` = 1";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_eps`
            FROM
                `nom_novedades_eps`
            WHERE `id_novedad`  IN (SELECT MAX(`id_novedad`) FROM `nom_novedades_eps` GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_arl`
            FROM
                `nom_novedades_arl`
            WHERE `id_novarl`  IN (SELECT MAX(`id_novarl`) FROM `nom_novedades_arl` GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_afp`
            FROM
                `nom_novedades_afp`
            WHERE `id_novafp`  IN (SELECT MAX(`id_novafp`) FROM `nom_novedades_afp` GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//cambio
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`
                , `salario_integral`
                , `no_documento`
                , `sub_alimentacion`
                , `tipo_empleado`
                ,`representacion`
                , CONCAT(`nombre1`, ' ', `nombre2`, ' ',`apellido1`, ' ', `apellido2`) AS `nombre`
                , `cargo` 
                , `subtipo_empleado`
            FROM `nom_empleado`
            WHERE  `estado` = '1'";
    $rs = $cmd->query($sql);
    $emple = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_novedades_arl`.`id_novarl`
                , `nom_novedades_arl`.`id_empleado`
                , `nom_novedades_arl`.`id_arl`
                , `nom_riesgos_laboral`.`id_rlab`
                , `nom_riesgos_laboral`.`cotizacion`
                , `nom_novedades_arl`.`fec_afiliacion`
            FROM
                `nom_novedades_arl`
                INNER JOIN `nom_riesgos_laboral` 
                    ON (`nom_novedades_arl`.`id_riesgo` = `nom_riesgos_laboral`.`id_rlab`)
            WHERE `nom_novedades_arl`.`id_novarl`
                IN(SELECT MAX(`id_novarl`) AS `id_novarl` FROM `nom_novedades_arl` WHERE SUBSTRING(`fec_afiliacion`, 1, 4)<= '$anio' GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $riesgos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                id_empleado, COUNT(id_embargo) AS cant_embargos
            FROM 
                (SELECT * 
                FROM
                    nom_embargos
                WHERE estado = '1') AS t 
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $embargos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM nom_incapacidad
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM nom_licenciasmp
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $licencias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_licnr, id_empleado, fec_inicio, fec_fin, dias_inactivo, dias_habiles
            FROM nom_licenciasnr
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $licenciasnr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT id_licluto, id_empleado, fec_inicio, fec_fin, dias_inactivo, dias_habiles
            FROM nom_licencia_luto
            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f'";
    $rs = $cmd->query($sql);
    $licLuto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM nom_vacaciones
            WHERE estado = 1";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT * FROM tb_datos_ips";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `val_pagoxdep` FROM `nom_pago_dependiente`";
    $rs = $cmd->query($sql);
    $pagoxdpte = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` FROM `nom_nominas` WHERE `mes` = '$mes' AND `vigencia` = '$anio' AND `tipo` = 'N'";
    $rs = $cmd->query($sql);
    $id_nom = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `val_liq_ps`,`id_empleado`
            FROM
                `nom_liq_prima`
            WHERE `id_liq_prima` IN (SELECT MAX(`id_liq_prima`) FROM `nom_liq_prima` GROUP BY `id_empleado`)";
    $res = $cmd->query($sql);
    $primadserv = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`, SUM(`val_bsp`) AS `val_bsp`
            FROM 
                `nom_liq_bsp`
            WHERE `id_bonificaciones` IN 
                (SELECT
                    MAX(`nom_liq_bsp`.`id_bonificaciones`) AS `id_bonificaciones`
                FROM
                    `nom_liq_bsp`
                INNER JOIN `nom_nominas`
                    ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE ((`nom_nominas`.`tipo` = 'N' OR `nom_nominas`.`tipo` = 'PS') AND `nom_nominas`.`vigencia` <= '$anio')
                GROUP BY `nom_liq_bsp`.`id_empleado`
                UNION ALL
                SELECT
                    MAX(`nom_liq_bsp`.`id_bonificaciones`) AS `id_bonificaciones`
                FROM
                    `nom_liq_bsp`
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE (`nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$anio')
                GROUP BY `nom_liq_bsp`.`id_empleado`)
            GROUP BY `id_empleado`";
    $res = $cmd->query($sql);
    $bonxserv = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_indemniza`, `id_empleado`, `cant_dias`, `estado`
            FROM
                `nom_indemniza_vac`
            WHERE (`estado` = 1)";
    $res = $cmd->query($sql);
    $indemnizaciones = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `t1`.`id_empleado`, SUM(`cant_dias`) AS `total_dias`
            FROM
                (SELECT
                    `id_empleado`, CONCAT(`anio`, `mes`) AS `corte`
                FROM
                    `nom_liq_bsp`
                WHERE `id_bonificaciones` 
                IN (
                    SELECT 
                        MAX(`id_bonificaciones`) AS `id_bsp` 
                        FROM `nom_liq_bsp`
                            INNER JOIN `nom_nominas`
                                ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` <> 'RA'
                        GROUP BY `id_empleado`
                )) AS `t1`
            INNER JOIN 
                (SELECT
                    `id_empleado`, `cant_dias`, CONCAT(`anio`, `mes`) AS `compara`
                FROM
                    `nom_liq_dias_lab`) AS `t2`
                    ON (`compara` > `corte`)
            WHERE `t1`.`id_empleado` = `t2`.`id_empleado`
            GROUP BY `t1`.`id_empleado`";
    $res = $cmd->query($sql);
    $dias_bonificacion = $res->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_cuota_sindical`) AS `id_cuota_sindical`, `id_empleado`
            FROM
                `nom_cuota_sindical`
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $porcuotasind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_dcto`, `id_empleado`, `valor`
            FROM
                `nom_otros_descuentos`
            WHERE (`estado` = 1 AND  (`fecha_fin` >= '$fec_f' OR `fecha_fin` IS NULL))";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$dossml = $smmlv * 2;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$mesliq = 0;
if (isset($_POST['check'])) {
    $list_liquidar = $_POST['check'];
    $ids = implode(',', $list_liquidar);
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT 
                    `nom_empleado`.`id_empleado`
                    , `t1`.`val_bsp`
                    , `t3`.`val_liq_ps` AS `val_prima_servicio`
                    , `t4`.`val_liq_pv` AS `val_prima_navidad`
                    , `t5`.`val_liq` AS `val_vacaciones`
                    , `t5`.`val_prima_vac` AS `val_prima_vacaciones`
                    , `t5`.`val_bon_recrea` AS `val_bon_recreacion`
                FROM
                    `nom_empleado`
                    LEFT JOIN  
                    (SELECT 
                        `id_empleado`,`val_bsp`
                    FROM `nom_liq_bsp`
                    WHERE `id_bonificaciones` IN (SELECT MAX(`id_bonificaciones`) FROM `nom_liq_bsp` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t1`
                        ON (`t1`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    LEFT JOIN
                    (SELECT   
                        `id_empleado`
                        , SUM(`val_liq_ps`) AS `val_liq_ps` 
                        , `corte` AS `corte_prim_sv`
                    FROM `nom_liq_prima` 
                    WHERE `id_liq_prima` IN 
                        (SELECT
                            MAX(`id_liq_prima`) AS `id_lp`
                        FROM
                            `nom_liq_prima`
                        INNER JOIN `nom_nominas`
                            ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` = 'PV'
                        GROUP BY `id_empleado`
                        UNION ALL 
                        SELECT
                            MAX(`id_liq_prima`) AS `id_lp`
                        FROM
                            `nom_liq_prima`
                        INNER JOIN `nom_nominas`
                            ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$anio'
                        GROUP BY `id_empleado`)
                    GROUP BY `id_empleado`) AS `t3`
                        ON (`nom_empleado`.`id_empleado` = `t3`.`id_empleado`)
                    LEFT JOIN 
                    (SELECT 
                        `id_empleado`,`val_liq_pv`
                    FROM `nom_liq_prima_nav`
                    WHERE `id_liq_privac` IN (SELECT MAX(`id_liq_privac`) FROM `nom_liq_prima_nav` WHERE `id_empleado`IN ($ids) GROUP BY `id_empleado`)) AS `t4`
                        ON (`nom_empleado`.`id_empleado` = `t4`.`id_empleado`)
                    LEFT JOIN 
                    (SELECT
                        `nom_vacaciones`.`id_empleado`
                        , SUM(`nom_liq_vac`.`val_prima_vac`) AS `val_prima_vac`
                        , SUM(`nom_liq_vac`.`val_liq`) AS `val_liq`
                        , SUM(`nom_liq_vac`.`val_bon_recrea`) AS `val_bon_recrea`
                        , `nom_vacaciones`.`corte` AS `corte`  
                    FROM
                        `nom_liq_vac`
                    INNER JOIN `nom_vacaciones` 
                        ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                    WHERE (`nom_liq_vac`.`id_vac` IN 
                        (SELECT 
                            MAX(`nom_vacaciones`.`id_vac`) 
                        FROM  `nom_vacaciones`
                        INNER JOIN  nom_liq_vac
                            ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                        INNER JOIN `nom_nominas`
                            ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` = 'N' OR `nom_nominas`.`tipo` = 'VC'
                        GROUP BY `id_empleado`
                        UNION ALL 
                        SELECT 
                            MAX(`nom_vacaciones`.`id_vac`) 
                        FROM  `nom_vacaciones`
                        INNER JOIN  nom_liq_vac
                            ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                        INNER JOIN `nom_nominas`
                            ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$anio'
                        GROUP BY `id_empleado`))
                    GROUP BY `id_empleado`) AS `t5`
                        ON (`nom_empleado`.`id_empleado` = `t5`.`id_empleado`)
                WHERE `nom_empleado`.`id_empleado` IN ($ids)";
        $rs = $cmd->query($sql);
        $lastpay = $rs->fetchAll(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    if (empty($id_nom)) {
        $descripcion = 'LIQUIDACIÓN MENSUAL EMPLEADOS';
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `nom_nominas` (`mes`, `vigencia`, `descripcion`, `fec_reg`, `id_user_reg`) VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $mes, PDO::PARAM_STR);
            $sql->bindParam(2, $anio, PDO::PARAM_STR);
            $sql->bindParam(3, $descripcion, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_user, PDO::PARAM_INT);
            $sql->execute();
            $id_nomina = $cmd->lastInsertId();
            if ($id_nomina > 0) {
            } else {
                echo $sql->errorInfo()[2] . '1';
                exit();
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        $id_nomina = $id_nom['id_nomina'];
    }
    foreach ($list_liquidar as $i) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $sql = "SELECT
                    `nom_liq_salario`.`id_sal_liq`
                FROM
                    `nom_liq_salario`
                    INNER JOIN `nom_nominas` 
                        ON (`nom_liq_salario`.`id_nomina` = `nom_nominas`.`id_nomina`)
                WHERE (`nom_nominas`.`mes` = '$mes' AND `nom_nominas`.`vigencia` = '$anio' AND `nom_nominas`.`tipo` = 'N' AND `nom_liq_salario`.`id_empleado` = $i)";
        $rs = $cmd->query($sql);
        $nomliq = $rs->fetch();
        $cmd = null;
        $key = array_search($i, array_column($arl, 'id_empleado'));
        $id_arl = false !== $key ? $arl[$key]['id_arl'] : null;
        $key = array_search($i, array_column($riesgos, 'id_empleado'));
        $nivel_rlab = false !== $key ? $riesgos[$key]['id_rlab'] : 0;
        $cot_rlab = false !== $key ? $riesgos[$key]['cotizacion'] : 0;
        if (empty($nomliq)) {
            $key = array_search($i, array_column($eps, 'id_empleado'));
            $id_eps = false !== $key ? $eps[$key]['id_eps'] : null;
            $key = array_search($i, array_column($afp, 'id_empleado'));
            $id_afp = false !== $key ? $afp[$key]['id_afp'] : null;
            $key = array_search($i, array_column($emple, 'id_empleado'));
            $sal_integ = false !== $key ? $emple[$key]['salario_integral'] : null;
            $tipo_emp = false !== $key ? $emple[$key]['tipo_empleado'] : 0;
            $subtip_emp = false !== $key ? $emple[$key]['subtipo_empleado'] : 1; //cambio
            $grepresenta = false !== $key ? $emple[$key]['representacion'] : 0;
            $salario = 0;
            $empleado = $i;
            $salbase = $_POST['numSalBas_' . $i];
            $diaslab = $_POST['numDiaLab_' . $i];
            //liquida horas extras 
            $devhe = 0;
            $auxtransp = 0;
            $basetransporte = ($salbase * 0) + $salbase;
            if ($basetransporte <= $dossml) {
                $auxtransp = $auxiliotranporte / 30;
            } else {
                $auxtransp = 0;
            }
            $auxt = $auxtransp * $diaslab;
            //subsidio de alimentación

            if ($salbase <= $basealim) {
                $auxali = ($auxalim / 30) * $diaslab;
            } else {
                $auxali = 0;
            }
            if ($tipo_emp == 12 || $tipo_emp == 8) {
                $auxali = 0;
                $auxt = 0;
            }
            if ($grepresenta == 1) {
                $gasrep = $representacion;
            } else {
                $gasrep = 0;
            }
            //liquidar licencia por luto
            $daylcluto = 0;
            $vallcluto = 0;
            $key = array_search($i, array_column($licLuto, 'id_empleado'));
            if ($key !== false) {
                $idlcluto = $licLuto[$key]['id_licluto'];
                $inilicluto = $licLuto[$key]['fec_inicio'];
                $finlicluto = $licLuto[$key]['fec_fin'];
                $daylcluto = $licLuto[$key]['dias_inactivo'];
                $vallcluto = $daylcluto * ($salbase / 30);
                if (intval($daylcluto) == 31 || ($mes == '02' && intval($daylcluto) >= 28)) {
                    $daylcluto = 30;
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_licluto` (`id_licluto`, `fec_inicio`, `fec_fin`, `dias_licluto`, `mes_licluto`, `anio_licluto`, `fec_reg`,`id_nomina`,`val_liq`) 
                        VALUES (?, ?, ?, ?, ?, ?,?,?,?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idlcluto, PDO::PARAM_INT);
                    $sql->bindParam(2, $inilicluto, PDO::PARAM_STR);
                    $sql->bindParam(3, $finlicluto, PDO::PARAM_STR);
                    $sql->bindParam(4, $daylcluto, PDO::PARAM_INT);
                    $sql->bindParam(5, $mes, PDO::PARAM_STR);
                    $sql->bindParam(6, $anio, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
                    $sql->bindParam(9, $vallcluto, PDO::PARAM_INT);
                    $sql->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $sql->errorInfo()[2] . 'LUTO';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            //liquidar licencia no remunerada
            $daylcnr = 0;
            $saludlicnrpatronal = 0;
            $pensionlicnrpatronal = 0;
            foreach ($licenciasnr as $lcnr) {
                if (intval($i) == intval($lcnr['id_empleado'])) {
                    $diflcnr = null;
                    $filcnr = intval(date('Ym', strtotime($lcnr['fec_inicio'])));
                    $fflcnr = intval(date('Ym', strtotime($lcnr['fec_fin'])));
                    $diflcnr = $fflcnr - $filcnr;
                    $idlcnr = $lcnr['id_licnr'];
                    $inlic = $lcnr['fec_inicio'];
                    $finlic = $lcnr['fec_fin'];
                    if (intval($diflcnr) > 0) {
                        $nextday = date("Y-m-d", strtotime($fec_f . "+1 day"));
                        $aperlicnr = new DateTime($inlic);
                        $cierlicnr = new DateTime($fec_f);
                        $timelcnr = $aperlicnr->diff($cierlicnr);
                        $daylcnr = intval($timelcnr->format('%d')) + 1;
                        $finlicnr = $fec_f;
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "UPDATE nom_licenciasnr SET  fec_inicio = ? WHERE id_licnr = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $nextday, PDO::PARAM_STR);
                        $sql->bindParam(2, $idlcnr, PDO::PARAM_INT);
                        $sql->execute();
                        $cmd = null;
                    } else {
                        $aperlc = new DateTime($inlic);
                        $closelc = new DateTime($finlic);
                        $timelic = $aperlc->diff($closelc);
                        $finlicnr = $finlic;
                        $daylcnr = intval($timelic->format('%d')) + 1;
                    }
                    if (intval($daylcnr) == 31 || ($mes == '02' && intval($daylcnr) >= 28)) {
                        $daylcnr = 30;
                    }
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO nom_liq_licnr (id_licnr, fec_inicio, fec_fin, dias_licnr, mes_licnr, anio_licnr, fec_reg,id_nomina) 
                                VALUES (?, ?, ?, ?, ?, ?,?,?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idlcnr, PDO::PARAM_INT);
                        $sql->bindParam(2, $inlic, PDO::PARAM_STR);
                        $sql->bindParam(3, $finlicnr, PDO::PARAM_STR);
                        $sql->bindParam(4, $daylcnr, PDO::PARAM_INT);
                        $sql->bindParam(5, $mes, PDO::PARAM_STR);
                        $sql->bindParam(6, $anio, PDO::PARAM_STR);
                        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . '5';
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                    $base = $salbase / 30;
                    $baselicnr = $daylcnr * $base;
                    $saludlicnrpatronal = $baselicnr * 0.085;
                    $pensionlicnrpatronal = $baselicnr * 0.12;
                }
            }
            $dayBSP = 30 - $daylcnr;
            $bsp_salarial = 0;
            $bsp = (($salbase + $gasrep) <= $bbs ? ($salbase + $gasrep) * 0.5 : ($salbase + $gasrep) * 0.35);
            $keybxsp = array_search($i, array_column($bonxserv, 'id_empleado'));
            if ($keybxsp !== false) {
                $keybsp = array_search($i, array_column($dias_bonificacion, 'id_empleado'));
                $esta = false !== $keybsp ? $dias_bonificacion[$keybsp]['total_dias'] + $dayBSP : 0;
            } else {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "SELECT
                                `id_empleado`
                                , SUM(`cant_dias`) AS `total_dias`
                            FROM
                                `nom_liq_dias_lab`
                            WHERE (`id_empleado`  = $i)";
                    $res = $cmd->query($sql);
                    $dyastobs = $res->fetch(PDO::FETCH_ASSOC);
                    $esta = $dyastobs['total_dias'] + $dayBSP;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
            }
            $keycargo = array_search($i, array_column($emple, 'id_empleado'));
            $cargo = false !== $keycargo ? $emple[$keycargo]['cargo'] : 0;
            if ($esta >= 360) {
                if (!($cargo == 1 || $cargo == 2 || $cargo == 3 || $cargo == 12 || $cargo == 14 || $cargo == 18)) {
                    $bsp_salarial = $bsp;
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `nom_liq_bsp`(`id_empleado`, `val_bsp`, `id_user_reg`, `fec_reg`, `id_nomina`, `mes`, `anio`) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $i, PDO::PARAM_INT);
                        $sql->bindParam(2, $bsp, PDO::PARAM_STR);
                        $sql->bindParam(3, $id_user, PDO::PARAM_INT);
                        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                        $sql->bindParam(6, $mes, PDO::PARAM_STR);
                        $sql->bindParam(7, $anio, PDO::PARAM_STR);
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . '2';
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
            $valhora = $salbase / 230;
            if (!empty($horas)) {
                foreach ($horas as $h) {
                    if ($h['id_empleado'] == $i) {
                        $idhe = $h['id_he_trab'];
                        if ($h['codigo'] == 3 || $h['codigo'] == 5) {
                            $factor = $h['factor'] / 100;
                            $cnthe = $h['cantidad_he'];
                        } else {
                            $factor = ($h['factor'] / 100) + 1;
                            $cnthe = $h['cantidad_he'];
                        }
                        $valhe = $valhora * $factor * $cnthe;
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO nom_liq_horex (id_he_lab, val_liq, mes_he, anio_he, fec_reg, id_nomina) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idhe, PDO::PARAM_INT);
                            $sql->bindParam(2, $valhe, PDO::PARAM_STR);
                            $sql->bindParam(3, $mes, PDO::PARAM_STR);
                            $sql->bindParam(4, $anio, PDO::PARAM_STR);
                            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                            $sql->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $sql->errorInfo()[2] . '3';
                            } else {
                                $heliq = 0;
                                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                                $sql = "UPDATE `nom_horas_ex_trab` SET  `tipo` = ? WHERE `id_he_trab` = ?";
                                $sql = $cmd->prepare($sql);
                                $sql->bindParam(1, $heliq, PDO::PARAM_STR);
                                $sql->bindParam(2, $idhe, PDO::PARAM_INT);
                                $sql->execute();
                            }
                            $cmd = null;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
            }
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "SELECT id_empleado, SUM(val_liq) AS tot_he
                        FROM 
                            (SELECT id_empleado, val_liq
                            FROM
                                nom_liq_horex
                            INNER JOIN nom_horas_ex_trab 
                                ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
                            WHERE fec_inicio BETWEEN '$fec_i' AND '$fec_f' AND id_empleado = '$i') AS the
                        GROUP BY id_empleado";
                $rs = $cmd->query($sql);
                $tothe = $rs->fetch();
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            if (!empty($tothe)) {
                $devhe = $tothe['tot_he'];
            }
            //liquidar licencia
            $vallic = 0;
            $lic = 0;
            $daylc = 0;
            $saludlc = 0;
            $pensionlc = 0;
            $saludlcem = 0;
            $pensionlcem = 0;
            $vallicen = 0;
            foreach ($licencias as $lc) {
                if (intval($i) == intval($lc['id_empleado'])) {
                    $diflc = null;
                    $filc = intval(date('Ym', strtotime($lc['fec_inicio'])));
                    $fflc = intval(date('Ym', strtotime($lc['fec_fin'])));
                    $diflc = $fflc - $filc;
                    $idlc = $lc['id_licmp'];
                    $tiplc = $lc['tipo'];
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $sql = "SELECT id_licmp, SUM(dias_liqs) AS tot_dias
                            FROM nom_liq_licmp
                            GROUP BY id_licmp
                            HAVING id_licmp = '$idlc'";
                    $rs = $cmd->query($sql);
                    $diaslic = $rs->fetch();
                    $cmd = null;
                    if (!isset($diaslic)) {
                        $dialic = $diaslic['tot_dias'];
                        $valdialc = $diaslic['val_dialc'];
                    } else {
                        $dialic = 0;
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "SELECT id_empleado, SUM(cant_dias) AS dias_cot
                            FROM nom_liq_dias_lab
                            GROUP BY id_empleado
                            HAVING id_empleado = '$i'";
                        $rs = $cmd->query($sql);
                        $diascot = $rs->fetch();
                        $cmd = null;
                        if ($tiplc == '1') {
                            if (intval($diascot['dias_cot']) >= 270) {
                                $valdialc = $salbase / 30;
                            } else {
                                $valdialc = ($diascot['dias_cot'] * $salbase) / (30 * 270);
                            }
                        } else {
                            $valdialc = $salbase / 30;
                        }
                    }
                    $inlic = $lc['fec_inicio'];
                    $finlic = $lc['fec_fin'];
                    if (intval($diflc) > 0) {
                        $nextday = date("Y-m-d", strtotime($fec_f . "+1 day"));
                        $aperlic = new DateTime($inlic);
                        $cierlic = new DateTime($fec_f);
                        $timelc = $aperlic->diff($cierlic);
                        $daylc = intval($timelc->format('%d')) + 1;
                        $finlic = $fec_f;
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "UPDATE nom_licenciasmp SET  fec_inicio = ? WHERE id_licmp = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $nextday, PDO::PARAM_STR);
                        $sql->bindParam(2, $idlc, PDO::PARAM_INT);
                        $sql->execute();
                        $cmd = null;
                    } else {
                        $aperlc = new DateTime($inlic);
                        $closelc = new DateTime($finlic);
                        $timelic = $aperlc->diff($closelc);
                        $daylc = intval($timelic->format('%d')) + 1;
                    }
                    $banlc = 0;
                    $feblc = 0;
                    $dayliqlc = $dialic + $daylc;
                    if (intval($daylc) == 31) {
                        $daylc = 30;
                        $banlc = 1;
                    }
                    if ($mes == '02' && intval($daylc) >= 28) {
                        $daylc = 30;
                        $feblc = 1;
                    }
                    $vallic = $valdialc * $daylc;
                    $saludlc = $vallic * 0.04;
                    $pensionlc = $vallic * 0.04;
                    if ($empresa['exonera_aportes'] == '1') {
                        $saludlcem = 0;
                    } else {
                        $saludlcem = $vallic * 0.085;
                    }
                    $pensionlcem = $vallic * 0.12;
                    $vallicen = $vallic;
                    $lic = 1;
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO nom_liq_licmp (id_licmp, id_eps, fec_inicio, fec_fin, dias_liqs, val_liq, val_dialc, mes_lic, anio_lic, fec_reg,id_nomina) 
                                VALUES (?, ?, ?, ?, ?, ?,?, ?, ?, ?,?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idlc, PDO::PARAM_INT);
                        $sql->bindParam(2, $id_eps, PDO::PARAM_INT);
                        $sql->bindParam(3, $inlic, PDO::PARAM_STR);
                        $sql->bindParam(4, $finlic, PDO::PARAM_STR);
                        $sql->bindParam(5, $daylc, PDO::PARAM_INT);
                        $sql->bindParam(6, $vallicen, PDO::PARAM_STR);
                        $sql->bindParam(7, $valdialc, PDO::PARAM_STR);
                        $sql->bindParam(8, $mes, PDO::PARAM_STR);
                        $sql->bindParam(9, $anio, PDO::PARAM_STR);
                        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(11, $id_nomina, PDO::PARAM_INT);
                        $sql->execute();
                        if ($cmd->lastInsertId() > 0) {
                        } else {
                            echo $sql->errorInfo()[2] . '4';
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
            //-------
            //liquidar vacaciones
            $valvac = 0;
            $vac = 0;
            $dayvac = 0;
            $saludvac = 0;
            $pensionvac = 0;
            $saludvacem = 0;
            $pensionvacem = 0;
            $vacacionsalario = 0;
            $primavacnsalario = 0;
            $bonrecreacionsalario = 0;
            $vacacion = 0;
            //prima de servicios
            $keybxsp = array_search($i, array_column($bonxserv, 'id_empleado'));
            if ($keybxsp !== false) {
                $bsp = $bonxserv[$keybxsp]['val_bsp'];
            } else {
                $bsp = 0;
            }
            $keyprimaserv = array_search($i, array_column($primadserv, 'id_empleado'));
            if ($keyprimaserv !== false) {
                $primservicio = $primadserv[$keyprimaserv]['val_liq_ps'];
            } else {
                $primservicio = 0;
            }
            $keyvc = array_search($i, array_column($vacaciones, 'id_empleado'));
            if ($keyvc !== false) {
                $idvac = $vacaciones[$keyvc]['id_vac'];
                $diastocalc = $vacaciones[$keyvc]['dias_liquidar'];
                $dayvac = $vacaciones[$keyvc]['dias_inactivo'];
                //modificar liquidación vacaciones. 
                $bonserpres = 0;
                //prima de vacaciones
                $primvacacion  = (($salbase + $gasrep + $auxt + $auxali + $bsp / 12 + $primservicio / 12) * 15) / 30;
                $primavacn = ($primvacacion / 360) * $diastocalc; //+
                //liquidacion vacaciones
                $liqvacacion  = (($salbase + $gasrep + $auxt + $auxali + $bsp / 12 + $primservicio / 12) * $dayvac) / 30;
                $vacacion = ($liqvacacion / 360) * $diastocalc; //=
                $bonrecrea = ($salbase / 30) * 2;
                $bonrecreacion = ($bonrecrea / 360) * $diastocalc; //+
                $vacacionsalario = $vacacion;
                $primavacnsalario = $primavacn;
                $bonrecreacionsalario = $bonrecreacion;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_vac`
                                        (`id_vac`, `dias_liqs`, `val_liq`, `val_bsp`, `val_prima_vac`, `val_bon_recrea`, `mes_vac`, `anio_vac`, `fec_reg`,`id_nomina`)
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idvac, PDO::PARAM_INT);
                    $sql->bindParam(2, $dayvac, PDO::PARAM_INT);
                    $sql->bindParam(3, $vacacionsalario, PDO::PARAM_STR);
                    $sql->bindParam(4, $bonserpres, PDO::PARAM_STR);
                    $sql->bindParam(5, $primavacn, PDO::PARAM_STR);
                    $sql->bindParam(6, $bonrecreacion, PDO::PARAM_STR);
                    $sql->bindParam(7, $mes, PDO::PARAM_STR);
                    $sql->bindParam(8, $anio, PDO::PARAM_STR);
                    $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(10, $id_nomina, PDO::PARAM_INT);
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $estado = 2;
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "UPDATE nom_vacaciones SET  estado = ? WHERE id_vac = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $estado, PDO::PARAM_INT);
                        $sql->bindParam(2, $idvac, PDO::PARAM_INT);
                        $sql->execute();
                    } else {
                        echo $sql->errorInfo()[2] . '8';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                $valvac = $vacacion;
                $base_ss = ($salbase / 30) * $dayvac;
                $saludvac = 0;
                $pensionvac = 0;
                if ($empresa['exonera_aportes'] == '1') {
                    $saludvacem = 0;
                } else {
                    $saludvacem = $base_ss * 0.125;
                }
                $pensionvacem = $base_ss * 0.16;
                $vac = 1;
            }

            //liquidar indemnizacion por vacaciones 
            $keyindem = array_search($i, array_column($indemnizaciones, 'id_empleado'));
            if ($keyindem !== false) {
                $diasindm = $indemnizaciones[$keyindem]['cant_dias'];
                $idindem = $indemnizaciones[$keyindem]['id_indemniza'];
                $valindem = ($salbase / 30) * $diasindm;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_indemniza_vac`
                                (`id_indemnizacion`, `val_liq`, `mes`, `vigencia`, `id_user_reg`, `fec_reg`,`id_nomina`)
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idindem, PDO::PARAM_INT);
                    $sql->bindParam(2, $valindem, PDO::PARAM_STR);
                    $sql->bindParam(3, $mes, PDO::PARAM_STR);
                    $sql->bindParam(4, $anio, PDO::PARAM_STR);
                    $sql->bindParam(5, $id_user, PDO::PARAM_INT);
                    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $estado = 2;
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $sql = "UPDATE `nom_indemniza_vac` SET  `estado` = ? WHERE `id_indemniza` = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $estado, PDO::PARAM_INT);
                        $sql->bindParam(2, $idindem, PDO::PARAM_INT);
                        $sql->execute();
                        if (!($sql->rowCount() > 0)) {
                            echo $sql->errorInfo()[2] . '=> indemnizacion';
                        }
                    } else {
                        echo $sql->errorInfo()[2] . '=> indemnizacion';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                $valindem = 0;
                $diasindm = 0;
            }
            //liquidar Incapacida
            $valincap = '0';
            $days = '0';
            $tot_dias_inc = 0;
            if (!empty($incapacidades)) {
                foreach ($incapacidades as $inc) {
                    $emple_inc = $inc['id_empleado'];
                    if ($emple_inc == $i) {
                        $days = $inc['can_dias'];
                        $tot_dias_inc = $tot_dias_inc + $days;
                        $idinc = $inc['id_incapacidad'];
                        $tipoinc = $inc['id_tipo']; //1 comun,  3 laboral
                        $categoria = $inc['categoria']; //1 inicial, 2 prorroga
                        $valdia = ($salbase / 30) * (2 / 3);
                        $valordia = $salbase / 30;
                        if ($categoria == 1) {
                            if ($tipoinc == 1) {
                                if ($days <= 2) {
                                    $pagoempre = $valordia * 2;
                                    $pagoeps = 0;
                                    $pagoarl = 0;
                                } else if ($days > 2) {
                                    $pagoempre = $valordia * 2;
                                    $pagoeps = $valdia * ($days - 2);
                                    $pagoarl = 0;
                                }
                            } else if ($tipoinc == 3) {
                                $pagoempre = 0;
                                $pagoeps = 0;
                                $pagoarl = $valordia * $days;
                            }
                        } else if ($categoria == 2) {
                            if ($tipoinc == 1) {
                                $pagoempre = 0;
                                $pagoeps = $valdia * $days;
                                $pagoarl = 0;
                            } else if ($tipoinc == 3) {
                                $pagoempre = 0;
                                $pagoeps = 0;
                                $pagoarl = $valordia * $days;
                            }
                        }
                        $valincap = $pagoempre + $pagoeps + $pagoarl + $valincap;
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO nom_liq_incap (id_incapacidad, id_eps, id_arl, dias_liq, pago_empresa, pago_eps, pago_arl, mes, anios, fec_reg, id_nomina) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idinc, PDO::PARAM_INT);
                            $sql->bindParam(2, $id_eps, PDO::PARAM_INT);
                            $sql->bindParam(3, $id_arl, PDO::PARAM_INT);
                            $sql->bindParam(4, $days, PDO::PARAM_STR);
                            $sql->bindParam(5, $pagoempre, PDO::PARAM_STR);
                            $sql->bindParam(6, $pagoeps, PDO::PARAM_STR);
                            $sql->bindParam(7, $pagoarl, PDO::PARAM_STR);
                            $sql->bindParam(8, $mes, PDO::PARAM_STR);
                            $sql->bindParam(9, $anio, PDO::PARAM_STR);
                            $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(11, $id_nomina, PDO::PARAM_INT);
                            $sql->execute();
                            if ($cmd->lastInsertId() > 0) {
                            } else {
                                echo $sql->errorInfo()[2] . '9';
                            }
                            $cmd = null;
                        } catch (Exception $ex) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
            }
            //liquidación dias laborados
            $diatovaca = $diaslab + $days + $daylc + $dayvac + $daylcluto - $daylcnr;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_dias_lab (id_empleado, cant_dias, mes, anio, fec_reg,id_nomina) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                $sql->bindParam(2, $diatovaca, PDO::PARAM_INT);
                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . '10';
                }
                $cmd = null;
            } catch (Exception $ex) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $val_real_inc = $valincap;
            if ($tot_dias_inc > 0 && $salbase == $smmlv) {
                $valincap = ($salbase / 30) * $tot_dias_inc;
            }
            $devtotal = $devhe + $valincap + (($salbase / 30) * $diaslab) + $gasrep + $bsp_salarial + $vallcluto;
            if ($sal_integ == 1) {
                $pensolid = (($salbase / 30) * $diaslab);
            } else {
                $pensolid = $devtotal + $vallic + $valvac;
            }
            //liquidar 
            if ($sal_integ == 1) {
                $liqpfisc = (($salbase / 30) * $diaslab) * 0.7;
            } else {
                $liqpfisc = (($salbase / 30) * $diaslab) + $bsp_salarial + $devhe + $vacacionsalario + $valincap + $vallic + $vallcluto + $valindem + $gasrep;
            }
            if ($empresa['exonera_aportes'] == '1') {
                $sena = 0;
                $icbf = 0;
                $comfam = ($liqpfisc) * 0.04;
            } else {
                $sena = ($liqpfisc) * 0.02;
                $icbf = ($liqpfisc) * 0.03;
                $comfam = ($liqpfisc) * 0.04;
            }
            if ($tipo_emp == 12 || $tipo_emp == 8) {
                $sena = 0;
                $icbf = 0;
                $comfam = 0;
            }
            $sena = redondeo($sena, -2);
            $icbf = redondeo($icbf, -2);
            $comfam = redondeo($comfam, -2);
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_parafiscales (id_empleado, val_sena, val_icbf, val_comfam, mes_pfis, anio_pfis, fec_reg,id_nomina) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $i, PDO::PARAM_INT);
                $sql->bindParam(2, $sena, PDO::PARAM_STR);
                $sql->bindParam(3, $icbf, PDO::PARAM_STR);
                $sql->bindParam(4, $comfam, PDO::PARAM_STR);
                $sql->bindParam(5, $mes, PDO::PARAM_STR);
                $sql->bindParam(6, $anio, PDO::PARAM_STR);
                $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . '11';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //liquida seguridad social
            if ($pensolid < $smmlv * 4) {
                $solidpension = 0;
                $porcenps = 0;
            } else if ($pensolid >= $smmlv * 4  && $pensolid < $smmlv * 16) {
                $solidpension = $pensolid * 0.01;
                $porcenps = 1;
            } else if ($pensolid >= $smmlv * 16  && $pensolid < $smmlv * 17) {
                $solidpension = $pensolid * 0.012;
                $porcenps = 1.2;
            } else if ($pensolid >= $smmlv * 17  && $pensolid < $smmlv * 18) {
                $solidpension = $pensolid * 0.014;
                $porcenps = 1.4;
            } else if ($pensolid >= $smmlv * 18  && $pensolid < $smmlv * 19) {
                $solidpension = $pensolid * 0.016;
                $porcenps = 1.6;
            } else if ($pensolid >= $smmlv * 19  && $pensolid < $smmlv * 20) {
                $solidpension = $pensolid * 0.018;
                $porcenps = 1.8;
            } else if ($pensolid >= $smmlv * 20) {
                $solidpension = $pensolid * 0.02;
                $porcenps = 2;
            }
            if ($sal_integ == 1) {
                $saludempleado = ((($salbase / 30) * $diaslab) * 0.7) * 0.04;
            } else {
                $saludempleado = $devtotal * 0.04 + $saludlc + $saludvac;
            }
            if ($sal_integ == 1) {
                $pensionempleado = ((($salbase / 30) * $diaslab) * 0.7) * 0.04;
            } else {
                $pensionempleado = $devtotal * 0.04 + $pensionlc + $pensionvac;
            }
            if ($empresa['exonera_aportes'] == '1') {
                $saludempresa = 0;
            } else {
                if ($sal_integ == 1) {
                    $saludempresa = ((($salbase / 30) * $diaslab) * 0.7) * 0.085;
                } else {
                    $saludempresa = $devtotal * 0.085 + $saludlcem + $saludvacem + $saludlicnrpatronal;
                }
            }
            if ($sal_integ == 1) {
                $pensionempresa = ((($salbase / 30) * $diaslab) * 0.7) * 0.12;
            } else {
                $pensionempresa = $devtotal * 0.12 + $pensionlcem + $pensionvacem + $pensionlicnrpatronal;
            }
            if ($sal_integ == 1) {
                $ibc = (($salbase / 30) * $diaslab) * 0.7;
            } else {
                $ibc = (($salbase / 30) * $diaslab) + $devhe + $bsp_salarial + $gasrep;
            }
            $rieslab = $ibc *  $cot_rlab;
            if ($tipo_emp == 12) {
                $saludempleado = 0;
                $pensionempleado = 0;
                $solidpension = 0;
                $porcenps = 0;
                $saludempresa = 0;
                $pensionempresa = 0;
            }
            if ($tipo_emp == 8) {
                $saludempleado = 0;
                $pensionempleado = 0;
                $solidpension = 0;
                $porcenps = 0;
                $saludempresa = (($salbase / 30) * $diaslab) * 0.125;
                $pensionempresa = 0;
                $salbase = $salbase * 0.75;
            }
            //cambio
            if ($subtip_emp == 2) {
                $pensionempleado = 0;
                $solidpension = 0;
                $porcenps = 0;
                $pensionempresa = 0;
            }
            $semp = redondeo($saludempleado, 0);
            $pemp = redondeo($pensionempleado, 0);
            $solidpension = redondeo($solidpension, -2);
            $stotal = redondeo($saludempresa + $saludempleado, -2);
            $ptotal = redondeo($pensionempresa + $pensionempleado, -2);
            $rieslab = redondeo($rieslab, -2);
            $saludempleado = $semp;
            $pensionempleado = $pemp;
            $saludempresa = $stotal - $semp;
            $pensionempresa = $ptotal - $pemp;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_segsocial_empdo (id_empleado, id_eps, id_arl, id_afp, aporte_salud_emp, aporte_pension_emp, aporte_solidaridad_pensional, porcentaje_ps, aporte_salud_empresa, aporte_pension_empresa, aporte_rieslab, mes, anio, fec_reg, id_nomina)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $i, PDO::PARAM_INT);
                $sql->bindParam(2, $id_eps, PDO::PARAM_INT);
                $sql->bindParam(3, $id_arl, PDO::PARAM_INT);
                $sql->bindParam(4, $id_afp, PDO::PARAM_INT);
                $sql->bindParam(5, $saludempleado, PDO::PARAM_STR);
                $sql->bindParam(6, $pensionempleado, PDO::PARAM_STR);
                $sql->bindParam(7, $solidpension, PDO::PARAM_STR);
                $sql->bindParam(8, $porcenps, PDO::PARAM_STR);
                $sql->bindParam(9, $saludempresa, PDO::PARAM_STR);
                $sql->bindParam(10, $pensionempresa, PDO::PARAM_STR);
                $sql->bindParam(11, $rieslab, PDO::PARAM_STR);
                $sql->bindParam(12, $mes, PDO::PARAM_STR);
                $sql->bindParam(13, $anio, PDO::PARAM_STR);
                $sql->bindValue(14, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(15, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . '12';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //Liaquidar auxilio de transporte y dias laborados
            $valdiaslab = $diaslab * ($salbase / 30);
            $vallaborado = $valdiaslab + $vallcluto;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_dlab_auxt (id_empleado, dias_liq, val_liq_dias, val_liq_auxt,aux_alim,g_representa,horas_ext, mes_liq, anio_liq, fec_reg, id_nomina) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                $sql->bindParam(2, $diaslab, PDO::PARAM_INT);
                $sql->bindParam(3, $vallaborado, PDO::PARAM_STR);
                $sql->bindParam(4, $auxt, PDO::PARAM_STR);
                $sql->bindParam(5, $auxali, PDO::PARAM_STR);
                $sql->bindParam(6, $gasrep, PDO::PARAM_STR);
                $sql->bindParam(7, $devhe, PDO::PARAM_STR);
                $sql->bindParam(8, $mes, PDO::PARAM_STR);
                $sql->bindParam(9, $anio, PDO::PARAM_STR);
                $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(11, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . '13';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //liquidar prestaciones sociales
            $diastocesantias = 30 - $daylcnr;
            if ($sal_integ == 1) {
                $vacacion = ((($salbase / 30) * $diastocesantias) * 0.7) * $diatovaca / 720;
                $cesantia = 0;
                $icesant = 0;
                $prima = 0;
            } else {
                $key = array_search($empleado, array_column($lastpay, 'id_empleado'));
                if ($key !== false) {
                    $ant_bsp = $lastpay[$key]['val_bsp'];
                    $ant_prima_servicio = $lastpay[$key]['val_prima_servicio'];
                    $ant_prima_navidad = $lastpay[$key]['val_prima_navidad'];
                    $ant_vacaciones =  $lastpay[$key]['val_vacaciones'];
                    $ant_prima_vacaciones = $lastpay[$key]['val_prima_vacaciones'];
                    $ant_bon_recreacion = $lastpay[$key]['val_bon_recreacion'];
                } else {
                    $ant_bsp = 0;
                    $ant_prima_servicio = 0;
                    $ant_prima_navidad = 0;
                    $ant_vacaciones =  0;
                    $ant_prima_vacaciones = 0;
                    $ant_bon_recreacion = 0;
                }
                //prima de vacaciones
                $prima_sv_dia = ($salbase + $auxt + $auxali + $ant_bsp / 12) / 720;
                $prima = $prima_sv_dia * $diastocesantias; //=

                $primvacacion  = (($salbase +  $gasrep + $auxt + $auxali + $ant_bsp / 12 + $ant_prima_servicio / 12) * 15) / 30;
                $privacmes = ($primvacacion / 360) * $diastocesantias; //+
                //liquidacion vacaciones
                $liqvacacion  = (($salbase  + $gasrep + $auxt + $auxali + $ant_bsp / 12 + $ant_prima_servicio / 12) * 22) / 30;
                $vacacion = ($liqvacacion / 360) * $diastocesantias; //=
                //prima de navidad
                $primanavidad = $salbase +  $gasrep + $auxt + $auxali + ($ant_bsp / 12) + ($ant_prima_servicio / 12) + ($ant_prima_vacaciones / 12);
                $prinavmes = ($primanavidad / 360) * $diastocesantias; //+
                //Bonificacion de recreacion
                $bonrecrea = ($salbase / 30) * 2;
                $bonrecmes = ($bonrecrea / 360) * $diastocesantias; //+
                //cesantia e intereses  cesantia
                $censantias = $salbase + $gasrep +  $auxt + $auxali + $ant_bsp / 12 + $ant_prima_servicio / 12 + $ant_prima_vacaciones / 12 + $ant_prima_navidad / 12;
                $cesantia = ($censantias / 360) * $diastocesantias; //=
                $icesant = $cesantia * 0.12;
            }
            if ($tipo_emp == 12 || $tipo_emp == 8) {
                $vacacion = 0;
                $cesantia = 0;
                $icesant = 0;
                $prima = 0;
                $privacmes = 0;
                $prinavmes = 0;
                $bonrecmes = 0;
            }
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_prestaciones_sociales` (`id_empleado`, `val_vacacion`, `val_cesantia`, `val_interes_cesantia`
                                                                    , `val_prima`, `val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`
                                                                    , `mes_prestaciones`, `anio_prestaciones`, `fec_reg`, `id_nomina`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $empleado, PDO::PARAM_INT);
                $sql->bindParam(2, $vacacion, PDO::PARAM_STR);
                $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
                $sql->bindParam(4, $icesant, PDO::PARAM_STR);
                $sql->bindParam(5, $prima, PDO::PARAM_STR);
                $sql->bindParam(6, $privacmes, PDO::PARAM_STR);
                $sql->bindParam(7, $prinavmes, PDO::PARAM_STR);
                $sql->bindParam(8, $bonrecmes, PDO::PARAM_STR);
                $sql->bindParam(9, $mes, PDO::PARAM_STR);
                $sql->bindParam(10, $anio, PDO::PARAM_STR);
                $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(12, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . '14';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //liquidar Libranzas
            if (true) {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "SELECT *
                                FROM nom_libranzas
                                WHERE estado = '1' AND id_empleado = '$empleado'";
                    $rs = $cmd->query($sql);
                    $libranzas = $rs->fetchAll();
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            $valincap = $val_real_inc;
            $base_descuentos = $devhe + (($salbase / 30) * $diaslab) + $auxt + $auxali + $vallic + $vallcluto + $valincap + $bsp_salarial + $vacacionsalario + $primavacnsalario + $bonrecreacionsalario + $gasrep + $valindem;
            //liquidar Embargos
            if (true) {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "SELECT * 
                                FROM nom_embargos
                                WHERE id_empleado = '$empleado' AND estado = '1'";
                    $rs = $cmd->query($sql);
                    $tienembg = $rs->fetchAll();
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            $dctoemb = 0;
            $descEmbargo = 0;
            if (!empty($tienembg)) {
                foreach ($tienembg as $te) {
                    $dctoemb = $te['valor_mes'];
                    if ($base_descuentos > $dctoemb && $base_descuentos > $smmlv) {
                        $id_embargo = $te['id_embargo'];
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO nom_liq_embargo (id_embargo, val_mes_embargo, mes_embargo, anio_embargo, fec_reg, id_nomina) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $id_embargo, PDO::PARAM_INT);
                            $sql->bindParam(2, $dctoemb, PDO::PARAM_STR);
                            $sql->bindParam(3, $mes, PDO::PARAM_STR);
                            $sql->bindParam(4, $anio, PDO::PARAM_STR);
                            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                            $sql->execute();
                            $base_descuentos -= $dctoemb;
                            $descEmbargo += $dctoemb;
                            $cmd = null;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                    }
                }
            }
            //liquidar cuota sindical
            $key = array_search($i, array_column($porcuotasind, 'id_empleado'));
            if ($key !== false && $daylcnr < 30) {
                $idcuotsind = $porcuotasind[$key]['id_cuota_sindical'];
            } else {
                $idcuotsind = 0;
            }
            if ($idcuotsind > 0) {
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "SELECT
                                `id_cuota_sindical`, `val_sidicalizacion`, `estado`, `val_fijo`, `porcentaje_cuota`
                            FROM
                                `nom_cuota_sindical`
                            WHERE `id_cuota_sindical` = '$idcuotsind'";
                    $rs = $cmd->query($sql);
                    $status_sind = $rs->fetch();
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //$valcuotsind = $devtotal * $_POST['txtPorcCuotaSind_' . $i];
                $porcsind = $status_sind['porcentaje_cuota'] > 0 ? $status_sind['porcentaje_cuota'] : 0;
                $valcuotsind = redondeoSind($salbase * $porcsind / 100, -2);
                if ($status_sind['estado'] == 1) {
                    $valcuotsind = $valcuotsind + $status_sind['val_sidicalizacion'];
                    $estado = 2;
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "UPDATE `nom_cuota_sindical` SET `estado` = ?, `fec_act` = ? WHERE `id_cuota_sindical` = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $estado, PDO::PARAM_STR);
                        $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(3, $idcuotsind, PDO::PARAM_INT);
                        $sql->execute();
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            } else {
                $idcuotsind = 0;
                $valcuotsind = 0;
            }
            if ($base_descuentos > $valcuotsind && $base_descuentos > $smmlv) {
                if ($idcuotsind != 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO nom_liq_sindicato_aportes (id_cuota_sindical, val_aporte, mes_aporte, anio_aporte, fec_reg, id_nomina) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $idcuotsind, PDO::PARAM_INT);
                        $sql->bindParam(2, $valcuotsind, PDO::PARAM_STR);
                        $sql->bindParam(3, $mes, PDO::PARAM_STR);
                        $sql->bindParam(4, $anio, PDO::PARAM_STR);
                        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                        $sql->execute();
                        $base_descuentos -= $valcuotsind;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            } else {
                $valcuotsind = 0;
            }
            $dctolib = 0;
            if (!empty($libranzas)) {
                foreach ($libranzas as $libranza) {
                    $idlib = $libranza['id_libranza'];
                    $abonolib = $libranza['val_mes'];
                    if ($base_descuentos > $abonolib && $base_descuentos > $smmlv) {
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO nom_liq_libranza (id_libranza, val_mes_lib, mes_lib, anio_lib, fec_reg, id_nomina) 
                                VALUES (?, ?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idlib, PDO::PARAM_INT);
                            $sql->bindParam(2, $abonolib, PDO::PARAM_STR);
                            $sql->bindParam(3, $mes, PDO::PARAM_STR);
                            $sql->bindParam(4, $anio, PDO::PARAM_STR);
                            $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                            $sql->execute();
                            $base_descuentos -= $abonolib;
                            $cmd = null;
                        } catch (PDOException $e) {
                            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                        }
                        $dctolib += $abonolib;
                    }
                }
            }
            //Retencion en la fuente.
            //pago por dependiente es para llenar la tabla y hacer la depuracion del valor para retencion en la fuentene (Bioclinico)
            $pagoxdependiente = 0;
            $keyrf = array_search($i, array_column($pagoxdpte, 'id_empleado'));

            if (false !== $keyrf) {
                $pagoxdependiente = ($valdiaslab + $bsp_salarial + $devhe + $vacacionsalario + $primavacnsalario + $bonrecreacionsalario + $gasrep) * 0.1;
                $maxpagoxdependiente = 32 * $uvt;
                if ($pagoxdependiente > $maxpagoxdependiente) {
                    $pagoxdependiente = $maxpagoxdependiente;
                }
            }
            $valrf = $valdiaslab + $bsp_salarial + $devhe + $vacacionsalario + $primavacnsalario + $bonrecreacionsalario + $gasrep + $valindem + $vallcluto - $saludempleado - $pensionempleado - $solidpension - $pagoxdependiente;
            $valdpurado =  $valrf - ($valrf * 0.25);
            if ($sal_integ == 1) {
                $inglabuvt = ((($salbase / 30) * $diaslab) * 0.75) / $uvt;
            } else {
                $inglabuvt = $valdpurado / $uvt;
            }
            if ($inglabuvt < 95) {
                $retencion = 0;
            } else if ($inglabuvt >= 95 && $inglabuvt < 150) {
                $uvtx = $inglabuvt - 95;
                $retencion = $uvt * $uvtx * 0.19;
            } else if ($inglabuvt >= 150 && $inglabuvt < 360) {
                $uvtx = $inglabuvt - 150;
                $retencion = ($uvt * $uvtx * 0.28) + (10 * $uvt);
            } else if ($inglabuvt >= 360 && $inglabuvt < 640) {
                $uvtx = $inglabuvt - 360;
                $retencion = ($uvt * $uvtx * 0.33) + (69 * $uvt);
            } else if ($inglabuvt >= 640 && $inglabuvt < 945) {
                $uvtx = $inglabuvt - 640;
                $retencion = ($uvt * $uvtx * 0.35) +  (162 * $uvt);
            } else if ($inglabuvt >= 945 && $inglabuvt < 2300) {
                $uvtx = $inglabuvt - 945;
                $retencion = ($uvt * $uvtx * 0.37) + (268 * $uvt);
            } else if ($inglabuvt >= 2300) {
                $uvtx = $inglabuvt - 2300;
                $retencion = ($uvt * $uvtx * 0.39) + (770 * $uvt);
            }
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_retencion_fte (id_empleado, val_ret, mes, anio, id_user_reg, fec_reg, id_nomina,base) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $i, PDO::PARAM_INT);
                $sql->bindParam(2, $retencion, PDO::PARAM_STR);
                $sql->bindParam(3, $mes, PDO::PARAM_STR);
                $sql->bindParam(4, $anio, PDO::PARAM_STR);
                $sql->bindParam(5, $id_user, PDO::PARAM_INT);
                $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(8, $valdpurado, PDO::PARAM_STR);
                $sql->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //Descuentos
            $otros_dctos = 0;
            $filtro = [];
            $key_dcto = array_search($i, array_column($descuentos, 'id_empleado'));
            if (false !== $key_dcto) {
                $filtro = array_filter($descuentos, function ($var) use ($i) {
                    return ($var['id_empleado'] == $i);
                });
            }
            if (!empty($filtro)) {
                foreach ($filtro as $dcto) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `nom_liq_descuento`
                                    (`id_dcto`,`valor`,`id_nomina`,`id_user_reg`,`fec_reg`)
                                VALUES (?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $dcto['id_dcto'], PDO::PARAM_INT);
                        $sql->bindParam(2, $dcto['valor'], PDO::PARAM_STR);
                        $sql->bindParam(3, $id_nomina, PDO::PARAM_INT);
                        $sql->bindParam(4, $id_user, PDO::PARAM_INT);
                        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . 'DCTO';
                        } else {
                            $otros_dctos += $dcto['valor'];
                        }
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
            //neto a pagar
            $salarioneto = $devhe + (($salbase / 30) * $diaslab) + $auxt + $auxali + $vallic + $vallcluto + $valincap + $bsp_salarial + $vacacionsalario + $primavacnsalario + $bonrecreacionsalario + $gasrep + $valindem - $saludempleado - $pensionempleado - $solidpension - $valcuotsind - $descEmbargo - $dctolib - $retencion - $otros_dctos;
            $salarioneto = $salarioneto < 0 ? 0 : $salarioneto;
            $fpag = '1';
            $mpag = $_POST['slcMetPag' . $i];
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO nom_liq_salario (id_empleado, val_liq, forma_pago, metodo_pago, mes, anio, fec_reg, id_nomina) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $i, PDO::PARAM_INT);
                $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
                $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                $sql->bindParam(5, $mes, PDO::PARAM_STR);
                $sql->bindParam(6, $anio, PDO::PARAM_STR);
                $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $key = array_search($i, array_column($emple, 'id_empleado'));
            if (false !== $key) {
                $cc = $emple[$key]['no_documento'];
                $nomempleado = $emple[$key]['nombre'];
            }
            $er .= '<tr class="text-left">'
                . '<td>' . $cc . '</td>'
                . '<td>' . mb_strtoupper($nomempleado) . '</td>'
                . '<td class="text-center"><i class="fas fa-check-circle text-success"></i></td>'
                . '</tr>';
            $mesliq++;
        }
    }
    $er .= '</tbody>
    </table>';
    if ($mesliq == 0) {
        echo '0';
    } else {
        echo $er;
    }
} else {
    echo 'No se selecionó ningún empleado';
}
function redondeoSind($numero)
{
    $residuo = $numero % 100;

    if ($residuo < 50) {
        return $numero - $residuo;
    } else {
        return $numero + (100 - $residuo);
    }
}
