<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}

function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
include '../../conexion.php';
$ids = isset($_POST['empleado']) ? $_POST['empleado'] : exit('No se seleccionó ningún empleado');
$empleados = [];
$vacaciones = [];
foreach ($ids as $emp => $vac) {
    $empleados[] = $emp;
    $vacaciones[] = $vac;
}
$ids_emp = implode(',', $empleados);
$ids_vac = implode(',', $vacaciones);
$vigencia = $_SESSION['vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `nom_valxvigencia`.`id_concepto`
                , `nom_valxvigencia`.`valor`
            FROM
                `nom_valxvigencia`
            INNER JOIN `nom_conceptosxvigencia` 
                ON (`nom_valxvigencia`.`id_concepto` = `nom_conceptosxvigencia`.`id_concp`)
            INNER JOIN `tb_vigencias` 
                ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE anio = '$vigencia';";
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

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `tb_tipos_documento`.`codigo_ne`
                , `tb_tipos_documento`.`descripcion`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`fech_inicio`
                , CONCAT_WS(' ',`nom_empleado`.`nombre1`, `nom_empleado`.`nombre2`, `nom_empleado`.`apellido1`, `nom_empleado`.`apellido2`) AS `nombre`
                , `nom_vacaciones`.`fec_inicial`
                , `nom_vacaciones`.`corte`
                , `nom_vacaciones`.`fec_inicio`
                , `nom_vacaciones`.`fec_fin`
                , `nom_vacaciones`.`dias_inactivo`
                , `nom_vacaciones`.`dias_habiles`
                , `nom_vacaciones`.`dias_liquidar`
                , `nom_empleado`.`id_empleado`
                , `nom_empleado`.`representacion`
                , `nom_vacaciones`.`id_vac`
            FROM
                `nom_vacaciones`
                INNER JOIN `nom_empleado` 
                    ON (`nom_vacaciones`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `tb_tipos_documento` 
                    ON (`nom_empleado`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE (`nom_vacaciones`.`id_vac` IN ($ids_vac))";
    $res = $cmd->query($sql);
    $datos = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `salario_basico`
            FROM
                `nom_salarios_basico`
            WHERE  `id_salario` = (SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `id_empleado` IN ($ids_emp))";
    $res = $cmd->query($sql);
    $salario = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT   
                `id_empleado`
                , `corte`
                , SUM(`val_liq_ps`) AS `val_liq_ps`
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
                WHERE `nom_nominas`.`tipo` = 'RA' AND `nom_nominas`.`vigencia` = '$vigencia'
                GROUP BY `id_empleado`)
            GROUP BY `id_empleado`";
    $res = $cmd->query($sql);
    $prima = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
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
    $res = $cmd->query($sql);
    $bpserv = $res->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_eps`
            FROM
                `nom_novedades_eps`
            WHERE `id_novedad`  IN (SELECT MAX(`id_novedad`) FROM `nom_novedades_eps` WHERE `id_empleado` IN ($ids_emp) GROUP BY `id_empleado`)";
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
            WHERE `id_novarl`  IN (SELECT MAX(`id_novarl`) FROM `nom_novedades_arl` WHERE `id_empleado` IN ($ids_emp) GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $arl = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, `id_afp`
            FROM
                `nom_novedades_afp`
            WHERE `id_novafp`  IN (SELECT MAX(`id_novafp`) FROM `nom_novedades_afp` WHERE `id_empleado` IN ($ids_emp) GROUP BY `id_empleado`)";
    $rs = $cmd->query($sql);
    $afp = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$meses = [
    'enero',
    'febrero',
    'marzo',
    'abril',
    'mayo',
    'junio',
    'julio',
    'agosto',
    'septiembre',
    'octubre',
    'noviembre',
    'diciembre'
];
$dossml = $smmlv * 2;
$descripcion = 'LIQUIDACIÓN DE VACACIONES';
$tipo = 'VC';
$mes = date('m');
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `nom_nominas` (`mes`, `vigencia`, `descripcion`, `fec_reg`, `tipo`, `id_user_reg`) VALUES (?, ?, ?, ?, ?,?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $mes, PDO::PARAM_STR);
    $sql->bindParam(2, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(3, $descripcion, PDO::PARAM_STR);
    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(5, $tipo, PDO::PARAM_STR);
    $sql->bindParam(6, $id_user, PDO::PARAM_INT);
    $sql->execute();
    if (!($cmd->lastInsertId() > 0)) {
        echo $sql->errorInfo()[2] . 'NOM';
        exit();
    } else {
        $id_nomina = $cmd->lastInsertId();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$lqs = 0;
foreach ($datos as $d) {
    $id_empleado = $d['id_empleado'];
    $id_vacacion = $d['id_vac'];
    $key = array_search($id_empleado, array_column($arl, 'id_empleado'));
    $id_arl = false !== $key ? $arl[$key]['id_arl'] : null;
    $key = array_search($id_empleado, array_column($eps, 'id_empleado'));
    $id_eps = false !== $key ? $eps[$key]['id_eps'] : null;
    $key = array_search($id_empleado, array_column($afp, 'id_empleado'));
    $id_afp = false !== $key ? $afp[$key]['id_afp'] : null;
    $key = array_search($id_empleado, array_column($salario, 'id_empleado'));
    $salbase = $key !== false ? $salario[$key]['salario_basico'] : 0;
    if ($salbase <= $dossml) {
        $auxt = $auxiliotranporte / 30;
    } else {
        $auxt = 0;
    }

    if ($salbase <= $basealim) {
        $auxali = $auxalim / 30;
    } else {
        $auxali = 0;
    }
    $grepresenta = $d['representacion'];
    if ($grepresenta == 1) {
        $gasrep = $representacion;
    } else {
        $gasrep = 0;
    }
    $dayvac = $d['dias_inactivo'];
    $dayhab = $d['dias_habiles'];
    $diastocalc = $d['dias_liquidar'];
    //prima de servicios
    $key = array_search($id_empleado, array_column($prima, 'id_empleado'));
    $primservicio = $key !== false ? $prima[$key]['val_liq_ps'] : 0;
    //bonificacion de servicios prestados
    $key = array_search($id_empleado, array_column($bpserv, 'id_empleado'));
    $bsp = $key !== false ? $bpserv[$key]['val_bsp'] : 0;
    //prima de vacaciones
    $primvacacion  = (($salbase + $gasrep + $auxt + $auxali + $bsp / 12 + $primservicio / 12) * $dayhab) / 30;
    $primavacn = ($primvacacion / 360) * $diastocalc;
    //liquidacion vacaciones
    $liqvacacion  = (($salbase + $gasrep + $auxt + $auxali + $bsp / 12 + $primservicio / 12) * $dayvac) / 30;
    $vacacion = ($liqvacacion / 360) * $diastocalc;
    $bonrecrea = ($salbase / 30) * 2;
    $bonrecreacion = ($bonrecrea / 360) * $diastocalc;
    $bonserpres = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_liq_vac`
                        (`id_vac`, `dias_liqs`, `val_liq`, `val_bsp`, `val_prima_vac`, `val_bon_recrea`, `mes_vac`, `anio_vac`, `fec_reg`,`id_nomina`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_vacacion, PDO::PARAM_INT);
        $sql->bindParam(2, $dayvac, PDO::PARAM_INT);
        $sql->bindParam(3, $vacacion, PDO::PARAM_STR);
        $sql->bindParam(4, $bonserpres, PDO::PARAM_STR);
        $sql->bindParam(5, $primavacn, PDO::PARAM_STR);
        $sql->bindParam(6, $bonrecreacion, PDO::PARAM_STR);
        $sql->bindParam(7, $mes, PDO::PARAM_STR);
        $sql->bindParam(8, $vigencia, PDO::PARAM_STR);
        $sql->bindValue(9, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(10, $id_nomina, PDO::PARAM_INT);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $estado = 2;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `nom_vacaciones` SET  `estado` = ? WHERE `id_vac` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $id_vacacion, PDO::PARAM_INT);
            $sql->execute();
        } else {
            echo $sql->errorInfo()[2] . 'VAC';
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $base_ss = ($salbase / 30) * $dayvac;
    $saludempleado = 0;
    $pensionempleado = 0;
    $saludempresa = $base_ss * 0.125;
    $pensionempresa = $base_ss * 0.16;

    $semp = redondeo($saludempleado, 0);
    $pemp = redondeo($pensionempleado, 0);
    $solidpension = 0;
    $porcenps = 0;
    $stotal = redondeo($saludempresa + $saludempleado, -2);
    $ptotal = redondeo($pensionempresa + $pensionempleado, -2);
    $rieslab = 0;
    $saludempleado = $semp;
    $pensionempleado = $pemp;
    $saludempresa = $stotal - $semp;
    $pensionempresa = $ptotal - $pemp;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_liq_segsocial_empdo` (`id_empleado`, `id_eps`, `id_arl`, `id_afp`, `aporte_salud_emp`, `aporte_pension_emp`, `aporte_solidaridad_pensional`, `porcentaje_ps`, `aporte_salud_empresa`, `aporte_pension_empresa`, `aporte_rieslab`, `mes`, `anio`, `fec_reg`, `id_nomina`)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
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
        $sql->bindParam(13, $vigencia, PDO::PARAM_STR);
        $sql->bindValue(14, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(15, $id_nomina, PDO::PARAM_INT);
        $sql->execute();
        if (!($cmd->lastInsertId() > 0)) {
            echo $sql->errorInfo()[2] . 'SS';
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $liqpfisc = $vacacion + $primavacn + $gasrep + $auxt + $auxali;
    $sena = redondeo(($liqpfisc * 0.02), -2);
    $icbf = redondeo(($liqpfisc * 0.03), -2);
    $comfam = redondeo(($liqpfisc * 0.04), -2);
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_liq_parafiscales` (`id_empleado`, `val_sena`, `val_icbf`, `val_comfam`, `mes_pfis`, `anio_pfis`, `fec_reg`, `id_nomina`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
        $sql->bindParam(2, $sena, PDO::PARAM_STR);
        $sql->bindParam(3, $icbf, PDO::PARAM_STR);
        $sql->bindParam(4, $comfam, PDO::PARAM_STR);
        $sql->bindParam(5, $mes, PDO::PARAM_STR);
        $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
        $sql->execute();
        if (!($cmd->lastInsertId() > 0)) {
            echo $sql->errorInfo()[2] . 'PFIS';
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $cero = 0;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_liq_dlab_auxt` 
                    (`id_empleado`, `dias_liq`, `val_liq_dias`, `val_liq_auxt`,`aux_alim`,`g_representa`,`horas_ext`, `mes_liq`, `anio_liq`, `fec_reg`, `id_nomina`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
        $sql->bindParam(2, $cero, PDO::PARAM_INT);
        $sql->bindParam(3, $cero, PDO::PARAM_STR);
        $sql->bindParam(4, $cero, PDO::PARAM_STR);
        $sql->bindParam(5, $cero, PDO::PARAM_STR);
        $sql->bindParam(6, $cero, PDO::PARAM_STR);
        $sql->bindParam(7, $cero, PDO::PARAM_STR);
        $sql->bindParam(8, $mes, PDO::PARAM_STR);
        $sql->bindParam(9, $vigencia, PDO::PARAM_STR);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(11, $id_nomina, PDO::PARAM_INT);
        $sql->execute();
        if (!($cmd->lastInsertId() > 0)) {
            echo $sql->errorInfo()[2] . 'LAB';
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    $salarioneto = $vacacion + $primavacn + $bonrecreacion - $saludempleado - $pensionempleado - $solidpension;
    $salarioneto = $salarioneto < 0 ? 0 : $salarioneto;
    $fpag = 1;
    $mpag = 42;
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `nom_liq_salario` (`id_empleado`, `val_liq`, `forma_pago`, `metodo_pago`, `mes`, `anio`, `fec_reg`, `id_nomina`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_empleado, PDO::PARAM_INT);
        $sql->bindParam(2, $salarioneto, PDO::PARAM_STR);
        $sql->bindParam(3, $fpag, PDO::PARAM_STR);
        $sql->bindParam(4, $mpag, PDO::PARAM_STR);
        $sql->bindParam(5, $mes, PDO::PARAM_STR);
        $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
        $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(8, $id_nomina, PDO::PARAM_INT);
        $sql->execute();
        if (!($cmd->lastInsertId() > 0)) {
            echo $sql->errorInfo()[2] . 'NETO';
        } else {
            $lqs++;
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}

function redondeo($value, $places)
{
    $mult = pow(10, abs($places));
    return $places < 0 ? ceil($value / $mult) * $mult : ceil($value * $mult) / $mult;
}

if ($lqs > 0) {
    echo 'ok';
} else {
    echo 'No se liquidó ningun empleado';
}
