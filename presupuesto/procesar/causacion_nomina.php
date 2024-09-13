<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$data = explode('|', file_get_contents("php://input"));
$idNomina = $data[0];
$tipo_nomina = $data[1];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`sede_emp`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`tipo_cargo`
                , `nom_liq_dlab_auxt`.`val_liq_dias`
                , `nom_liq_dlab_auxt`.`val_liq_auxt`
                , `nom_liq_dlab_auxt`.`aux_alim`
                , `nom_liq_dlab_auxt`.`g_representa`
                , `nom_liq_dlab_auxt`.`horas_ext`
            FROM
                `nom_liq_dlab_auxt`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_dlab_auxt`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_dlab_auxt`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $sueldoBasico = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$ced = [];
$ced[] = 0;
foreach ($sueldoBasico as $sb) {
    $ced[] = $sb['no_documento'];
}
$cedulas = implode(',', $ced);
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api`, `no_doc` FROM `seg_terceros` WHERE (`no_doc` IN ($cedulas))";
    $rs = $cmd->query($sql);
    $idApi = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_nomina`, `tipo`, `descripcion`, `mes` FROM `nom_nominas` WHERE (`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $infonomina = $rs->fetch(PDO::FETCH_ASSOC);
    $tipo_nomina = $infonomina['tipo'];
    $descripcion = $infonomina['descripcion'];
    $mes = $infonomina['mes'] == '' ? '00' : $infonomina['mes'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_cargo`
                , `nom_liq_segsocial_empdo`.`id_eps`
                , `nom_liq_segsocial_empdo`.`id_arl`
                , `nom_liq_segsocial_empdo`.`id_afp`
                , `nom_afp`.`id_tercero_api` AS `id_tercero_afp`
                , `nom_arl`.`id_tercero_api` AS `id_tercero_arl`
                , `nom_epss`.`id_tercero_api` AS `id_tercero_eps`
                , `nom_liq_segsocial_empdo`.`aporte_salud_emp`
                , `nom_liq_segsocial_empdo`.`aporte_pension_emp`
                , `nom_liq_segsocial_empdo`.`aporte_solidaridad_pensional`
                , `nom_liq_segsocial_empdo`.`porcentaje_ps`
                , `nom_liq_segsocial_empdo`.`aporte_salud_empresa`
                , `nom_liq_segsocial_empdo`.`aporte_pension_empresa`
                , `nom_liq_segsocial_empdo`.`aporte_rieslab`
            FROM
                `nom_liq_segsocial_empdo`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_segsocial_empdo`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_afp` 
                    ON (`nom_liq_segsocial_empdo`.`id_afp` = `nom_afp`.`id_afp`)
                INNER JOIN `nom_arl` 
                    ON (`nom_liq_segsocial_empdo`.`id_arl` = `nom_arl`.`id_arl`)
                INNER JOIN `nom_epss` 
                    ON (`nom_liq_segsocial_empdo`.`id_eps` = `nom_epss`.`id_eps`)
            WHERE (`nom_liq_segsocial_empdo`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $segSocial = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_cargo`
                , `nom_liq_parafiscales`.`val_sena`
                , `nom_liq_parafiscales`.`val_icbf`
                , `nom_liq_parafiscales`.`val_comfam`
            FROM
                `nom_liq_parafiscales`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_parafiscales`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_parafiscales`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $parafiscales = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_indemniza_vac`.`id_empleado`
                , `nom_liq_indemniza_vac`.`val_liq`
                , `nom_liq_indemniza_vac`.`id_nomina`
            FROM
                `nom_liq_indemniza_vac`
                INNER JOIN `nom_indemniza_vac` 
                    ON (`nom_liq_indemniza_vac`.`id_indemnizacion` = `nom_indemniza_vac`.`id_indemniza`)
            WHERE (`nom_liq_indemniza_vac`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $indemnizacion = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `r_admin`, `r_operativo`, `id_tipo` FROM `nom_rel_rubro` WHERE (`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto` FROM `pto_presupuestos` WHERE `id_tipo` = 2 AND `id_vigencia` = $id_vigencia";
    $rs = $cmd->query($sql);
    $pto = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_vacaciones`.`id_empleado`, `nom_liq_vac`.`val_liq`, `nom_liq_vac`.`val_prima_vac`, `nom_liq_vac`.`val_bon_recrea`
            FROM
                `nom_liq_vac`
                INNER JOIN `nom_vacaciones` 
                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
            WHERE (`nom_liq_vac`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `val_bsp`
            FROM
                `nom_liq_bsp`
            WHERE (`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $bsp = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_incapacidad`.`id_empleado`
                , `nom_liq_incap`.`pago_eps`
                , `nom_liq_incap`.`pago_arl`
                , `nom_liq_incap`.`id_nomina`
                , `nom_liq_incap`.`pago_empresa`
                , `nom_liq_incap`.`mes`
                , `nom_liq_incap`.`anios`
                , `nom_liq_incap`.`tipo_liq`
                , `nom_incapacidad`.`id_tipo`
            FROM
                `nom_liq_incap`
                INNER JOIN `nom_incapacidad` 
                    ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
            WHERE (`nom_liq_incap`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_liq_prima_nav`.`val_liq_pv`
                , `nom_liq_prima_nav`.`id_nomina`
            FROM
                `nom_liq_prima_nav`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_prima_nav`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_prima_nav`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $prima_nav = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_liq_prima`.`val_liq_ps`
                , `nom_liq_prima`.`id_nomina`
            FROM
                `nom_liq_prima`
                LEFT JOIN `nom_empleado` 
                    ON (`nom_liq_prima`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_prima`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $prima_sv = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_liq_cesantias`.`val_icesantias`
                , `nom_liq_cesantias`.`val_cesantias`
                , `nom_liq_cesantias`.`id_nomina`
            FROM
                `nom_liq_cesantias`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_cesantias`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_cesantias`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $cesantias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_liq_compesatorio`.`val_compensa`
                , `nom_liq_compesatorio`.`id_nomina`
            FROM
                `nom_liq_compesatorio`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_compesatorio`.`id_empleado` = `nom_empleado`.`id_empleado`)
            WHERE (`nom_liq_compesatorio`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $compensatorios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $sql = "SELECT COUNT(`id_empleado`) FROM `nom_liq_salario`  WHERE `id_nomina` = $idNomina";
    $cantidad_empleados = $cmd->query($sql)->fetchColumn();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$meses = array(
    '00' => '',
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
$id_pto = $pto['id_pto'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $date->format('Y-m-d');
if ($tipo_nomina == 'N') {
    $objeto = 'LIQUIDACIÓN MENSUAL EMPLEADOS, ' . mb_strtoupper($meses[$mes]) . ' DE ' . $vigencia;
} else if ($tipo_nomina == 'PS') {
    $objeto = $descripcion . ' DE EMPLEADOS, NÓMINA No. ' . $idNomina . ' VIGENCIA ' . $vigencia;
}
$iduser = $_SESSION['id_user'];
$fecha2 = $date->format('Y-m-d H:i:s');
//CDP
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_cdp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$cerrado = 2;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `pto_cdp` (`id_pto`, `id_manu`, `fecha`, `objeto`, `id_user_reg`, `fecha_reg`, `estado`) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(3, $fecha, PDO::PARAM_STR);
    $sql->bindParam(4, $objeto, PDO::PARAM_STR);
    $sql->bindParam(5, $iduser, PDO::PARAM_INT);
    $sql->bindParam(6, $fecha2);
    $sql->bindParam(7, $cerrado, PDO::PARAM_INT);
    $sql->execute();
    $id_cdp = $cmd->lastInsertId();
    if (!($id_cdp > 0)) {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//CRP
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api` FROM `seg_terceros` WHERE `no_doc` = " . $_SESSION['nit_emp'];
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch();
    $id_ter_api = !empty($tercero) ? $tercero['id_tercero_api'] : 0;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `pto_crp` (`id_pto`, `id_cdp`, `id_manu`, `fecha`, `objeto`, `id_user_reg`, `fecha_reg`, `estado`, `id_tercero_api`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $id_cdp, PDO::PARAM_INT);
    $sql->bindParam(3, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(4, $fecha, PDO::PARAM_STR);
    $sql->bindParam(5, $objeto, PDO::PARAM_STR);
    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
    $sql->bindParam(7, $fecha2);
    $sql->bindParam(8, $cerrado, PDO::PARAM_INT);
    $sql->bindParam(9, $id_ter_api, PDO::PARAM_INT);
    $sql->execute();
    $id_crp = $cmd->lastInsertId();
    if (!($id_crp > 0)) {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//CDP DETALLES
$contador = 0;
foreach ($sueldoBasico as $sb) {
    $id_empleado = $sb['id_empleado'];
    $key = array_search($id_empleado, array_column($compensatorios, 'id_empleado'));
    $compensa = $key !== false ? $compensatorios[$key]['val_compensa'] : 0;
    $basico = $sb['val_liq_dias'] + $compensa; //1
    $extras = $sb['horas_ext']; //2
    $repre = $sb['g_representa']; //3
    $auxtras = $sb['val_liq_auxt']; //6
    $auxalim = $sb['aux_alim'];
    $id_sede = $sb['sede_emp'];
    $tipoCargo = $sb['tipo_cargo'];
    $doc_empleado = $sb['no_documento'];
    $keyt = array_search($doc_empleado, array_column($idApi, 'no_doc'));
    $id_tercero = $keyt !== false ? $idApi[$keyt]['id_tercero_api'] : NULL;
    $restar = 0;
    $rest = 0;
    //administrativos
    $contador++;
    $keypf = array_search($id_empleado, array_column($parafiscales, 'id_empleado'));
    $keyss = array_search($id_empleado, array_column($segSocial, 'id_empleado'));
    try {
        $liberado = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `pto_cdp_detalle` (`id_pto_cdp`, `id_rubro`, `valor`, `valor_liberado`) 
                    VALUES (?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_cdp, PDO::PARAM_INT);
        $query->bindParam(2, $rubro, PDO::PARAM_INT);
        $query->bindParam(3, $valor, PDO::PARAM_STR);
        $query->bindParam(4, $liberado, PDO::PARAM_STR);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $con = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sqly = "INSERT INTO `pto_crp_detalle` (`id_pto_crp`, `id_pto_cdp_det`, `id_tercero_api`, `valor`, `valor_liberado`) 
                    VALUES (?, ?, ?, ?, ?)";
        $sqly = $con->prepare($sqly);
        $sqly->bindParam(1, $id_crp, PDO::PARAM_INT);
        $sqly->bindParam(2, $id_detalle_cdp, PDO::PARAM_INT);
        $sqly->bindParam(3, $id_tercero, PDO::PARAM_INT);
        $sqly->bindParam(4, $valor, PDO::PARAM_STR);
        $sqly->bindParam(5, $liberado, PDO::PARAM_STR);
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    foreach ($rubros as $rb) {
        $tipo = $rb['id_tipo'];
        if ($tipoCargo == '1') {
            $rubro = $rb['r_admin'];
        } else {
            $rubro = $rb['r_operativo'];
        }
        $valor = 0;
        switch ($tipo) {
            case 1:
                $valor = $basico;
                break;
            case 2:
                $valor = $extras;
                break;
            case 3:
                $valor = $repre;
                break;
            case 4:
                $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                $valor = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                break;
            case 5:
                $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                $valor = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                break;
            case 6:
                $valor = $auxtras;
                break;
            case 7:
                $valor = $auxalim;
                break;
            case 9:
                $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                $valor = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                break;
            case 17:
                $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                $valor = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                break;
            case 18:
                $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                $valor = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                break;
            case 19:
                $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                $valor = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                break;
            case 20:
                $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                $valor = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                break;
            case 21:
                $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                $valor = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                break;
            case 22:
                $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                $valor = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                break;
            case 32:
                $valor = 0;
                $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                if ($key !== false) {
                    $filtro = [];
                    $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                        return $incapacidades["id_empleado"] == $id_empleado;
                    });
                    foreach ($filtro as $f) {
                        $valor += $f['pago_empresa'];
                    }
                }
                break;
            default:
                $valor = 0;
                break;
        }
        if ($valor > 0 && $rubro != '') {
            $query->execute();
            $id_detalle_cdp = $cmd->lastInsertId();
            if ($id_detalle_cdp > 0) {
                $sqly->execute();
                if (!($con->lastInsertId() > 0)) {
                    echo $sqly->errorInfo()[2];
                }
            } else {
                echo $query->errorInfo()[2];
            }
        }
    }
}
$cmd = null;
$con = null;
try {
    $estado = 3;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `nom_nominas` SET `estado` = ? WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $idNomina, PDO::PARAM_INT);
    $sql->execute();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "INSERT INTO `nom_nomina_pto_ctb_tes` (`id_nomina`, `cdp`, `crp`, `tipo`) 
                VALUES (?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $idNomina, PDO::PARAM_INT);
    $query->bindParam(2, $id_cdp, PDO::PARAM_INT);
    $query->bindParam(3, $id_crp, PDO::PARAM_INT);
    $query->bindParam(4, $tipo_nomina, PDO::PARAM_STR);
    $query->execute();
    if (!($cmd->lastInsertId() > 0)) {
        echo $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo 'ok';
