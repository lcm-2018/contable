<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$ids = isset($_POST['empleado']) ? $_POST['empleado'] : exit('Acción no permitida');
$ids = implode(',', $ids);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
            `nom_empleado`.`id_empleado`
            , `nom_empleado`.`tipo_doc`
            , `nom_empleado`.`no_documento`
            , `nom_empleado`.`genero`
            , `nom_empleado`.`apellido1`
            , `nom_empleado`.`apellido2`
            , `nom_empleado`.`nombre2`
            , `nom_empleado`.`nombre1`
            , `nom_empleado`.`fech_inicio`
            , `nom_empleado`.`fec_retiro`
            , `nom_empleado`.`estado`
            , `nom_empleado`.`salario_integral`
            , `nom_empleado`.`representacion`
            , `nom_salarios_basico`.`id_salario`
            , `nom_salarios_basico`.`vigencia`
            , `nom_salarios_basico`.`salario_basico`
        FROM (SELECT
            MAX(`id_salario`) AS `id_salario`, `id_empleado`
            FROM
                `nom_salarios_basico`
            WHERE `vigencia` <= '$vigencia'
            GROUP BY `id_empleado`) AS `t`
        INNER JOIN `nom_salarios_basico`
            ON (`nom_salarios_basico`.`id_salario` = `t`.`id_salario`)
        INNER JOIN `nom_empleado`
            ON (`nom_empleado`.`id_empleado` = `t`.`id_empleado`)
        WHERE `nom_empleado`.`id_empleado` IN ($ids)";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, CONCAT(`anio`, `periodo`) AS `periodo`
            FROM `nom_liq_prima`
            WHERE `anio` = '$vigencia'";
    $rs = $cmd->query($sql);
    $primliq = $rs->fetchAll();
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
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
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
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $bon_servicios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$vigant = $vigencia - 1;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_licenciasnr`.`id_empleado`
                , SUM(`nom_liq_licnr`.`dias_licnr`) AS `tot_dias`
            FROM
                `nom_liq_licnr`
                INNER JOIN `nom_licenciasnr` 
                    ON (`nom_liq_licnr`.`id_licnr` = `nom_licenciasnr`.`id_licnr`)
            WHERE (`nom_liq_licnr`.`id_nomina` IN 
                (SELECT `t`.`id_nomina` FROM 
                    (SELECT
                        `id_nomina`
                        , `tipo`
                        , DATE_FORMAT(CONCAT_WS('-',`vigencia`,`mes`, '01'),'%Y-%m-%d') AS fecha
                    FROM
                        `nom_nominas`
                    WHERE `tipo` = 'N' AND `id_nomina`) AS `t`
                WHERE `t`.`fecha` BETWEEN '$vigant-07-01' AND '$vigencia-06-30'))
            GROUP BY `nom_licenciasnr`.`id_empleado`";
    // Reemplazar  WHERE `tipo` = 'N' AND `id_nomina`) AS `t`  WHERE `tipo` = 'N' AND `id_nomina` <> 0) AS `t`
    $rs = $cmd->query($sql);
    $lic_noremun = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT   
                `id_empleado`
                , `corte`
            FROM `nom_liq_prima` WHERE `id_liq_prima` IN 
            (SELECT
                MAX(`id_liq_prima`) AS `id_lp`
            FROM
                `nom_liq_prima`
            INNER JOIN `nom_nominas`
                ON (`nom_liq_prima`.`id_nomina` = `nom_nominas`.`id_nomina`)
            WHERE `nom_nominas`.`tipo` = 'PV' OR `nom_nominas`.`tipo` = 'PS' 
            GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $corteprimant = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina` FROM `nom_nominas` WHERE `vigencia` = '$vigencia' AND `tipo` = 'PV'";
    $rs = $cmd->query($sql);
    $id_nom = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$liquidados = 0;
