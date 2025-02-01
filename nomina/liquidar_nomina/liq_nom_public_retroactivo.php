<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../conexion.php';
include '../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$ids = isset($_POST['id_empleado']) ? $_POST['id_empleado'] : exit('Acción no permitida');
$ids = implode(',', $ids);
$vigencia = $_SESSION['vigencia'];
$id_user = $_SESSION['id_user'];
$diasxempleado = [];
$id_retroactivo = $_POST['id_retroactivo'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_retroactivo`, `fec_inicio`, `fec_final`, `meses`, `porcentaje`, `id_incremento`, `observaciones`, `nom_retroactivos`.`vigencia`,`nom_retroactivos`.`estado`
            FROM
            `nom_retroactivos`
            INNER JOIN `nom_incremento_salario` 
                ON (`nom_retroactivos`.`id_incremento` = `nom_incremento_salario`.`id_inc`)
            WHERE `id_retroactivo` = '$id_retroactivo'";
    $rs = $cmd->query($sql);
    $retroactivo = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_incremento = $retroactivo['id_incremento'];
$fecIni = $retroactivo['fec_inicio'];
$fecFin = $retroactivo['fec_final'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` 
            FROM 
                (SELECT 
                    `id_nomina`,DATE_FORMAT(CONCAT_WS('-', `vigencia`,`mes`,'01'),'%Y-%m-%d') AS `fecha`
                FROM `nom_nominas` 
                WHERE `tipo` = 'N' AND `id_nomina` <> 0) AS `t1`
            WHERE `fecha` BETWEEN  '$fecIni' AND '$fecFin'";
    $rs = $cmd->query($sql);
    $ids_nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ids_nominas = !empty($ids_nominas) ? implode(',', array_column($ids_nominas, 'id_nomina')) : -1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` 
            FROM 
                (SELECT 
                    `id_nomina`,DATE_FORMAT(CONCAT_WS('-', `vigencia`,`mes`,'01'),'%Y-%m-%d') AS `fecha`
                FROM `nom_nominas` 
                WHERE `id_nomina` <> 0) AS `t1`
            WHERE `fecha` BETWEEN  '$fecIni' AND '$fecFin'";
    $rs = $cmd->query($sql);
    $idnomvac = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$idnomvacs = !empty($idnomvac) ? implode(',', array_column($idnomvac, 'id_nomina')) : -1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `fec_retiro`, `fech_inicio`, `tipo_empleado`
            FROM
                `nom_empleado`
            WHERE `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT  `id_empleado`, SUM(`cant_dias`) AS `total_dlab`
            FROM `nom_liq_dias_lab`
            WHERE `id_nomina` IN ($ids_nominas) 
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $diaslaborados = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
                    WHERE ((`nom_nominas`.`tipo` = 'N' OR `nom_nominas`.`tipo` = 'PS') AND `nom_nominas`.`vigencia` <= '$vigencia')
                    GROUP BY `nom_liq_bsp`.`id_empleado`
                    UNION ALL
                    SELECT
                        MAX(`nom_liq_bsp`.`id_bonificaciones`) AS `id_bonificaciones`
                    FROM
                        `nom_liq_bsp`
                    INNER JOIN `nom_nominas` 
                        ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                    WHERE (`nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$vigencia')
                    GROUP BY `nom_liq_bsp`.`id_empleado`)
                GROUP BY `id_empleado`) AS `t1`
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
                    WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$vigencia'
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
                    WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$vigencia'
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

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`
                , `dias_incap` + `dias_licr` AS `dias`
            FROM 
                (SELECT 
                    `nom_empleado`.`id_empleado`
                    , IFNULL(`dias_incap`,0) AS `dias_incap`
                    , IFNULL(`dias_licnr`,0) AS `dias_licnr`
                    , IFNULL(`dias_licr`,0) AS `dias_licr`
                    , IFNULL(`dias_vac`,0) AS `dias_vac`
                FROM `nom_empleado`
                    LEFT JOIN 
                    (SELECT
                        `nom_incapacidad`.`id_empleado`
                        , SUM(`nom_liq_incap`.`dias_liq`) AS `dias_incap`
                    FROM
                        `nom_liq_incap`
                        INNER JOIN `nom_incapacidad` 
                        ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
                    WHERE (`nom_liq_incap`.`id_nomina` IN($ids_nominas) AND `id_empleado` IN ($ids))
                    GROUP BY `nom_incapacidad`.`id_empleado`) AS `t1`
                        ON (`nom_empleado`.`id_empleado` = `t1`.`id_empleado`)
                    LEFT JOIN
                    (SELECT
                        `nom_licenciasnr`.`id_empleado`
                        , SUM(`nom_liq_licnr`.`dias_licnr`) AS `dias_licnr`
                    FROM
                        `nom_liq_licnr`
                        INNER JOIN `nom_licenciasnr` 
                        ON (`nom_liq_licnr`.`id_licnr` = `nom_licenciasnr`.`id_licnr`)
                        INNER JOIN `nom_nominas` 
                        ON (`nom_liq_licnr`.`id_nomina` = `nom_nominas`.`id_nomina`)
                    WHERE (`nom_liq_licnr`.`id_nomina` IN ($ids_nominas) AND `id_empleado` IN ($ids))
                    GROUP BY `nom_licenciasnr`.`id_empleado`) AS `t2`
                        ON (`nom_empleado`.`id_empleado` = `t2`.`id_empleado`)
                    LEFT JOIN
                    (SELECT
                        `nom_licenciasmp`.`id_empleado`
                        , SUM(`nom_liq_licmp`.`dias_liqs`) AS `dias_licr`
                    FROM
                        `nom_liq_licmp`
                        INNER JOIN `nom_licenciasmp` 
                        ON (`nom_liq_licmp`.`id_licmp` = `nom_licenciasmp`.`id_licmp`)
                        INNER JOIN `nom_nominas` 
                        ON (`nom_liq_licmp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                    WHERE (`nom_liq_licmp`.`id_nomina` IN ($ids_nominas) AND `id_empleado` IN ($ids))
                    GROUP BY `nom_licenciasmp`.`id_empleado`) AS `t3`
                        ON (`nom_empleado`.`id_empleado` = `t3`.`id_empleado`)
                    LEFT JOIN 
                    (SELECT
                        `nom_vacaciones`.`id_empleado`
                        , SUM(`nom_liq_vac`.`dias_liqs`) AS `dias_vac`
                    FROM
                        `nom_liq_vac`
                        INNER JOIN `nom_vacaciones` 
                        ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                    WHERE (`nom_liq_vac`.`id_nomina` IN ($idnomvacs) AND `id_empleado` IN ($ids))
                    GROUP BY `nom_vacaciones`.`id_empleado`) AS `t4`
                        ON (`nom_empleado`.`id_empleado` = `t4`.`id_empleado`)
                WHERE `nom_empleado`.`id_empleado` IN ($ids)) AS `t5`";
    $rs = $cmd->query($sql);
    $dias_dcto = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `codigo`, `fin_mes`
            FROM
                `nom_meses`";
    $rs = $cmd->query($sql);
    $meses = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `anio`, `id_concepto`, `valor`
            FROM
                `nom_valxvigencia`
            INNER JOIN `tb_vigencias` 
                ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE `anio` = '$vigencia'";
    $rs = $cmd->query($sql);
    $val_vig = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_salario`,`id_empleado`, `salario_basico`  
            FROM
            `nom_salarios_basico`
            WHERE `id_salario` 
                IN (SELECT MAX(`id_salario`) AS `id_salario` FROM `nom_salarios_basico` GROUP BY `id_empleado`)
            AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $salario = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_horas_ex_trab`.`id_empleado`
                , `nom_horas_ex_trab`.`id_he_trab`
                , `nom_horas_ex_trab`.`id_he`
                , `nom_horas_ex_trab`.`cantidad_he`
                , `nom_tipo_horaex`.`codigo`
                , `nom_tipo_horaex`.`factor`
            FROM
                `nom_liq_horex`
                INNER JOIN `nom_horas_ex_trab` 
                    ON (`nom_liq_horex`.`id_he_lab` = `nom_horas_ex_trab`.`id_he_trab`)
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_horex`.`id_nomina` = `nom_nominas`.`id_nomina`)
                INNER JOIN `nom_tipo_horaex`
                    ON (`nom_horas_ex_trab`.`id_he` = `nom_tipo_horaex`.`id_he`)
            WHERE (`nom_horas_ex_trab`.`fec_inicio` BETWEEN '$fecIni' AND '$fecFin' AND `nom_liq_horex`.`id_nomina` IN($ids_nominas) AND `id_empleado` IN ($ids))";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                IN(SELECT MAX(`id_novarl`) AS `id_novarl` FROM `nom_novedades_arl` WHERE SUBSTRING(`fec_afiliacion`, 1, 4)<= '$vigencia' GROUP BY `id_empleado`)
                AND `nom_novedades_arl`.`id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $riesgos = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE `id_novedad`  IN (SELECT MAX(`id_novedad`) FROM `nom_novedades_eps` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $eps = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE `id_novarl`  IN (SELECT MAX(`id_novarl`) FROM `nom_novedades_arl` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE `id_novafp`  IN (SELECT MAX(`id_novafp`) FROM `nom_novedades_afp` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `val_pagoxdep` FROM `nom_pago_dependiente` WHERE `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $pagoxdpte = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`
                ,SUM(`val_liq_auxt`) AS `val_auxtran`
                ,SUM(`aux_alim`) AS `val_auxalim`  
            FROM 
                `nom_liq_dlab_auxt` 
            WHERE `id_nomina` IN ($ids_nominas) 
            GROUP BY  `id_empleado`";
    $rs = $cmd->query($sql);
    $liq_auxs = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_empleado`, `val_bsp` FROM `nom_liq_bsp` WHERE `id_nomina` IN ($idnomvacs)";
    $rs = $cmd->query($sql);
    $liq_bsp = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_vacaciones`.`id_empleado`
                , `nom_vacaciones`.`dias_inactivo`
                , `nom_vacaciones`.`dias_habiles`
                , `nom_vacaciones`.`dias_liquidar`
                , `nom_liq_vac`.`val_liq`
                , `nom_liq_vac`.`val_prima_vac`
                , `nom_liq_vac`.`val_bon_recrea`
                , `nom_liq_vac`.`id_nomina`
                , `nom_vacaciones`.`id_vac`
                , `nom_liq_vac`.`dias_liqs`
            FROM
                `nom_liq_vac`
                INNER JOIN `nom_vacaciones` 
                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
            WHERE `nom_liq_vac`.`id_nomina` IN ($idnomvacs)";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_incapacidad`.`id_empleado`
                , `nom_incapacidad`.`id_incapacidad`
                , `nom_liq_incap`.`id_eps`
                , `nom_liq_incap`.`id_arl`
                , `nom_liq_incap`.`dias_liq`
                , `nom_liq_incap`.`pago_empresa`
                , `nom_liq_incap`.`pago_eps`
                , `nom_liq_incap`.`pago_arl`
                , `nom_liq_incap`.`id_nomina`
            FROM
                `nom_liq_incap`
                INNER JOIN `nom_incapacidad` 
                    ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
            WHERE (`nom_liq_incap`.`id_nomina` IN ($ids_nominas))";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_indemniza_vac`.`id_empleado`
                , `nom_indemniza_vac`.`id_indemniza`
                , `nom_indemniza_vac`.`cant_dias`
                , `nom_liq_indemniza_vac`.`val_liq`
                , `nom_liq_indemniza_vac`.`id_nomina`
            FROM
                `nom_liq_indemniza_vac`
                INNER JOIN `nom_indemniza_vac` 
                    ON (`nom_liq_indemniza_vac`.`id_indemnizacion` = `nom_indemniza_vac`.`id_indemniza`)
            WHERE (`nom_liq_indemniza_vac`.`id_nomina` IN ($ids_nominas))";
    $rs = $cmd->query($sql);
    $indemnizavac = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`
                , SUM(`cant_dias`) AS `dias`
                , SUM(`val_liq_ps`) AS `valor`
            FROM `nom_liq_prima`
            WHERE (`id_nomina` IN ($idnomvacs) AND `id_empleado`  IN ($ids))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $primas_servicio = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_liq_privac`
                , `id_empleado`
                , `cant_dias`
                , `val_liq_pv`
                , `id_nomina`
            FROM
                `nom_liq_prima_nav`
            WHERE (`id_liq_privac` IN (SELECT MAX(`id_liq_privac`) FROM `nom_liq_prima_nav` WHERE (`id_nomina` IN ($idnomvacs) AND `id_empleado` IN ($ids))
            GROUP BY `id_empleado`))";
    $rs = $cmd->query($sql);
    $primas_navidad = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_liq_cesan`
                , `id_empleado`
                , `cant_dias`
                , `val_cesantias`
                , `val_icesantias`
                , `id_nomina`
            FROM
                `nom_liq_cesantias`
            WHERE (`id_liq_cesan` IN (SELECT MAX(`id_liq_cesan`) FROM `nom_liq_cesantias` WHERE (`id_nomina` IN ($idnomvacs) AND `id_empleado` IN ($ids))
            GROUP BY `id_empleado`))";
    $rs = $cmd->query($sql);
    $vals_censatias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_licenciasmp`.`id_licmp`
                , `nom_licenciasmp`.`id_empleado`
                , `nom_liq_licmp`.`id_eps`
                , `nom_liq_licmp`.`dias_liqs`
                , `nom_liq_licmp`.`val_liq`
                , `nom_liq_licmp`.`id_nomina`
            FROM
                `nom_liq_licmp`
                INNER JOIN `nom_licenciasmp` 
                    ON (`nom_liq_licmp`.`id_licmp` = `nom_licenciasmp`.`id_licmp`)
            WHERE (`nom_liq_licmp`.`id_nomina` IN ($ids_nominas))";
    $rs = $cmd->query($sql);
    $licencias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` FROM `nom_nominas` WHERE `id_incremento` = $id_retroactivo AND `tipo` = 'RA' LIMIT 1";
    $rs = $cmd->query($sql);
    $nomina_anterior = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`,`aporte_salud_emp`,`aporte_pension_emp`,`aporte_solidaridad_pensional`,`aporte_salud_empresa`,`aporte_pension_empresa`,`aporte_rieslab`
            FROM 
                `nom_liq_segsocial_empdo` 
            WHERE `id_liq_empdo` IN (SELECT MAX(`id_liq_empdo`) FROM `nom_liq_segsocial_empdo` WHERE `id_empleado` IN ($ids) GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $ibcant = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search('1', array_column($val_vig, 'id_concepto'));
$smmlv = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('2', array_column($val_vig, 'id_concepto'));
$auxt = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('3', array_column($val_vig, 'id_concepto'));
$auxali = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('6', array_column($val_vig, 'id_concepto'));
$uvt = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('7', array_column($val_vig, 'id_concepto'));
$bbs = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('9', array_column($val_vig, 'id_concepto'));
$basalim = false !== $key ? $val_vig[$key]['valor'] : 0;
$gasrep = 0;
$c = 0;
$tipo = "RA";
$liquidados = [];
if (count($empleado) > 0) {
    $porc_retro = $retroactivo['porcentaje'] / 100;
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $mesreg = date('m');
    if (empty($nomina_anterior)) {
        $descripcion = "LIQUIDACIÓN RETROACTIVA DE NOMINAS DE $fecIni A $fecFin";
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `nom_nominas` (`tipo`, `vigencia`, `descripcion`,`fec_reg`, `mes`, `id_user_reg`, `id_incremento`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tipo, PDO::PARAM_STR);
            $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
            $sql->bindParam(3, $descripcion, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $mesreg, PDO::PARAM_STR);
            $sql->bindParam(6, $id_user, PDO::PARAM_INT);
            $sql->bindParam(7, $id_retroactivo, PDO::PARAM_INT);
            $sql->execute();
            $id_nomina = $cmd->lastInsertId();
            if (!($id_nomina > 0)) {
                echo $sql->errorInfo()[2] . 'NOM';
                exit();
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        //consultar si ya se liquidaron los empleados
        $id_nomina = $nomina_anterior['id_nomina'];
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "SELECT `id_empleado` FROM `nom_liq_salario` WHERE `id_nomina` = $id_nomina";
            $rs = $cmd->query($sql);
            $liquidados = $rs->fetchAll(PDO::FETCH_ASSOC);
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    foreach ($empleado as $e) {
        $auxali = 0;
        $auxt = 0;
        $id = $e['id_empleado'];
        $key = array_search($id, array_column($liquidados, 'id_empleado'));
        if (false === $key) {
            $salarios = BuscaSalarios2($id);
            $ant_base = $salarios[1]['salario_basico'];
            $new_base = $salarios[0]['salario_basico'];
            $salbase = $new_base - $ant_base;
            $key = array_search($id, array_column($liq_auxs, 'id_empleado'));
            $auxt_pag = false !== $key ? $liq_auxs[$key]['val_auxtran'] : 0;
            $auxali_pag = false !== $key ? $liq_auxs[$key]['val_auxalim'] : 0;
            $tipo_emp = $e['tipo_empleado'];
            $auxt_dcto = 0;
            $auxali_dcto = 0;
            if ($new_base > $smmlv * 2 && $auxt_pag > 0) {
                $auxt_dcto = $auxt;
            }
            if ($new_base > $basalim && $auxali_pag > 0) {
                $auxali_dcto = $auxali;
            }
            if ($new_base <= $smmlv * 2) {
                $auxt = 0;
            }
            if ($new_base <= $basalim) {
                $auxali = 0;
            }
            $max_dias = $retroactivo['meses'] * 30;
            $key = array_search($id, array_column($diaslaborados, 'id_empleado'));
            $total_dias = false !== $key ? $diaslaborados[$key]['total_dlab'] : 0;
            $total_dias = $total_dias > $max_dias ? $max_dias : $total_dias;
            $key = array_search($id, array_column($dias_dcto, 'id_empleado'));
            $dias_nolab = false !== $key ? $dias_dcto[$key]['dias'] : 0;
            $dias_lab = $total_dias - $dias_nolab;
            $liq_dialab = ($salbase / 30) * $dias_lab;
            //horas extras
            if (!empty($horas)) {
                $valhora = $salbase / 240;
                foreach ($horas as $h) {
                    if ($h['id_empleado'] == $id) {
                        $idhe = $h['id_he_trab'];
                        if ($h['codigo'] == 3 || $h['codigo'] == 5) {
                            $factor = $h['factor'] / 100;
                            $cnthe = $h['cantidad_he'];
                            $valhe = $valhora * $factor * $cnthe;
                        } else {
                            $factor = ($h['factor'] / 100) + 1;
                            $cnthe = $h['cantidad_he'];
                            $valhe = $valhora * $factor * $cnthe;
                        }
                        try {
                            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                            $sql = "INSERT INTO `nom_liq_horex` (`id_he_lab`, `val_liq`, `fec_reg`, `id_nomina`, `tipo_liq`) 
                                    VALUES (?, ?, ?, ?, ?)";
                            $sql = $cmd->prepare($sql);
                            $sql->bindParam(1, $idhe, PDO::PARAM_INT);
                            $sql->bindParam(2, $valhe, PDO::PARAM_STR);
                            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                            $sql->bindParam(4, $id_nomina, PDO::PARAM_INT);
                            $sql->bindParam(5, $tipo, PDO::PARAM_INT);
                            $sql->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $sql->errorInfo()[2] . 'HE';
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
                $sql = "SELECT `id_empleado`, SUM(`val_liq`) AS `tot_he`
            FROM 
                (SELECT `id_empleado`, `val_liq`
                FROM
                    `nom_liq_horex`
                INNER JOIN `nom_horas_ex_trab` 
                    ON (`nom_liq_horex`.`id_he_lab` = `nom_horas_ex_trab`.`id_he_trab`)
                WHERE `id_empleado` = $id AND `id_nomina` = $id_nomina) AS `the`
            GROUP BY `id_empleado`";
                $rs = $cmd->query($sql);
                $tothe = $rs->fetch(PDO::FETCH_ASSOC);
                $devhe = !empty($tothe) ? $tothe['tot_he'] : 0;
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            // Ingresar valores liquidados
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_dlab_auxt` 
                        (`id_empleado`, `dias_liq`, `val_liq_dias`, `val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`, `fec_reg`, `id_nomina`,`tipo_liq`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $dias_lab, PDO::PARAM_INT);
                $sql->bindParam(3, $liq_dialab, PDO::PARAM_STR);
                $sql->bindParam(4, $auxt, PDO::PARAM_STR);
                $sql->bindParam(5, $auxali, PDO::PARAM_STR);
                $sql->bindParam(6, $gasrep, PDO::PARAM_STR);
                $sql->bindParam(7, $devhe, PDO::PARAM_STR);
                $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(9, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(10, $tipo, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'LQS';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //prestaciones sociales
            $key = array_search($id, array_column($lastpay, 'id_empleado'));
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
            $key = array_search($id, array_column($diaslaborados, 'id_empleado'));
            $d_laborados = $key !== false ? $diaslaborados[$key]['total_dlab'] : 0;
            //prima de servicios
            $prima_sv_dia = ($salbase + $auxt + $auxali + $gasrep + $ant_bsp / 12) / 720;
            $prima = $prima_sv_dia * $d_laborados;
            //prima de vacaciones
            $prima_vac_dia = ((($salbase +  $gasrep + $auxt + $auxali + $ant_bsp  / 12 + $ant_prima_servicio / 12) * 15) / 30) / 360;
            $prima_vac = $prima_vac_dia * $d_laborados;
            //liquidacion vacaciones
            $vac_dia  = ((($salbase  + $gasrep + $auxt + $auxali + $ant_bsp  / 12 + $ant_prima_servicio / 12) * 22) / 30) / 360;
            $vacacion = $vac_dia * $d_laborados;
            //Bonificacion de recreacion
            $bonrecrea = (($salbase / 30) * (2 * $d_laborados / 360));
            //prima de navidad
            $prima_nav_dia = (($salbase +  $gasrep + $auxt + $auxali + ($ant_bsp  / 12) + ($ant_prima_servicio / 12) + ($ant_prima_vacaciones / 12))) / 360;
            $prima_nav = $prima_nav_dia * $d_laborados;
            //cesantia e intereses  cesantia
            $censantia_dia = ($salbase + $gasrep +  $auxt + $auxali + $ant_bsp  / 12 + $ant_prima_servicio / 12 + $ant_prima_vacaciones / 12 + $ant_prima_navidad / 12) / 360;
            $cesantia = $censantia_dia * $d_laborados;
            $icesantia = $cesantia * 0.12;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_prestaciones_sociales` (`id_empleado`, `val_vacacion`, `val_cesantia`, `val_interes_cesantia`
                                                                , `val_prima`, `val_prima_vac`,`val_prima_nav`,`val_bonifica_recrea`
                                                                , `fec_reg`, `id_nomina`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $vacacion, PDO::PARAM_STR);
                $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
                $sql->bindParam(4, $icesant, PDO::PARAM_STR);
                $sql->bindParam(5, $prima, PDO::PARAM_STR);
                $sql->bindParam(6, $prima_vac, PDO::PARAM_STR);
                $sql->bindParam(7, $prima_nav, PDO::PARAM_STR);
                $sql->bindParam(8, $bonrecrea, PDO::PARAM_STR);
                $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(10, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'RESERVA';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //incapacidades
            $valincap = 0;
            //indemniaciones por vacaciones
            $keyindem = array_search($id, array_column($indemnizavac, 'id_empleado'));
            if ($keyindem !== false) {
                $idindem = $indemnizavac[$key]['id_indemniza'];
                $val_indem = $indemnizavac[$key]['val_liq'] * $porc_retro;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_indemniza_vac`
                            (`id_indemnizacion`, `val_liq`, `id_user_reg`, `fec_reg`,`id_nomina`)
                        VALUES (?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idindem, PDO::PARAM_INT);
                    $sql->bindParam(2, $valindem, PDO::PARAM_STR);
                    $sql->bindParam(3, $id_user, PDO::PARAM_INT);
                    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                    $sql->execute();
                    if (!($sql->rowCount() > 0)) {
                        echo $sql->errorInfo()[2] . 'INDEM';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                $valindem = 0;
            }
            //licencia materna o paterna
            $key = array_search($id, array_column($licencias, 'id_empleado'));
            if ($key !== false) {
                $idlc = $licencias[$key]['id_licmp'];
                $id_eps = $licencias[$key]['id_eps'];
                $daylc = $licencias[$key]['dias_liqs'];
                $vallicen = $licencias[$key]['val_liq'] * $porc_retro;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_licmp` (`id_licmp`, `id_eps`, `dias_liqs`, `val_liq`, `anio_lic`, `fec_reg`, `id_nomina`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $idlc, PDO::PARAM_INT);
                    $sql->bindParam(2, $id_eps, PDO::PARAM_INT);
                    $sql->bindParam(3, $daylc, PDO::PARAM_INT);
                    $sql->bindParam(4, $vallicen, PDO::PARAM_STR);
                    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
                    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        echo $sql->errorInfo()[2] . 'LICR';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                $vallicen = 0;
            }
            //Bonificación por Servicios Prestados
            $bsp_salarial = 0;
            $key = array_search($id, array_column($liq_bsp, 'id_empleado'));
            if (false !== $key) {
                $bsp_liq = $liq_bsp[$key]['val_bsp'];
                $bsp = (($new_base + $gasrep) <= $bbs ? ($new_base + $gasrep) * 0.5 : ($new_base + $gasrep) * 0.35);
                $bsp = $bsp - $bsp_liq;
                $bsp_salarial = $bsp;
                if ($bsp > 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `nom_liq_bsp`(`id_empleado`, `val_bsp`, `id_user_reg`, `fec_reg`, `id_nomina`, `anio`) 
                            VALUES (?, ?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id, PDO::PARAM_INT);
                        $sql->bindParam(2, $bsp, PDO::PARAM_STR);
                        $sql->bindParam(3, $id_user, PDO::PARAM_INT);
                        $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                        $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $sql->errorInfo()[2] . 'BSP';
                        }
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
            //prima de vacaciones
            $key = array_search($id, array_column($vacaciones, 'id_empleado'));
            $prima_vac = 0;
            $vacacion = 0;
            $bonrecrea = 0;
            if (false !== $key) {
                $dias_vac = $vacaciones[$key]['dias_liquidar'];
                $val_vac = $vacaciones[$key]['val_liq'];
                $val_prim_vac = $vacaciones[$key]['val_prima_vac'];
                $val_recrea = $vacaciones[$key]['val_bon_recrea'];
                $id_vac = $vacaciones[$key]['id_vac'];
                $diasVcas = $vacaciones[$key]['dias_liqs'];
                $diasinactivo = $vacaciones[$key]['dias_inactivo'] != '' ? $vacaciones[$key]['dias_inactivo'] : 22;
                $diashabiles = $vacaciones[$key]['dias_habiles'] != '' ? $vacaciones[$key]['dias_habiles'] : 15;
                $doceavas_pvac = ((720 * $val_prim_vac) / $dias_vac) - $ant_base;
                $prima_vac_dia = (($new_base + $doceavas_pvac) * $diashabiles) / 30;
                $prima_vac = ($prima_vac_dia / 360) * $dias_vac;
                //liquidacion vacaciones
                $vac_dia  = (($new_base + $doceavas_pvac) * $diasinactivo) / 30;
                $vacacion = ($vac_dia / 360) * $dias_vac;
                //Bonificacion de recreacion
                $bonrecrea = (($new_base / 30) * (2 * $dias_vac / 360));
                $prima_vac = $prima_vac - $val_prim_vac;
                $vacacion = $vacacion - $val_vac;
                $bonrecrea = $bonrecrea - $val_recrea;
                if ($id_vac > 0) {
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                        $sql = "INSERT INTO `nom_liq_vac`
                                    (`id_vac`, `dias_liqs`, `val_liq`, `val_prima_vac`, `val_bon_recrea`, `fec_reg`,`id_nomina`, `tipo_liq`)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $id_vac, PDO::PARAM_INT);
                        $sql->bindParam(2, $diasVcas, PDO::PARAM_STR);
                        $sql->bindParam(3, $vacacion, PDO::PARAM_STR);
                        $sql->bindParam(4, $prima_vac, PDO::PARAM_STR);
                        $sql->bindParam(5, $bonrecrea, PDO::PARAM_STR);
                        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                        $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                        $sql->bindParam(8, $tipo, PDO::PARAM_INT);
                        $sql->execute();
                        if (!($cmd->lastInsertId() > 0)) {
                            echo $cdm->errorInfo()[2] . 'VAC';
                        }

                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                }
            }
            //prima de servicios
            $key = array_search($id, array_column($primas_servicio, 'id_empleado'));
            if (false !== $key) {
                $dias_prim_sv = $primas_servicio[$key]['dias'];
                $val_prim_sv = $primas_servicio[$key]['valor'];
                $doceava_bsp = (($val_prim_sv * 720) / $dias_prim_sv) - $ant_base;
                $prima_sv_dia = ($new_base + $doceava_bsp) / 720;
                $prima_sv = $prima_sv_dia * $dias_prim_sv;
                $prima_sv = $prima_sv - $val_prim_sv;
                $prima_sv = $prima_sv > 0 ? $prima_sv : $prima_sv * -1;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_prima`(`id_empleado`,`cant_dias`,`val_liq_ps`,`fec_reg`,`id_nomina`, `corte`)
                        VALUES (?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $diasToPriServ, PDO::PARAM_STR);
                    $sql->bindParam(3, $prima_sv, PDO::PARAM_STR);
                    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                    $sql->bindParam(6, $fec_retiro, PDO::PARAM_STR);
                    $sql->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $cdm->errorInfo()[2] . 'PS';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            } else {
                $prima_sv = 0;
            }
            // liquidar cesantias, interes a cesantias, prima de navidad;
            //prima de navidad
            $prima_nav = 0;
            $cesantia = 0;
            $icesantia = 0;
            if ($e['fec_retiro'] != '' && $e['fec_retiro'] <= $fecFin) {
                $termina = $e['fec_retiro'];
                $inicia = $e['fech_inicio'] > $fecIni ? $e['fech_inicio'] : $fecIni;
                $diasnov = calcularDias($inicia, $e['fec_retiro']);
                $diasnov_divide = $diasnov == 0 ? 1 : $diasnov;
                $promHorExt = PromedioHoras($inicia, $e['fec_retiro'], $id) * $porc_retro;
                $key = array_search($id, array_column($primas_navidad, 'id_empleado'));
                $prima_nav_ant = $key !== false ? $primas_navidad[$key]['val_liq_pv'] : 0;
                $doceavas_ants = (($prima_nav_ant * 360) / $diasnov_divide) - $ant_base;
                $prima_nav_dia = (($new_base + $doceavas_ants)) / 360;
                $prima_nav = $prima_nav_dia * $diasnov;
                $prima_nav = $prima_nav - $prima_nav_ant;
                //cesantia e intereses  cesantia
                $key = array_search($id, array_column($vals_censatias, 'id_empleado'));
                $cesantia_ant = $key !== false ? $vals_censatias[$key]['val_cesantias'] : 0;
                $doceavas_ces = (($cesantia_ant * 360) / $diasnov_divide) - $ant_base;
                $censantia_dia = ($new_base + $promHorExt + $doceavas_ces) / 360;
                $cesantia = $censantia_dia * $diasnov;
                $cesantia = $cesantia - $cesantia_ant;
                $icesantia = $cesantia * 0.12;
                //prima de navidad
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_prima_nav`(`id_empleado`,`cant_dias`,`val_liq_pv`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $diasnov, PDO::PARAM_STR);
                    $sql->bindParam(3, $prima_nav, PDO::PARAM_STR);
                    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                    $sql->bindParam(6, $termina, PDO::PARAM_STR);
                    $sql->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $sql->errorInfo()[2] . 'PN ';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                //cesantias
                try {
                    $porcentaje = 12;
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO `nom_liq_cesantias`(`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $diasnov, PDO::PARAM_STR);
                    $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
                    $sql->bindParam(4, $icesantia, PDO::PARAM_STR);
                    $sql->bindParam(5, $porcentaje, PDO::PARAM_STR);
                    $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                    $sql->bindParam(8, $termina, PDO::PARAM_STR);
                    $sql->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $sql->errorInfo()[2] . 'CES';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
            }
            //*****************************************************
            //salud,pension,arl-> compensatorio, horas extras y bsp 
            $base_ss = $liq_dialab + $devhe + $bsp_salarial;
            $key = array_search($id, array_column($ibcant, 'id_empleado'));
            $salud_ant = false !== $key ? $ibcant[$key]['aporte_salud_emp'] : 0;
            $ibc = $salud_ant * 25;
            $base_ps = $base_ss + $ibc;
            $base_ss = $base_ss + $ibc > $smmlv * 25 ? $smmlv * 25 - $ibc : $base_ss;
            $saludempleado = $base_ss * 0.04;
            $pensionempleado = $base_ss * 0.04;
            if ($base_ps < $smmlv * 4) {
                $solidpension = 0;
                $porcenps = 0;
            } else if ($base_ps >= $smmlv * 4  && $base_ps < $smmlv * 16) {
                $solidpension = $base_ps * 0.01;
                $porcenps = 1;
            } else if ($base_ps >= $smmlv * 16  && $base_ps < $smmlv * 17) {
                $solidpension = $base_ps * 0.012;
                $porcenps = 1.2;
            } else if ($base_ps >= $smmlv * 17  && $base_ps < $smmlv * 18) {
                $solidpension = $base_ps * 0.014;
                $porcenps = 1.4;
            } else if ($base_ps >= $smmlv * 18  && $base_ps < $smmlv * 19) {
                $solidpension = $base_ps * 0.016;
                $porcenps = 1.6;
            } else if ($base_ps >= $smmlv * 19  && $base_ps < $smmlv * 20) {
                $solidpension = $base_ps * 0.018;
                $porcenps = 1.8;
            } else if ($base_ps >= $smmlv * 20) {
                $solidpension = $base_ps * 0.02;
                $porcenps = 2;
            }
            $saludempresa = $base_ss * 0.085;
            $pensionempresa = $base_ss * 0.12;
            $key = array_search($id, array_column($riesgos, 'id_empleado'));
            $cot_rlab = $key !== false ? $riesgos[$key]['cotizacion'] : 0;
            $rieslab = $base_ss *  $cot_rlab;
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
                $saludempresa = $base_ss * 0.125;
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

            $key = array_search($id, array_column($eps, 'id_empleado'));
            $id_eps = false !== $key ? $eps[$key]['id_eps'] : null;
            $key = array_search($id, array_column($afp, 'id_empleado'));
            $id_afp = false !== $key ? $afp[$key]['id_afp'] : null;
            $key = array_search($id, array_column($arl, 'id_empleado'));
            $id_arl = false !== $key ? $arl[$key]['id_arl'] : null;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_segsocial_empdo` 
                        (`id_empleado`, `id_eps`, `id_arl`, `id_afp`, `aporte_salud_emp`, `aporte_pension_emp`, 
                        `aporte_solidaridad_pensional`, `porcentaje_ps`, `aporte_salud_empresa`, `aporte_pension_empresa`, 
                        `aporte_rieslab`, `fec_reg`, `id_nomina`, `tipo_liq`, `anio`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
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
                $sql->bindValue(12, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(13, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(14, $tipo, PDO::PARAM_INT);
                $sql->bindParam(15, $vigencia, PDO::PARAM_STR);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'SS';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //Parafiscales
            $auxali = $auxt = 0;
            $base_pf = $base_ss + $vacacion + $prima_sv;
            $sena = $base_pf * 0.02;
            $icbf = $base_pf * 0.03;
            $comfam = $base_pf * 0.04;
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
                $sql = "INSERT INTO `nom_liq_parafiscales` 
                        (`id_empleado`, `val_sena`, `val_icbf`, `val_comfam`, `fec_reg`,`id_nomina`, `tipo_liq`, `anio_pfis`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $sena, PDO::PARAM_STR);
                $sql->bindParam(3, $icbf, PDO::PARAM_STR);
                $sql->bindParam(4, $comfam, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(7, $tipo, PDO::PARAM_INT);
                $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'PF';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //retencion en la fuente
            $pagoxdependiente = 0;
            $key = array_search($id, array_column($pagoxdpte, 'id_empleado'));
            if (false !== $key) {
                $pagoxdependiente = ($base_ss + $vacacion + $prima_vac + $bonrecrea + $gasrep) * 0.1;
                $maxpagoxdependiente = 32 * $uvt;
                if ($pagoxdependiente > $maxpagoxdependiente) {
                    $pagoxdependiente = $maxpagoxdependiente;
                }
            }
            $valrf = $base_ss + $vacacion + $prima_vac + $bonrecrea + $gasrep - $saludempleado - $pensionempleado - $solidpension - $pagoxdependiente;
            $valdpurado =  $valrf - ($valrf * 0.25);
            $inglabuvt = $valdpurado / $uvt;
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
                $sql = "INSERT INTO `nom_retencion_fte` (`id_empleado`, `val_ret`, `fec_reg`, `base`,`id_nomina`) 
                        VALUES (?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $retencion, PDO::PARAM_STR);
                $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(4, $valdpurado, PDO::PARAM_STR);
                $sql->bindParam(5, $id_nomina, PDO::PARAM_INT);
                $sql->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            //neto a pagar
            $salarioneto = $devhe + $liq_dialab + $bsp_salarial + $vacacion + $prima_vac + $bonrecrea + $gasrep + $prima_sv + $prima_nav + $cesantia + $icesantia - $saludempleado - $pensionempleado - $solidpension - $retencion;
            $fpag = '1';
            $mpag = '47';
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_salario` (`id_empleado`, `val_liq`, `forma_pago`, `metodo_pago`, `fec_reg`, `id_nomina`, `sal_base`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
                $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(7, $salbase, PDO::PARAM_STR);
                $sql->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }

            $c++;
        }
    }
} else {
    echo 'No hay empleados para liquidar';
}
if ($c > 0) {
    echo 'ok';
} else {
    echo 'No hay empleado nuevos para liquidar';
}
function calcularDias($fechaInicial, $fechaFinal)
{
    $fechaInicial = strtotime($fechaInicial);
    $fechaFinal = strtotime($fechaFinal);
    $dias360 = 0;
    if (!($fechaInicial > $fechaFinal)) {
        while ($fechaInicial < $fechaFinal) {
            $dias360 += 30; // Agregar 30 días por cada mes
            $fechaInicial = strtotime('+1 month', $fechaInicial);
        }

        // Agregar los días restantes después del último mes completo
        $dias360 += ($fechaFinal - $fechaInicial) / (60 * 60 * 24);
        $dias360 = $dias360 + 1;
    }
    return $dias360;
}
function redondeo($value, $places)
{
    $mult = pow(10, abs($places));
    return $places < 0 ? ceil($value / $mult) * $mult : ceil($value * $mult) / $mult;
}

function BuscaSalarios2($id_empleado)
{
    include '../../conexion.php';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT
                    `id_empleado`, `salario_basico` 
                FROM `nom_salarios_basico` 
                WHERE `id_empleado` = $id_empleado ORDER BY `id_salario` DESC LIMIT 2";
        $rs = $cmd->query($sql);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $rs->fetchAll(PDO::FETCH_ASSOC);
}

function PromedioHoras($feci, $fecf, $id)
{
    include '../../conexion.php';
    $promedio = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT 
                    `id_nomina`
                FROM 
                    (SELECT
                        `id_nomina`
                        , CONCAT_WS('-', `vigencia`
                        , `mes`, '01') AS `fecha`
                        , `estado`
                        , `tipo`
                    FROM
                        `nom_nominas`
                    WHERE (`estado` >= 5 AND `tipo` = 'N' AND `id_nomina` > 0)) AS `t1`
                WHERE `t1`.`fecha` BETWEEN '$feci' AND '$fecf'";
        $rs = $cmd->query($sql);
        $ids = $rs->fetchAll();

        if (!empty($ids)) {
            $total = count($ids);
            $ids = implode(',', array_column($ids, 'id_nomina'));
            $sql = "SELECT 
                        SUM(`liquidado`) AS `total`
                    FROM 
                        (SELECT
                            SUM(`nom_liq_horex`.`val_liq`) AS `liquidado`
                            , `nom_liq_horex`.`id_nomina`
                        FROM
                            `nom_liq_horex`
                            INNER JOIN `nom_horas_ex_trab` 
                                ON (`nom_liq_horex`.`id_he_lab` = `nom_horas_ex_trab`.`id_he_trab`)
                        WHERE (`nom_horas_ex_trab`.`id_empleado` = $id AND `nom_liq_horex`.`id_nomina` IN ($ids))
                    GROUP BY `nom_liq_horex`.`id_nomina`) AS `t2`";
            $rs = $cmd->query($sql);
            $valor = $rs->fetch();
            if (!empty($valor)) {
                $promedio = $valor['total'] / $total;
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    return $promedio;
}
