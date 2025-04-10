<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}

include '../../conexion.php';
include '../../permisos.php';
date_default_timezone_set('America/Bogota');
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `fec_retiro`, `fech_inicio`, `tipo_empleado`, `nom_empleado`.`representacion`
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
    $sql = "SELECT 
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`representacion`
                , `t1`.`val_bsp`
                , `t1`.`mes`
                , `t1`.`anio`
                , `t2`.`corte_ces`
                , `t3`.`val_liq_ps`
                , `t3`.`corte_prim_sv`
                , `t4`.`val_liq_pv`
                , `t4`.`corte_prim_nav`
                , `t5`.`corte` as `corte_vac`
                , `t5`.`val_liq`
                , `t5`.`val_prima_vac`
                , `t5`.`val_bon_recrea`
            FROM
                `nom_empleado`
                LEFT JOIN  
                    (SELECT
                        `tbsp`.`id_empleado`
                        , CASE
                            WHEN `tbsp`.`fec_comp1` > IFNULL(`tbspra`.`fec_comp2`,'1900-01-01') THEN `tbsp`.`val_bsp`
                            ELSE IFNULL(`tbsp`.`val_bsp`,0) + IFNULL(`tbspra`.`val_bsp_ra`,0)
                        END AS `val_bsp`
                        , RIGHT(`tbsp`.`fec_comp1`, 2) AS `mes` 
                        , LEFT(`tbsp`.`fec_comp1`, 4) AS `anio`
                    FROM	
                        (SELECT
                            `id_empleado`
                            , `val_bsp`
                            , CONCAT(`anio`,`mes`) AS `fec_comp1` 
                        FROM `nom_liq_bsp`
                        WHERE `id_bonificaciones` IN
                            (SELECT
                                MAX(`nom_liq_bsp`.`id_bonificaciones`)
                            FROM
                                `nom_liq_bsp`
                                INNER JOIN `nom_nominas` 
                                ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                            WHERE (`nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5 AND `nom_nominas`.`tipo` = 'N')
                            GROUP BY `nom_liq_bsp`.`id_empleado`)) AS `tbsp`
                        LEFT JOIN
                            (SELECT
                                `nom_liq_bsp`.`id_empleado`
                                , `nom_liq_bsp`.`val_bsp` AS `val_bsp_ra`
                                , DATE_FORMAT(`nom_retroactivos`.`fec_final`, '%Y%m') AS `fec_comp2`
                            FROM `nom_liq_bsp`
                                INNER JOIN `nom_nominas`
                                    ON(`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                LEFT JOIN `nom_retroactivos`
                                    ON(`nom_retroactivos`.`id_incremento` = `nom_nominas`.`id_incremento`)
                            WHERE `nom_liq_bsp`.`id_bonificaciones` IN
                                (SELECT
                                    MAX(`nom_liq_bsp`.`id_bonificaciones`)
                                FROM
                                    `nom_liq_bsp`
                                    INNER JOIN `nom_nominas` 
                                    ON (`nom_liq_bsp`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                WHERE (`nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5 AND `nom_nominas`.`tipo` = 'RA')
                                GROUP BY `nom_liq_bsp`.`id_empleado`)) `tbspra`
                            ON(`tbsp`.`id_empleado` = `tbspra`.`id_empleado`)) AS `t1`
                    ON (`t1`.`id_empleado` = `nom_empleado`.`id_empleado`)
                LEFT JOIN 
                    (SELECT 
                        `id_empleado`,`corte` AS `corte_ces`
                    FROM `nom_liq_cesantias`
                    WHERE `id_liq_cesan`  IN 
                        (SELECT 
                            MAX(`id_liq_cesan`) 
                        FROM `nom_liq_cesantias`
                            INNER JOIN `nom_nominas`
                                ON (`nom_liq_cesantias`.`id_nomina` = `nom_nominas`.`id_nomina`)
                        WHERE `nom_nominas`.`tipo` = 'CE' AND `nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5
                        GROUP BY `id_empleado`)) AS `t2`
                        ON (`nom_empleado`.`id_empleado` = `t2`.`id_empleado`)
                LEFT JOIN
                    (SELECT 
                        `tpv`.`id_empleado`
                        , CASE 
                            WHEN `tpv`.`corte_pv` > IFNULL(`tra`.`corte_ra`,'1900-01-01') THEN IFNULL(`tpv`.`val_liq_pv`,0)
                            ELSE IFNULL(`tpv`.`val_liq_pv`,0) + IFNULL(`tra`.`val_liq_ra`,0)
                        END AS `val_liq_ps`
                        , `tpv`.`corte_pv` AS `corte_prim_sv`
                    FROM
                        (SELECT   
                            `id_empleado`
                            , `val_liq_ps` AS `val_liq_pv`
                            , `corte` AS `corte_pv`
                        FROM `nom_liq_prima` 
                        WHERE `id_liq_prima` IN 
                            (SELECT
                                MAX(`id_liq_prima`) AS `id_lp`
                            FROM
                                `nom_liq_prima`
                                INNER JOIN `nom_nominas`
                                    ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                            WHERE `nom_nominas`.`tipo` = 'PV' AND `nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5
                            GROUP BY `id_empleado`)) AS `tpv`
                        LEFT JOIN 
                            (SELECT   
                                `id_empleado`
                                , `val_liq_ps` AS `val_liq_ra`
                                , `nom_retroactivos`.`fec_final` AS `corte_ra`
                            FROM `nom_liq_prima` 
                                INNER JOIN `nom_nominas`
                                    ON(`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                LEFT JOIN `nom_retroactivos`
                                    ON(`nom_retroactivos`.`id_incremento` = `nom_nominas`.`id_incremento`)
                            WHERE `id_liq_prima` IN 
                                    (SELECT
                                        MAX(`id_liq_prima`) AS `id_lp`
                                    FROM
                                        `nom_liq_prima`
                                        INNER JOIN `nom_nominas`
                                            ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                    WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5
                                    GROUP BY `id_empleado`)) AS `tra`
                                    ON (`tpv`.`id_empleado` = `tra`.`id_empleado`)) AS `t3`
                        ON (`nom_empleado`.`id_empleado` = `t3`.`id_empleado`)
                LEFT JOIN 
                    (SELECT 
                        `id_empleado`,`val_liq_pv`,`corte` AS `corte_prim_nav`
                    FROM `nom_liq_prima_nav`
                    WHERE `id_liq_privac` IN 
                            (SELECT 
                                MAX(`id_liq_privac`) 
                            FROM `nom_liq_prima_nav` 
                                INNER JOIN `nom_nominas` 
                                    ON (`nom_liq_prima_nav`.`id_nomina` = `nom_nominas`.`id_nomina`)
                            WHERE `nom_nominas`.`tipo` = 'PN' AND `nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`estado` = 5
                            GROUP BY `id_empleado`)) AS `t4`
                        ON (`nom_empleado`.`id_empleado` = `t4`.`id_empleado`)
                LEFT JOIN 
                    (SELECT 
                        `tvc`.`id_empleado`
                        , CASE
                            WHEN `tvc`.`corte` > IFNULL(`travc`.`fec_final`,'1900-01-01') THEN IFNULL(`tvc`.`val_prima_vac`,0)
                                            ELSE IFNULL(`tvc`.`val_prima_vac`,0) + IFNULL(`travc`.`val_prima_vac_racv`,0)
                                        END AS `val_prima_vac`
                            , CASE
                            WHEN `tvc`.`corte` > IFNULL(`travc`.`fec_final`,'1900-01-01') THEN IFNULL(`tvc`.`val_liq`,0)
                                            ELSE IFNULL(`tvc`.`val_liq`,0) + IFNULL(`travc`.`val_liq_racv`,0)
                                        END AS `val_liq`
                            , CASE
                            WHEN `tvc`.`corte` > IFNULL(`travc`.`fec_final`,'1900-01-01') THEN IFNULL(`tvc`.`val_bon_recrea`,0)
                                            ELSE IFNULL(`tvc`.`val_bon_recrea`,0) + IFNULL(`travc`.`val_bon_recrea_racv`,0)
                                        END AS `val_bon_recrea`
                            , `tvc`.`corte`
                    FROM 
                        (SELECT
                            `nom_vacaciones`.`id_empleado`
                            , `nom_liq_vac`.`val_prima_vac`
                            , `nom_liq_vac`.`val_liq`
                            , `nom_liq_vac`.`val_bon_recrea`
                            , `nom_vacaciones`.`corte` 
                        FROM
                            `nom_liq_vac`
                            INNER JOIN `nom_nominas` 
                                ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                            INNER JOIN `nom_vacaciones` 
                                ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                        WHERE `nom_liq_vac`.`id_liq_vac` IN
                                (SELECT
                                    MAX(`nom_liq_vac`.`id_liq_vac`) 
                                FROM
                                    `nom_liq_vac`
                                    INNER JOIN `nom_nominas` 
                                    ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                    INNER JOIN `nom_vacaciones` 
                                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                                WHERE `nom_nominas`.`vigencia` <= '$vigencia' AND (`nom_nominas`.`tipo` = 'VC' OR `nom_nominas`.`tipo` = 'N') AND `nom_nominas`.`estado` = 5
                                GROUP BY `nom_vacaciones`.`id_empleado`)) AS `tvc` 
                        LEFT JOIN
                            (SELECT
                                `nom_vacaciones`.`id_empleado`
                                , `nom_liq_vac`.`val_prima_vac` AS `val_prima_vac_racv`
                                , `nom_liq_vac`.`val_liq` AS `val_liq_racv`
                                , `nom_liq_vac`.`val_bon_recrea` AS `val_bon_recrea_racv`
                                , `nom_retroactivos`.`fec_final`
                            FROM
                                `nom_liq_vac`
                                INNER JOIN `nom_nominas` 
                                    ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                INNER JOIN `nom_vacaciones` 
                                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                                LEFT JOIN `nom_retroactivos`
                                    ON(`nom_retroactivos`.`id_incremento` = `nom_nominas`.`id_incremento`)
                            WHERE `nom_liq_vac`.`id_liq_vac` IN
                                (SELECT
                                    MAX(`nom_liq_vac`.`id_liq_vac`) 
                                FROM
                                    `nom_liq_vac`
                                    INNER JOIN `nom_nominas` 
                                    ON (`nom_liq_vac`.`id_nomina` = `nom_nominas`.`id_nomina`)
                                    INNER JOIN `nom_vacaciones` 
                                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
                                WHERE `nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`estado` = 5
                                GROUP BY `nom_vacaciones`.`id_empleado`)) AS `travc`
                            ON(`travc`.`id_empleado` = `tvc`.`id_empleado`)) AS `t5`
                        ON (`nom_empleado`.`id_empleado` = `t5`.`id_empleado`)
            WHERE `nom_empleado`.`id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $cortes = $rs->fetchAll(PDO::FETCH_ASSOC);
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
    $val_vig = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_empleado`,`sal_base` AS `salario_basico`
            FROM `nom_liq_salario`
            WHERE `id_sal_liq` IN (SELECT
                MAX(`nom_liq_salario`.`id_sal_liq`)
            FROM
                `nom_liq_salario`
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_salario`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_nominas`.`vigencia` <= '$vigencia' AND `nom_nominas`.`tipo` = 'N')
            GROUP BY `nom_liq_salario`.`id_empleado`)";
    $rs = $cmd->query($sql);
    $salario = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//cambiar por fondo de cesantias
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_fc`
            FROM
                `nom_novedades_fc`
            WHERE `id_novfc`  IN (SELECT MAX(`id_novfc`) FROM `nom_novedades_fc` GROUP BY `id_empleado`)
                AND `id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $fondos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_liq_salario`.`id_empleado`
                , `nom_nominas`.`tipo`
                , `nom_nominas`.`vigencia`
            FROM
                `nom_liq_salario`
                INNER JOIN `nom_nominas` 
                    ON (`nom_liq_salario`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE (`nom_liq_salario`.`id_empleado` IN ($ids) 
                    AND `nom_nominas`.`tipo` = 'CE' 
                    AND `nom_nominas`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $liquidados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$key = array_search('1', array_column($val_vig, 'id_concepto'));
$smmlv = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('2', array_column($val_vig, 'id_concepto'));
$auxt_base = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('3', array_column($val_vig, 'id_concepto'));
$auxali_base = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('6', array_column($val_vig, 'id_concepto'));
$uvt = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('7', array_column($val_vig, 'id_concepto'));
$bbs = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('8', array_column($val_vig, 'id_concepto'));
$repre = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('9', array_column($val_vig, 'id_concepto'));
$basalim = false !== $key ? $val_vig[$key]['valor'] : 0;
$c = 0;
$tipo = "CE";
if (count($empleado) > 0) {
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $descripcion = "LIQUIDACIÓN CESANTÍAS";
    $mesreg = date('m');
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT 
                    MAX(`id_nomina`) AS `id_nomina`
                FROM 
                    `nom_nominas`
                WHERE `tipo` = 'CE' AND `estado` = 1 AND `vigencia`= '$vigencia'";
        $rs = $cmd->query($sql);
        $lastID = $rs->fetch();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    if ($lastID['id_nomina'] > 0) {
        $id_nomina = $lastID['id_nomina'];
    } else {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `nom_nominas` (`tipo`, `vigencia`, `descripcion`,`fec_reg`, `mes`, `id_user_reg`) VALUES (?, ?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tipoNom, PDO::PARAM_STR);
            $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
            $sql->bindParam(3, $describeNom, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $mesreg, PDO::PARAM_STR);
            $sql->bindParam(6, $id_user, PDO::PARAM_INT);
            $tipoNom = $tipo;
            $describeNom = $descripcion;
            $sql->execute();
            $id_nomina = $cmd->lastInsertId();
            if (!($id_nomina > 0)) {
                echo $sql->errorInfo()[2] . 'NOMC';
                exit();
            } else {
                $tipo_ic = $tipoNom = "IC";
                $describe_ic = $describeNom = "LIQUIDACIÓN INTERÉS A CESANTÍAS";
                $sql->execute();
                $id_nomina_ic = $cmd->lastInsertId();
                if (!($id_nomina_ic > 0)) {
                    echo $sql->errorInfo()[2] . 'NOMIC';
                    exit();
                }
            }

            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    foreach ($empleado as $e) {
        $id = $e['id_empleado'];
        $key = array_search($id, array_column($liquidados, 'id_empleado'));
        if ($key === false) {
            $cero = 0;
            $key = array_search($id, array_column($salario, 'id_empleado'));
            $salbase = false !== $key ? $salario[$key]['salario_basico'] : 0;
            $tipo_emp = $e['tipo_empleado'];
            $auxt = $salbase > $smmlv * 2 ? 0 : $auxt_base;
            $auxali = $salbase > $basalim ? 0 : $auxali_base;
            $gasrep = $e['representacion'] == 1 ? $repre : 0;
            $key = array_search($id, array_column($cortes, 'id_empleado'));
            $datos = false !== $key ? $cortes[$key] : [];
            //FECHAS
            $fec_corte = $vigencia . '-12-31';
            $feci_ces = $datos['corte_ces'] != '' ? date('Y-m-d', strtotime($datos['corte_ces'] . ' + 1 day')) : $e['fech_inicio'];
            $feci_priserv = $datos['corte_prim_sv'] != '' ? date('Y-m-d', strtotime($datos['corte_prim_sv'] . ' + 1 day')) : $e['fech_inicio'];
            $primserant = $datos['val_liq_ps'] > 0 ? $datos['val_liq_ps'] : 0;
            $bspant  = $datos['val_bsp'] > 0 ? $datos['val_bsp'] : 0;
            $primavacant = $datos['val_prima_vac'] > 0 ? $datos['val_prima_vac'] : 0;
            $primanavant = $datos['val_liq_pv'] > 0 ? $datos['val_liq_pv'] : 0;
            $diasToCes = calcularDias($feci_ces, $fec_corte, $id);
            $diasToCes = $diasToCes > 360 ? 360 : $diasToCes;
            $promHorExt = PromedioHoras($feci_ces, $fec_corte, $id);
            //cesantia e intereses  cesantia
            $censantia_dia = ($salbase + $gasrep +  $auxt + $auxali + $promHorExt + $bspant  / 12 + $primserant / 12 + $primavacant / 12 + $primanavant / 12) / 360;
            $cesantia = $censantia_dia * $diasToCes;
            $icesantia = $cesantia * 0.12;
            if ($_SESSION['caracter'] == '1') {
                $cesantia = ($salbase + $promHorExt) * $diasToCes / 360;
                $icesantia = $cesantia * 0.12;
            }
            //cesantias
            $porcentaje = 12;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_cesantias`(`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`fec_reg`,`id_nomina`, `corte`, `salbase`, `gasrep`, `auxt`, `auxali`, `promHorExt`, `bspant`, `primserant`, `primavacant`, `primanavant`, `diasToCes`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $diasToCes, PDO::PARAM_STR);
                $sql->bindParam(3, $cesantia, PDO::PARAM_STR);
                $sql->bindParam(4, $cero, PDO::PARAM_STR);
                $sql->bindParam(5, $cero, PDO::PARAM_STR);
                $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(7, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(8, $fec_corte, PDO::PARAM_STR);
                $sql->bindParam(9, $salbase, PDO::PARAM_STR);
                $sql->bindParam(10, $gasrep, PDO::PARAM_STR);
                $sql->bindParam(11, $auxt, PDO::PARAM_STR);
                $sql->bindParam(12, $auxali, PDO::PARAM_STR);
                $sql->bindParam(13, $promHorExt, PDO::PARAM_STR);
                $sql->bindParam(14, $bspant, PDO::PARAM_STR);
                $sql->bindParam(15, $primserant, PDO::PARAM_STR);
                $sql->bindParam(16, $primavacant, PDO::PARAM_STR);
                $sql->bindParam(17, $primanavant, PDO::PARAM_STR);
                $sql->bindParam(18, $diasToCes, PDO::PARAM_STR);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'CES';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            try {
                $porcentaje = 12;
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_cesantias`(`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`fec_reg`,`id_nomina`, `corte`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $diasToCes, PDO::PARAM_STR);
                $sql->bindParam(3, $cero, PDO::PARAM_STR);
                $sql->bindParam(4, $icesantia, PDO::PARAM_STR);
                $sql->bindParam(5, $porcentaje, PDO::PARAM_STR);
                $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(7, $id_nomina_ic, PDO::PARAM_INT);
                $sql->bindParam(8, $fec_corte, PDO::PARAM_STR);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'ICES';
                }
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
                $sql->bindParam(2, $cero, PDO::PARAM_INT);
                $sql->bindParam(3, $cero, PDO::PARAM_STR);
                $sql->bindParam(4, $cero, PDO::PARAM_STR);
                $sql->bindParam(5, $cero, PDO::PARAM_STR);
                $sql->bindParam(6, $cero, PDO::PARAM_STR);
                $sql->bindParam(7, $cero, PDO::PARAM_STR);
                $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(9, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(10, $tipo, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'LQSC';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_dlab_auxt` 
                        (`id_empleado`, `dias_liq`, `val_liq_dias`, `val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`, `fec_reg`, `id_nomina`,`tipo_liq`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $cero, PDO::PARAM_INT);
                $sql->bindParam(3, $cero, PDO::PARAM_STR);
                $sql->bindParam(4, $cero, PDO::PARAM_STR);
                $sql->bindParam(5, $cero, PDO::PARAM_STR);
                $sql->bindParam(6, $cero, PDO::PARAM_STR);
                $sql->bindParam(7, $cero, PDO::PARAM_STR);
                $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(9, $id_nomina_ic, PDO::PARAM_INT);
                $sql->bindParam(10, $tipo_ic, PDO::PARAM_INT);
                $sql->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $sql->errorInfo()[2] . 'LQSIC';
                }
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $fpag = '1';
            $mpag = '47';
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_salario` (`id_empleado`, `val_liq`, `forma_pago`, `metodo_pago`, `fec_reg`, `id_nomina`,`sal_base`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $cesantia, PDO::PARAM_STR);
                $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                $sql->bindParam(7, $salbase, PDO::PARAM_STR);
                $sql->execute();
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "INSERT INTO `nom_liq_salario` (`id_empleado`, `val_liq`, `forma_pago`, `metodo_pago`, `fec_reg`, `id_nomina`,`sal_base`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id, PDO::PARAM_INT);
                $sql->bindParam(2, $icesantia, PDO::PARAM_STR);
                $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(6, $id_nomina_ic, PDO::PARAM_INT);
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
    echo 'No se liquidó ningún empleado';
}
function calcularDias($fI, $fF, $id)
{
    include '../../conexion.php';
    $fechaInicial = strtotime($fI);
    $fechaFinal = strtotime($fF);
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
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "SELECT
                    SUM(`dias_inactivo`) AS `dias`
                FROM
                    `nom_licenciasnr`
                WHERE ((`fec_inicio` BETWEEN '$fI' AND '$fF')OR (`fec_fin` BETWEEN '$fI' AND '$fF')) AND `id_empleado` = $id";
        $rs = $cmd->query($sql);
        $dias = $rs->fetch(PDO::FETCH_ASSOC);
        $dlcnr = !empty($dias) ? $dias['dias'] : 0;
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $dias360 = $dias360 > $dlcnr ? $dias360 - $dlcnr : 0;
    return $dias360;
}
function redondeo($value, $places)
{
    $mult = pow(10, abs($places));
    return $places < 0 ? ceil($value / $mult) * $mult : ceil($value * $mult) / $mult;
}


function PromedioHoras($feci, $fecf, $id)
{
    include '../../conexion.php';
    $promedio = 0;
    $feci = explode('-', $feci);
    $feci = $feci[0] . '-' . $feci[1] . '-01';
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
                    WHERE (`estado` >= 5 AND `tipo` = 'RA' AND `id_nomina` > 0)) AS `t1`
                WHERE `t1`.`fecha` BETWEEN '$feci' AND '$fecf'";
        $rs = $cmd->query($sql);
        $idsRA = $rs->fetchAll();

        if (!empty($ids)) {
            $total = count($ids);
            $ids = implode(',', array_column($ids, 'id_nomina'));
            if (!empty($idsRA)) {
                $idsRA = implode(',', array_column($idsRA, 'id_nomina'));
                $ids = $ids . ',' . $idsRA;
            }
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