$perido = 1;
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
$key = array_search('8', array_column($val_vig, 'id_concepto'));
$repre = false !== $key ? $val_vig[$key]['valor'] : 0;
$key = array_search('9', array_column($val_vig, 'id_concepto'));
$basalim = false !== $key ? $val_vig[$key]['valor'] : 0;
$gasrep = 0;
$tipo = 'PV';
if (isset($empleados)) {
    //***********   */
    # CONSULTAR NOMINAS
    //************ */
    if (empty($id_nom)) {
        $descripcion = "LIQUIDACIÓN PRIMA DE SERVICIOS";
        $mesreg = '06';
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "INSERT INTO `nom_nominas` (`tipo`, `vigencia`, `descripcion`,`fec_reg`, `mes`) VALUES (?, ?, ?, ?, ?)";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $tipo, PDO::PARAM_STR);
            $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
            $sql->bindParam(3, $descripcion, PDO::PARAM_STR);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $mesreg, PDO::PARAM_INT);
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
        $id_nomina = $id_nom['id_nomina'];
    }
    $corte = date('Y-m-d', strtotime($vigencia . '-06-30'));
    foreach ($empleados as $emp) {
        $sal_integ = $emp['salario_integral'];
        $id = $emp['id_empleado'];
        $salbase = $emp['salario_basico'];

        if ($sal_integ == 0) {
            $key = array_search($id, array_column($primliq, 'id_empleado'));
            if (false === $key) {
                $basetransporte = ($salbase * 0.06) + $salbase;
                $auxt_base = $basetransporte > $smmlv * 2 ? 0 : $auxt;
                $auxali_base = $salbase > $basalim ? 0 : $auxali;
                $gasrep = $emp['representacion'] == 1 ? $repre : 0;
                $key = array_search($id, array_column($corteprimant, 'id_empleado'));
                $corteant = false !== $key ? $corteprimant[$key]['corte'] : $emp['fech_inicio'];
                $diastoprima = calcularDias($corteant, $corte);
                $diastoprima = $diastoprima > 360 ? 360 : $diastoprima;
                $key = array_search($id, array_column($lic_noremun, 'id_empleado'));
                $tot_dlic = false !== $key ? $lic_noremun[$key]['tot_dias'] : 0;
                $diastoprima = $diastoprima - $tot_dlic;
                $diastoprima = $diastoprima < 0 ? 0 : $diastoprima;
                $key = array_search($id, array_column($bon_servicios, 'id_empleado'));
                $bspant = false !== $key ? $bon_servicios[$key]['val_bsp'] : 0;
                //prima de servicios
                $prima_sv_dia = ($salbase + $auxt_base + $auxali_base + $gasrep + $bspant / 12) / 720;
                $prima = $prima_sv_dia * $diastoprima;
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "INSERT INTO `nom_liq_prima` (`id_empleado`, `cant_dias`, `val_liq_ps`, `periodo`, `anio`, `corte`, `fec_reg`, `id_nomina`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $diastoprima, PDO::PARAM_STR);
                    $sql->bindParam(3, $prima, PDO::PARAM_STR);
                    $sql->bindParam(4, $perido, PDO::PARAM_STR);
                    $sql->bindParam(5, $vigencia, PDO::PARAM_STR);
                    $sql->bindParam(6, $corte, PDO::PARAM_STR);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
                    $sql->execute();
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
                    $sql->bindParam(2, $prima, PDO::PARAM_STR);
                    $sql->bindParam(3, $fpag, PDO::PARAM_STR);
                    $sql->bindParam(4, $mpag, PDO::PARAM_STR);
                    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
                    $sql->bindParam(6, $id_nomina, PDO::PARAM_INT);
                    $sql->bindParam(7, $salbase, PDO::PARAM_STR);
                    $sql->execute();
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                // Ingresar valores liquidados
                try {
                    $cero = 0;
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
                        echo $sql->errorInfo()[2] . 'LQS';
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                }
                $liquidados++;
            }
        }
    }
}
if ($liquidados > 0) {
    echo 'ok';
} else {
    echo 'No se liquidó ningún empleado';
}

function calcularDias($fI, $fF)
{
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
    return $dias360;
}
