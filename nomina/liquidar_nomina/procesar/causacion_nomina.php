<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$mes = $_POST['mes'];
$idNomina = $_POST['id'];
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
    $sql = "SELECT `id_nomina`, `tipo`, `descripcion` FROM `nom_nominas` WHERE (`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $infonomina = $rs->fetch(PDO::FETCH_ASSOC);
    $tipo_nomina = $infonomina['tipo'];
    $descripcion = $infonomina['descripcion'];
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
                , `nom_horas_ex_trab`.`id_he`
                , `nom_liq_horex`.`val_liq`
            FROM
                `nom_horas_ex_trab`
                INNER JOIN `nom_empleado` 
                    ON (`nom_horas_ex_trab`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_liq_horex` 
                    ON (`nom_liq_horex`.`id_he_lab` = `nom_horas_ex_trab`.`id_he_trab`)
            WHERE (`nom_liq_horex`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $horas = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_cargo`
                , `nom_liq_embargo`.`val_mes_embargo`
            FROM
                `nom_embargos`
                INNER JOIN `nom_empleado` 
                    ON (`nom_embargos`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_liq_embargo` 
                    ON (`nom_liq_embargo`.`id_embargo` = `nom_embargos`.`id_embargo`)
            WHERE (`nom_liq_embargo`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $embargos = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                , `nom_liq_libranza`.`val_mes_lib`
                , `nom_liq_libranza`.`mes_lib`
                , `nom_liq_libranza`.`anio_lib`
            FROM
                `nom_libranzas`
                INNER JOIN `nom_empleado` 
                    ON (`nom_libranzas`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_liq_libranza` 
                    ON (`nom_liq_libranza`.`id_libranza` = `nom_libranzas`.`id_libranza`)
            WHERE (`nom_liq_libranza`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $libranzas = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                , `nom_liq_sindicato_aportes`.`val_aporte`
            FROM
                `nom_cuota_sindical`
                INNER JOIN `nom_empleado` 
                    ON (`nom_cuota_sindical`.`id_empleado` = `nom_empleado`.`id_empleado`)
                INNER JOIN `nom_liq_sindicato_aportes` 
                    ON (`nom_liq_sindicato_aportes`.`id_cuota_sindical` = `nom_cuota_sindical`.`id_cuota_sindical`)
            WHERE (`nom_liq_sindicato_aportes`.`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $sindicato = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado` , `val_liq` FROM `nom_liq_salario` WHERE (`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $salario = $rs->fetchAll(PDO::FETCH_ASSOC);
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
    $sql = "SELECT
                `nom_tipo_rubro`.`id_rubro`
                , `nom_rel_rubro`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_rel_rubro`.`r_admin`
                , `nom_rel_rubro`.`r_operativo`
                , `nom_rel_rubro`.`vigencia`
            FROM
                `nom_rel_rubro`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_rel_rubro`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
            WHERE (`nom_rel_rubro`.`vigencia` = '$vigencia')";
    $rs = $cmd->query($sql);
    $rubros = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_causacion`.`id_causacion`
                , `nom_causacion`.`centro_costo`
                , `nom_causacion`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_causacion`.`cuenta`
                , `nom_causacion`.`detalle`
            FROM
                `nom_causacion`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
            WHERE `nom_causacion`.`centro_costo` = 'ADMIN'";
    $rs = $cmd->query($sql);
    $cAdmin = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_causacion`.`id_causacion`
                , `nom_causacion`.`centro_costo`
                , `nom_causacion`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_causacion`.`cuenta`
                , `nom_causacion`.`detalle`
            FROM
                `nom_causacion`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
            WHERE `nom_causacion`.`centro_costo` = 'URG'";
    $rs = $cmd->query($sql);
    $cUrg = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_causacion`.`id_causacion`
                , `nom_causacion`.`centro_costo`
                , `nom_causacion`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_causacion`.`cuenta`
                , `nom_causacion`.`detalle`
            FROM
                `nom_causacion`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
            WHERE `nom_causacion`.`centro_costo` = 'PASIVO'";
    $rs = $cmd->query($sql);
    $cPasivo = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado` , `val_ret` FROM `nom_retencion_fte` WHERE (`id_nomina` = $idNomina)";
    $rs = $cmd->query($sql);
    $rfte = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tes_cuenta`, `cta_contable` FROM `tes_cuentas` WHERE (`id_tes_cuenta` = 21)";
    $rs = $cmd->query($sql);
    $banco = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_pto_presupuestos` , `id_pto_tipo` FROM `pto_presupuestos` WHERE `id_pto_tipo` =2";
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
$id_pto = $pto['id_pto_presupuestos'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $date->format('Y-m-d');
if ($tipo_nomina == 'N') {
    $objeto = 'LIQUIDACIÓN MENSUAL EMPLEADOS, ' . mb_strtoupper($meses[$mes]) . ' DE ' . $vigencia;
} else if ($tipo_nomina == 'PS') {
    $objeto = $descripcion . ' DE EMPLEADOS, NÓMINA No. ' . $idNomina . ' VIGENCIA ' . $vigencia;
}
$sede = 1;
$iduser = $_SESSION['id_user'];
$fecha2 = $date->format('Y-m-d H:i:s');
//CDP
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) as `id_manu`, `tipo_doc`, `id_pto_presupuestos`
            FROM
                `pto_documento`
            WHERE (`tipo_doc` ='CDP'
                AND `id_pto_presupuestos` = $id_pto)";
    $rs = $cmd->query($sql);
    $id_m = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_manu = $id_m['id_manu'] + 1;
$tipo_doc = 'CDP';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `pto_documento` (`id_pto_presupuestos`,`id_sede`, `tipo_doc`, `id_manu`, `fecha`, `objeto`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $sede, PDO::PARAM_INT);
    $sql->bindParam(3, $tipo_doc, PDO::PARAM_STR);
    $sql->bindParam(4, $id_manu, PDO::PARAM_STR);
    $sql->bindParam(5, $fecha, PDO::PARAM_STR);
    $sql->bindParam(6, $objeto, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindParam(8, $fecha2);
    $sql->execute();
    $id_doc = $cmd->lastInsertId();
    if (!($cmd->lastInsertId() > 0)) {
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
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu`, `tipo_doc`, `id_pto_presupuestos`
            FROM
                `pto_documento`
            WHERE (`tipo_doc` ='CRP'
                AND `id_pto_presupuestos` = $id_pto)";
    $rs = $cmd->query($sql);
    $id_m = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_manu = $id_m['id_manu'] + 1;
$tipo_doc = 'CRP';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `pto_documento` (`id_pto_presupuestos`,`id_sede`, `tipo_doc`, `id_manu`, `fecha`, `objeto`, `id_user_reg`, `fec_reg`,`id_auto`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $sede, PDO::PARAM_INT);
    $sql->bindParam(3, $tipo_doc, PDO::PARAM_STR);
    $sql->bindParam(4, $id_manu, PDO::PARAM_STR);
    $sql->bindParam(5, $fecha, PDO::PARAM_STR);
    $sql->bindParam(6, $objeto, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindParam(8, $fecha2);
    $sql->bindParam(9, $id_doc, PDO::PARAM_INT);
    $sql->execute();
    $id_doc_crp = $cmd->lastInsertId();
    if (!($cmd->lastInsertId() > 0)) {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$contador = 0;
//CNOM
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`id_manu`) as `id_manu` FROM `ctb_doc` WHERE (`vigencia`= '$vigencia' AND `tipo_doc` ='CNOM')";
    $rs = $cmd->query($sql);
    $id_m = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_manu = $id_m['id_manu'] + 1;
$tipo_doc = 'CNOM';
$id_tercero = 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $cmd->prepare("INSERT INTO `ctb_doc` (`vigencia`, `tipo_doc`, `id_manu`,`id_tercero`, `fecha`, `detalle`, `id_user_reg`, `fec_reg`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $query->bindParam(1, $vigencia, PDO::PARAM_INT);
    $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
    $query->bindParam(3, $id_manu, PDO::PARAM_INT);
    $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(5, $fecha, PDO::PARAM_STR);
    $query->bindParam(6, $objeto, PDO::PARAM_STR);
    $query->bindParam(7, $iduser, PDO::PARAM_INT);
    $query->bindParam(8, $fecha2);
    $query->execute();
    $id_doc_nom = $cmd->lastInsertId();
    if (!($cmd->lastInsertId() > 0)) {
        echo $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//CEVA
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`id_manu`) as `id_manu` FROM `ctb_doc` WHERE (`vigencia`= '$vigencia' AND `tipo_doc` ='CEVA')";
    $rs = $cmd->query($sql);
    $id_m = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_manu = $id_m['id_manu'] + 1;
$tipo_doc = 'CEVA';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $cmd->prepare("INSERT INTO `ctb_doc` (`vigencia`, `tipo_doc`, `id_manu`,`id_tercero`, `fecha`, `detalle`, `id_user_reg`, `fec_reg`)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $query->bindParam(1, $vigencia, PDO::PARAM_INT);
    $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
    $query->bindParam(3, $id_manu, PDO::PARAM_INT);
    $query->bindParam(4, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(5, $fecha, PDO::PARAM_STR);
    $query->bindParam(6, $objeto, PDO::PARAM_STR);
    $query->bindParam(7, $iduser, PDO::PARAM_INT);
    $query->bindParam(8, $fecha2);
    $query->execute();
    $id_doc_ceva = $cmd->lastInsertId();
    if (!($cmd->lastInsertId() > 0)) {
        echo $query->errorInfo()[2];
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
        $tipo_mov = 'CDP';
        $estado = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `pto_documento_detalles` (`id_pto_doc`, `tipo_mov`, `id_tercero_api`, `rubro`, `valor`,`estado`) 
                    VALUES (?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(4, $rubro, PDO::PARAM_STR);
        $query->bindParam(5, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            if ($tipoCargo == '1') {
                $rubro = $rb['r_admin'];
            } else {
                $rubro = $rb['r_operativo'];
            }
            $valorCdp = 0;
            switch ($tipo) {
                case 1:
                    $valorCdp = $basico;
                    break;
                case 2:
                    $valorCdp = $extras;
                    break;
                case 3:
                    $valorCdp = $repre;
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $valorCdp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 6:
                    $valorCdp = $auxtras;
                    break;
                case 7:
                    $valorCdp = $auxalim;
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $valorCdp = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                    break;
                case 18:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                    break;
                case 19:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                    break;
                case 20:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                    break;
                case 21:
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                    break;
                case 32:
                    $valorCdp = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            $valorCdp += $f['pago_empresa'];
                        }
                    }
                    break;
                default:
                    $valorCdp = 0;
                    break;
            }
            if ($valorCdp > 0 && $rubro != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $tipo_mov = 'CRP';
        $estado = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `pto_documento_detalles` (`id_pto_doc`, `tipo_mov`, `id_tercero_api`, `rubro`, `valor`,`estado`,`id_auto_dep`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_crp, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(4, $rubro, PDO::PARAM_STR);
        $query->bindParam(5, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $id_doc, PDO::PARAM_INT);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            if ($tipoCargo == '1') {
                $rubro = $rb['r_admin'];
            } else {
                $rubro = $rb['r_operativo'];
            }
            $valorCdp = 0;
            switch ($tipo) {
                case 1:
                    $valorCdp = $basico;
                    break;
                case 2:
                    $valorCdp = $extras;
                    break;
                case 3:
                    $valorCdp = $repre;
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $valorCdp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 6:
                    $valorCdp = $auxtras;
                    break;
                case 7:
                    $valorCdp = $auxalim;
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $valorCdp = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                    break;
                case 18:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                    break;
                case 19:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                    break;
                case 20:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                    break;
                case 21:
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                    break;
                case 32:
                    $valorCdp = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            $valorCdp += $f['pago_empresa'];
                        }
                    }
                default:
                    $valorCdp = 0;
                    break;
            }
            if ($valorCdp > 0 && $rubro != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $tipo_mov = 'COP';
        $estado = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `pto_documento_detalles` (`id_pto_doc`, `tipo_mov`, `id_tercero_api`, `rubro`, `valor`,`estado`,`id_auto_dep`,`id_ctb_doc`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_crp, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(4, $rubro, PDO::PARAM_STR);
        $query->bindParam(5, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $id_doc, PDO::PARAM_INT);
        $query->bindParam(8, $id_doc_nom, PDO::PARAM_INT);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            if ($tipoCargo == '1') {
                $rubro = $rb['r_admin'];
            } else {
                $rubro = $rb['r_operativo'];
            }
            $valorCdp = 0;
            switch ($tipo) {
                case 1:
                    $valorCdp = $basico;
                    break;
                case 2:
                    $valorCdp = $extras;
                    break;
                case 3:
                    $valorCdp = $repre;
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $valorCdp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 6:
                    $valorCdp = $auxtras;
                    break;
                case 7:
                    $valorCdp = $auxalim;
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $valorCdp = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                    break;
                case 18:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                    break;
                case 19:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                    break;
                case 20:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                    break;
                case 21:
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                    break;
                case 32:
                    $valorCdp = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            $valorCdp += $f['pago_empresa'];
                        }
                    }
                    break;
                default:
                    $valorCdp = 0;
                    break;
            }
            if ($valorCdp > 0 && $rubro != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $credito = 0;
        $id_cc = 0;
        $id_rte = 0;
        $id_fac = 0;
        $id_tipo_bn_sv = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `ctb_libaux` (`id_ctb_doc`,`id_tercero`,`cuenta`,`debito`,`credito`,`id_sede`,`id_cc`,`id_crp`,`id_rte`,`id_fac`,`id_tipo_ad`,`id_user_reg`,`fec_reg`) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta, PDO::PARAM_STR);
        $query->bindParam(4, $valor, PDO::PARAM_STR);
        $query->bindParam(5, $credito, PDO::PARAM_STR);
        $query->bindParam(6, $id_sede, PDO::PARAM_INT);
        $query->bindParam(7, $id_cc, PDO::PARAM_INT);
        $query->bindParam(8, $id_doc_crp, PDO::PARAM_INT);
        $query->bindParam(9, $id_rte, PDO::PARAM_INT);
        $query->bindParam(10, $id_fac, PDO::PARAM_INT);
        $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
        $query->bindParam(12, $iduser, PDO::PARAM_INT);
        $query->bindParam(13, $fecha2);
        if ($tipoCargo == '1') {
            //administrativos
            foreach ($cAdmin as $ca) {
                $tipo = $ca['id_tipo'];
                $cuenta = $ca['cuenta'];
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
                    case 8:
                        $valor = 0;
                        $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                        if ($key !== false) {
                            $filtro = [];
                            $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                                return $incapacidades["id_empleado"] == $id_empleado;
                            });
                            foreach ($filtro as $f) {
                                if ($f['id_tipo'] == 1) {
                                    $valor += $f['pago_eps'];
                                } else {
                                    $valor += $f['pago_arl'];
                                }
                            }
                        }
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
                if ($valor > 0 && $cuenta != '') {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
            }
        } else {
            //asistenciales u operativos
            foreach ($cUrg as $cu) {
                $tipo = $cu['id_tipo'];
                $cuenta = $cu['cuenta'];
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
                        $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                        $valor = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                        break;
                    case 5:
                        $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                        $valor = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                        break;
                    case 6:
                        $valor = $auxtras;
                        break;
                    case 7:
                        $valor = $auxalim;
                        break;
                    case 8:
                        $valor = 0;
                        $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                        if ($key !== false) {
                            $filtro = [];
                            $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                                return $incapacidades["id_empleado"] == $id_empleado;
                            });
                            foreach ($filtro as $f) {
                                if ($f['id_tipo'] == 1) {
                                    $valor += $f['pago_eps'];
                                } else {
                                    $valor += $f['pago_arl'];
                                }
                            }
                        }
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
                if ($valor > 0 && $cuenta != '') {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
            }
        }
        foreach ($cPasivo as $cp) {
            $valor = 0;
            $tipo = $cp['id_tipo'];
            $cuenta = $cp['cuenta'];
            $credito = 0;
            switch ($tipo) {
                case 1:
                    $key = array_search($id_empleado, array_column($sindicato, 'id_empleado'));
                    $valSind = $key !== false ? $sindicato[$key]['val_aporte'] : 0;
                    $key = array_search($id_empleado, array_column($libranzas, 'id_empleado'));
                    $valLib =  0;
                    if ($key !== false) {
                        foreach ($libranzas as $li) {
                            if ($li['id_empleado'] == $id_empleado) {
                                $valLib += $li['val_mes_lib'];
                            }
                        }
                    }
                    $key = array_search($id_empleado, array_column($embargos, 'id_empleado'));
                    $valEmb = 0;
                    if ($key !== false) {
                        foreach ($embargos as $em) {
                            if ($em['id_empleado'] == $id_empleado) {
                                $valEmb += $em['val_mes_embargo'];
                            }
                        }
                    }
                    $key = array_search($id_empleado, array_column($rfte, 'id_empleado'));
                    $valRteFte = $key !== false ? $rfte[$key]['val_ret'] : 0;
                    $credito = $basico + $extras + $repre + $auxtras + $auxalim - ($segSocial[$keyss]['aporte_pension_emp'] + $segSocial[$keyss]['aporte_solidaridad_pensional'] + $segSocial[$keyss]['aporte_salud_emp'] + $valSind + $valLib + $valEmb + $valRteFte);
                    if ($credito < 0) {
                        $restar = $credito * -1;
                        $credito = 0;
                    } else {
                        $restar = 0;
                    }
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $credito = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $credito = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 8:
                    $credito = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            if ($f['id_tipo'] == 1) {
                                $credito += $f['pago_eps'];
                            } else {
                                $credito += $f['pago_arl'];
                            }
                        }
                        $credito -= $restar;
                        if ($credito < 0) {
                            $restar = $credito * -1;
                            $credito = 0;
                        } else {
                            $restar = 0;
                        }
                    }
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $credito = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $credito = $key !== false ? $vacaciones[$key]['val_liq'] - $restar : 0;
                    break;
                case 18:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $credito = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                    break;
                case 19:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $credito = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                    break;
                case 20:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $credito = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                    if ($credito < 0) {
                        $restar = $credito * -1;
                        $credito = 0;
                    } else {
                        $restar = 0;
                    }
                    break;
                case 21:
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    $credito = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    $credito = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima, 'id_empleado'));
                    $credito = $key !== false ? $prima[$key]['val_liq_ps'] : 0;
                    break;
                case 24:
                    $credito = $segSocial[$keyss]['aporte_pension_emp'] + $segSocial[$keyss]['aporte_solidaridad_pensional'];
                    break;
                case 25:
                    $credito = $segSocial[$keyss]['aporte_salud_emp'];
                    break;
                case 26:
                    $key = array_search($id_empleado, array_column($sindicato, 'id_empleado'));
                    $credito = $key !== false ? $sindicato[$key]['val_aporte'] : 0;
                    break;
                case 28:
                    $key = array_search($id_empleado, array_column($libranzas, 'id_empleado'));
                    $credito =  0;
                    if ($key !== false) {
                        foreach ($libranzas as $li) {
                            if ($li['id_empleado'] == $id_empleado) {
                                $credito += $li['val_mes_lib'];
                            }
                        }
                    }
                    break;
                case 29:
                    $key = array_search($id_empleado, array_column($embargos, 'id_empleado'));
                    $credito = 0;
                    if ($key !== false) {
                        foreach ($embargos as $em) {
                            if ($em['id_empleado'] == $id_empleado) {
                                $credito += $em['val_mes_embargo'];
                            }
                        }
                    }
                    break;
                case 30:
                    $key = array_search($id_empleado, array_column($rfte, 'id_empleado'));
                    $credito = $key !== false ? $rfte[$key]['val_ret'] : 0;
                    break;
                case 32:
                    $credito = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            $credito += $f['pago_empresa'];
                        }
                        $credito -= $restar;
                    }
                    break;
                default:
                    $credito = 0;
                    break;
            }
            if ($credito > 0 && $cuenta != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }

    try {
        $tipo_mov = 'PAG';
        $estado = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `pto_documento_detalles` (`id_pto_doc`, `tipo_mov`, `id_tercero_api`, `rubro`, `valor`,`estado`,`id_auto_dep`,`id_ctb_doc`,`id_ctb_cop`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_crp, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
        $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(4, $rubro, PDO::PARAM_STR);
        $query->bindParam(5, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $id_doc, PDO::PARAM_INT);
        $query->bindParam(8, $id_doc_ceva, PDO::PARAM_INT);
        $query->bindParam(9, $id_doc_nom, PDO::PARAM_INT);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            if ($tipoCargo == '1') {
                $rubro = $rb['r_admin'];
            } else {
                $rubro = $rb['r_operativo'];
            }
            $valorCdp = 0;
            switch ($tipo) {
                case 1:
                    $valorCdp = $basico;
                    break;
                case 2:
                    $valorCdp = $extras;
                    break;
                case 3:
                    $valorCdp = $repre;
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $valorCdp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 6:
                    $valorCdp = $auxtras;
                    break;
                case 7:
                    $valorCdp = $auxalim;
                    break;
                case 8:
                    $valorCdp = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            if ($f['id_tipo'] == 1) {
                                $valorCdp += $f['pago_eps'];
                            } else {
                                $valorCdp += $f['pago_arl'];
                            }
                        }
                    }
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $valorCdp = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_liq'] : 0;
                    break;
                case 18:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
                    break;
                case 19:
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    $valorCdp = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
                    break;
                case 20:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valorCdp = $key !== false ? $vacaciones[$key]['val_prima_vac'] : 0;
                    break;
                case 21:
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
                    break;
                case 22:
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    $valorCdp = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
                    break;
                case 32:
                    $valorCdp = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            $valorCdp += $f['pago_empresa'];
                        }
                    }
                    break;
                default:
                    $valorCdp = 0;
                    break;
            }
            if ($valorCdp > 0 && $rubro != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
    try {
        $id_cc = 0;
        $id_rte = 0;
        $id_fac = 0;
        $id_tipo_bn_sv = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `ctb_libaux` (`id_ctb_doc`,`id_tercero`,`cuenta`,`debito`,`credito`,`id_sede`,`id_cc`,`id_crp`,`id_rte`,`id_fac`,`id_tipo_ad`,`id_user_reg`,`fec_reg`) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_ceva, PDO::PARAM_INT);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta, PDO::PARAM_STR);
        $query->bindParam(4, $valor, PDO::PARAM_STR);
        $query->bindParam(5, $credito, PDO::PARAM_STR);
        $query->bindParam(6, $id_sede, PDO::PARAM_INT);
        $query->bindParam(7, $id_cc, PDO::PARAM_INT);
        $query->bindParam(8, $id_doc_crp, PDO::PARAM_INT);
        $query->bindParam(9, $id_rte, PDO::PARAM_INT);
        $query->bindParam(10, $id_fac, PDO::PARAM_INT);
        $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
        $query->bindParam(12, $iduser, PDO::PARAM_INT);
        $query->bindParam(13, $fecha2);
        $neto = 0;
        foreach ($cPasivo as $cp) {
            $credito = 0;
            $tipo = $cp['id_tipo'];
            $cuenta = $cp['cuenta'];
            $valor = 0;
            switch ($tipo) {
                case 1:
                    $key = array_search($id_empleado, array_column($sindicato, 'id_empleado'));
                    $valSind = $key !== false ? $sindicato[$key]['val_aporte'] : 0;
                    $key = array_search($id_empleado, array_column($libranzas, 'id_empleado'));
                    $valLib =  0;
                    if ($key !== false) {
                        foreach ($libranzas as $li) {
                            if ($li['id_empleado'] == $id_empleado) {
                                $valLib += $li['val_mes_lib'];
                            }
                        }
                    }
                    $key = array_search($id_empleado, array_column($embargos, 'id_empleado'));
                    $valEmb = 0;
                    if ($key !== false) {
                        foreach ($embargos as $em) {
                            if ($em['id_empleado'] == $id_empleado) {
                                $valEmb += $em['val_mes_embargo'];
                            }
                        }
                    }
                    $key = array_search($id_empleado, array_column($rfte, 'id_empleado'));
                    $valRteFte = $key !== false ? $rfte[$key]['val_ret'] : 0;
                    $valor = $basico + $extras + $repre + $auxtras + $auxalim - ($segSocial[$keyss]['aporte_pension_emp'] + $segSocial[$keyss]['aporte_solidaridad_pensional'] + $segSocial[$keyss]['aporte_salud_emp'] + $valSind + $valLib + $valEmb + $valRteFte);
                    if ($valor < 0) {
                        $rest = $valor * -1;
                        $valor = 0;
                    }
                    break;
                case 4:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valor = $key !== false ? $vacaciones[$key]['val_bon_recrea'] : 0;
                    break;
                case 5:
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    $valor = $key !== false ? $bsp[$key]['val_bsp'] : 0;
                    break;
                case 8:
                    $valor = 0;
                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        $filtro = [];
                        $filtro = array_filter($incapacidades, function ($incapacidades) use ($id_empleado) {
                            return $incapacidades["id_empleado"] == $id_empleado;
                        });
                        foreach ($filtro as $f) {
                            if ($f['id_tipo'] == 1) {
                                $valor += $f['pago_eps'];
                            } else {
                                $valor += $f['pago_arl'];
                            }
                        }
                        $valor -= $rest;
                        if ($valor < 0) {
                            $rest = $valor * -1;
                            $valor = 0;
                        } else {
                            $rest = 0;
                        }
                    }

                    $key = array_search($id_empleado, array_column($incapacidades, 'id_empleado'));
                    if ($key !== false) {
                        if ($incapacidades[$key]['id_tipo'] == 1) {
                            $valor = $incapacidades[$key]['pago_eps'] - $rest;
                        } else {
                            $valor = $incapacidades[$key]['pago_arl'] - $rest;
                        }
                        if ($valor < 0) {
                            $rest = $valor * -1;
                            $valor = 0;
                        } else {
                            $rest = 0;
                        }
                    } else {
                        $valor = 0;
                    };
                    break;
                case 9:
                    $key = array_search($id_empleado, array_column($indemnizacion, 'id_empleado'));
                    $valor = $key !== false ? $indemnizacion[$key]['val_liq'] : 0;
                    break;
                case 17:
                    $key = array_search($id_empleado, array_column($vacaciones, 'id_empleado'));
                    $valor = $key !== false ? $vacaciones[$key]['val_liq'] - $rest : 0;
                    if ($valor < 0) {
                        $rest = $valor * -1;
                        $valor = 0;
                    } else {
                        $rest = 0;
                    }
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
                        $valor -= $rest;
                    }
                    break;
                default:
                    $valor = 0;
                    break;
            }
            if ($valor > 0 && $cuenta != '') {
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    echo $query->errorInfo()[2];
                }
            }
            $neto += $valor;
        }
        $valor = 0;
        $credito = $neto;
        $cuenta = $banco['cta_contable'];
        $query->execute();
        if (!($cmd->lastInsertId() > 0)) {
            echo $query->errorInfo()[2];
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $estado = 2;
    $tipo = 'N';
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
echo 'ok';
