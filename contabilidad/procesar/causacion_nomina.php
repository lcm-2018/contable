<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$data = explode(',', file_get_contents("php://input"));
$id_nomina = $data[0];
$crp = $data[1];
$tipo_nomina = $data[2];
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
                , `ccostos`.`id_ccosto`
                , `t`.`id_tercero_api`
            FROM
                `nom_liq_dlab_auxt`
                INNER JOIN `nom_empleado` 
                    ON (`nom_liq_dlab_auxt`.`id_empleado` = `nom_empleado`.`id_empleado`)
                LEFT JOIN 
                    (SELECT
                        MAX(`id_ccosto`) AS `id_ccosto`
                        , `id_empleado`
                    FROM
                        `nom_ccosto_empleado`
                    GROUP BY `id_empleado`) AS `ccostos`
                    ON (`nom_liq_dlab_auxt`.`id_empleado` = `ccostos`.`id_empleado`)
                LEFT JOIN `tb_terceros` AS `t` 
                    ON (`nom_empleado`.`no_documento` = `t`.`nit_tercero`)
            WHERE (`nom_liq_dlab_auxt`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sueldoBasico = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_nomina`, `tipo`, `descripcion`, `mes` FROM `nom_nominas` WHERE (`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_segsocial_empdo`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_parafiscales`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_embargo`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_libranza`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_sindicato_aportes`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sindicato = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE (`nom_liq_indemniza_vac`.`id_nomina` = $id_nomina)";
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
            FROM
                `nom_rel_rubro`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_rel_rubro`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
            WHERE (`nom_rel_rubro`.`id_vigencia` = $id_vigencia)";
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
                , `tb_centrocostos`.`es_pasivo`
                FROM
                    `nom_causacion`
                INNER JOIN `nom_tipo_rubro` 
                    ON (`nom_causacion`.`id_tipo` = `nom_tipo_rubro`.`id_rubro`)
                INNER JOIN `tb_centrocostos`
                    ON (`nom_causacion`.`centro_costo` = `tb_centrocostos`.`id_centro`)";
    $rs = $cmd->query($sql);
    $cuentas_causacion = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `ctb_retencion_rango`.`id_rango`
            FROM 
                `nom_causacion` 
                INNER JOIN `tb_centrocostos` 
                    ON (`nom_causacion`.`centro_costo` = `tb_centrocostos`.`id_centro`)
                INNER JOIN `ctb_retenciones` 
                    ON (`ctb_retenciones`.`id_cuenta` = `nom_causacion`.`cuenta`)
                INNER JOIN `ctb_retencion_rango` 
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
            WHERE (`tb_centrocostos`.`es_pasivo` = 1 AND `nom_causacion`.`id_tipo` = 30 AND `ctb_retencion_rango`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $rango = $rs->fetch(PDO::FETCH_ASSOC);
    $rango = !empty($rango) ? $rango['id_rango'] : exit('No se encontró el rango de retención  en la fuente para la causación de nómina');
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado` , `val_ret`, `base` FROM `nom_retencion_fte` WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $rfte = $rs->fetchAll(PDO::FETCH_ASSOC);
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
            WHERE (`nom_liq_vac`.`id_nomina` = $id_nomina)";
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
            WHERE (`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_incap`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_prima_nav`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_prima`.`id_nomina` = $id_nomina)";
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
            WHERE (`nom_liq_cesantias`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $cesantias = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($tipo_nomina == 'CE' || $tipo_nomina == 'IC') {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT
                    SUM(`nom_liq_cesantias`.`val_cesantias`) AS `val_cesantias`
                    , SUM(`nom_liq_cesantias`.`val_icesantias`) AS `val_icesantias`
                    , `nom_fondo_censan`.`id_tercero_api`
                FROM
                    `nom_liq_cesantias`
                    INNER JOIN `nom_novedades_fc` 
                        ON (`nom_liq_cesantias`.`id_empleado` = `nom_novedades_fc`.`id_empleado`)
                    INNER JOIN `nom_fondo_censan` 
                        ON (`nom_novedades_fc`.`id_fc` = `nom_fondo_censan`.`id_fc`)
                WHERE (`nom_liq_cesantias`.`id_nomina` =  $id_nomina)
                GROUP BY `nom_fondo_censan`.`id_tercero_api`";
        $rs = $cmd->query($sql);
        $cesantias2 = $rs->fetchAll(PDO::FETCH_ASSOC);
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
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
            WHERE (`nom_liq_compesatorio`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $compensatorios = $rs->fetchAll(PDO::FETCH_ASSOC);
    $sql = "SELECT COUNT(`id_empleado`) FROM `nom_liq_salario`  WHERE `id_nomina` = $id_nomina";
    $cantidad_empleados = $cmd->query($sql)->fetchColumn();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_liq_descuento`.`valor`
                , `nom_tipo_descuentos`.`id_cuenta`
                , `nom_otros_descuentos`.`id_empleado`
            FROM
                `nom_liq_descuento`
                INNER JOIN `nom_otros_descuentos` 
                    ON (`nom_liq_descuento`.`id_dcto` = `nom_otros_descuentos`.`id_dcto`)
                INNER JOIN `nom_tipo_descuentos` 
                    ON (`nom_otros_descuentos`.`id_tipo_dcto` = `nom_tipo_descuentos`.`id_tipo`)
            WHERE (`nom_liq_descuento`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetchAll(PDO::FETCH_ASSOC);
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $data[3];
if ($tipo_nomina == 'N') {
    $objeto = 'LIQUIDACIÓN MENSUAL EMPLEADOS, ' . mb_strtoupper($meses[$mes]) . ' DE ' . $vigencia;
} else if ($tipo_nomina == 'PS') {
    $objeto = $descripcion . ' DE EMPLEADOS, NÓMINA No. ' . $id_nomina . ' VIGENCIA ' . $vigencia;
}
$iduser = $_SESSION['id_user'];
$fecha2 = $date->format('Y-m-d H:i:s');
//CNOM = 5
$cnom = 5;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT MAX(`id_manu`) AS `id_manu` FROM `ctb_doc` WHERE `id_vigencia` = $id_vigencia AND `id_tipo_doc` = $cnom";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tercero_api` FROM `tb_terceros` WHERE `nit_tercero` = " . $_SESSION['nit_emp'];
    $rs = $cmd->query($sql);
    $tercero = $rs->fetch();
    $id_ter_api = !empty($tercero) ? $tercero['id_tercero_api'] : NULL;
    $id_ter_api = count($sueldoBasico) == 1 ? $sueldoBasico[0]['id_tercero_api'] : $id_ter_api;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_crp_detalle`.`id_pto_crp_det`
                , `pto_cdp_detalle`.`id_rubro`
                , `pto_crp_detalle`.`id_tercero_api`
            FROM
                `pto_crp_detalle`
                INNER JOIN `pto_cdp_detalle` 
                    ON (`pto_crp_detalle`.`id_pto_cdp_det` = `pto_cdp_detalle`.`id_pto_cdp_det`)
            WHERE (`pto_crp_detalle`.`id_pto_crp` = $crp)";
    $rs = $cmd->query($sql);
    $ids_detalle = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $estado = 2;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "INSERT INTO `ctb_doc` 
                (`id_vigencia`, `id_tipo_doc`, `id_manu`,`id_tercero`, `fecha`, `detalle`, `id_user_reg`, `fecha_reg`, `estado`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_vigencia, PDO::PARAM_INT);
    $query->bindParam(2, $cnom, PDO::PARAM_STR);
    $query->bindParam(3, $id_manu, PDO::PARAM_INT);
    $query->bindParam(4, $id_ter_api, PDO::PARAM_INT);
    $query->bindParam(5, $fecha, PDO::PARAM_STR);
    $query->bindParam(6, $objeto, PDO::PARAM_STR);
    $query->bindParam(7, $iduser, PDO::PARAM_INT);
    $query->bindParam(8, $fecha2);
    $query->bindParam(9, $estado, PDO::PARAM_INT);
    $query->execute();
    $id_doc_nom = $cmd->lastInsertId();
    if (!($cmd->lastInsertId() > 0)) {
        echo $query->errorInfo()[2];
        exit();
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$con_ces = 0;

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
    $ccosto = $sb['id_ccosto'] == '' ? 21 : $sb['id_ccosto'];
    $doc_empleado = $sb['no_documento'];
    $id_ter_api = $sb['id_tercero_api'];
    $restar = 0;
    $rest = 0;
    $liberado = 0;
    //administrativos
    $keypf = array_search($id_empleado, array_column($parafiscales, 'id_empleado'));
    $keyss = array_search($id_empleado, array_column($segSocial, 'id_empleado'));
    if ($_SESSION['pto'] == '1') {
        try {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $query = "INSERT INTO `pto_cop_detalle`
                    (`id_ctb_doc`, `id_pto_crp_det`,`id_tercero_api`,`valor`,`valor_liberado`,`id_user_reg`,`fecha_reg`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
            $query->bindParam(2, $id_det, PDO::PARAM_INT);
            $query->bindParam(3, $id_ter_api, PDO::PARAM_INT);
            $query->bindParam(4, $valor, PDO::PARAM_STR);
            $query->bindParam(5, $liberado, PDO::PARAM_STR);
            $query->bindParam(6, $iduser, PDO::PARAM_INT);
            $query->bindParam(7, $fecha2, PDO::PARAM_STR);
            foreach ($rubros as $rb) {
                $tipo = $rb['id_tipo'];
                if ($tipoCargo == '1') {
                    $rubro = $rb['r_admin'];
                } else {
                    $rubro = $rb['r_operativo'];
                }
                $valor = 0;
                $id_det = NULL;
                foreach ($ids_detalle as $detalle) {
                    if ($detalle['id_rubro'] == $rubro && $detalle['id_tercero_api'] == $id_ter_api) {
                        $id_det = $detalle['id_pto_crp_det'];
                        break;
                    }
                }
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
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                        exit();
                    }
                }
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    }
    try {
        $credito = 0;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `ctb_libaux` (`id_ctb_doc`,`id_tercero_api`,`id_cuenta`,`debito`,`credito`,`id_user_reg`,`fecha_reg`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
        $query->bindParam(2, $id_ter_api, PDO::PARAM_INT);
        $query->bindParam(3, $cuenta, PDO::PARAM_STR);
        $query->bindParam(4, $valor, PDO::PARAM_STR);
        $query->bindParam(5, $credito, PDO::PARAM_STR);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);

        $sql1 = "INSERT INTO `ctb_causa_retencion`
                    (`id_ctb_doc`,`id_rango`,`valor_base`,`tarifa`,`valor_retencion`,`id_terceroapi`,`id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $sql1 = $cmd->prepare($sql1);
        $sql1->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
        $sql1->bindParam(2, $rango, PDO::PARAM_INT);
        $sql1->bindParam(3, $base, PDO::PARAM_STR);
        $sql1->bindValue(4, 0, PDO::PARAM_STR);
        $sql1->bindParam(5, $valor_retencion, PDO::PARAM_STR);
        $sql1->bindParam(6, $id_ter_api, PDO::PARAM_INT);
        $sql1->bindParam(7, $iduser, PDO::PARAM_INT);
        $sql1->bindParam(8, $fecha2);

        $filtro = [];
        $filtro = array_filter($cuentas_causacion, function ($cuentas_causacion) use ($ccosto) {
            return $cuentas_causacion["centro_costo"] == $ccosto;
        });
        foreach ($filtro as $ca) {
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

        $cPasivo = [];
        $cPasivo = array_filter($cuentas_causacion, function ($cuentas_causacion) use ($ccosto) {
            return $cuentas_causacion["es_pasivo"] == 1;
        });
        $key_dcto = array_search($id_empleado, array_column($descuentos, 'id_empleado'));
        $dcto = [];
        if ($key_dcto !== false) {
            $dcto = array_filter($descuentos, function ($descuentos) use ($id_empleado) {
                return $descuentos["id_empleado"] == $id_empleado;
            });
        }
        if (($tipo_nomina == 'CE' || $tipo_nomina == 'IC')) {
            if ($con_ces == 0) {
                $cPasivo = array_values($cPasivo);
                foreach ($cesantias2 as $ces) {
                    $valor = 0;
                    $credito = 0;
                    $key = array_search(18, array_column($cPasivo, 'id_tipo'));
                    if ($key !== false) {
                        $cuenta = $cPasivo[$key]['cuenta'];
                        $credito = $ces['val_cesantias'];
                        $id_ter_api = $ces['id_tercero_api'];
                        if ($credito > 0 && $cuenta != '') {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                                exit();
                            }
                        }
                    }
                    $key = array_search(19, array_column($cPasivo, 'id_tipo'));
                    if ($key !== false) {
                        $cuenta = $cPasivo[$key]['cuenta'];
                        $credito = $ces['val_icesantias'];
                        $id_ter_api = $ces['id_tercero_api'];
                        if ($credito > 0 && $cuenta != '') {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                                exit();
                            }
                        }
                    }
                }
            }
            $con_ces = 1;
        } else {
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
                        if ($key !== false && $valRteFte > 0) {
                            $base = $rfte[$key]['base'];
                            $valor_retencion = $rfte[$key]['val_ret'];
                            $sql1->execute();
                        }
                        $val_dcto = 0;
                        if (!empty($dcto)) {
                            foreach ($dcto as $d) {
                                $val_dcto += $d['valor'];
                            }
                        }
                        $sgs = $keyss !== false ? ($segSocial[$keyss]['aporte_pension_emp'] + $segSocial[$keyss]['aporte_solidaridad_pensional'] + $segSocial[$keyss]['aporte_salud_emp']) : 0;
                        $credito = $basico + $extras + $repre + $auxtras + $auxalim - ($sgs + $valSind + $valLib + $valEmb + $valRteFte + $val_dcto);
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
                        $sgs = $keyss !== false ? $segSocial[$keyss]['aporte_pension_emp'] + $segSocial[$keyss]['aporte_solidaridad_pensional'] : 0;
                        $credito = $sgs;
                        break;
                    case 25:
                        $sgs = $keyss !== false ? $segSocial[$keyss]['aporte_salud_emp'] : 0;
                        $credito = $sgs;
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
                    case 33:
                        if (!empty($dcto)) {
                            foreach ($dcto as $dc) {
                                $credito = $dc['valor'];
                                $cuenta = $dc['id_cuenta'];
                                if ($credito > 0 && $cuenta != '') {
                                    $query->execute();
                                    if (!($cmd->lastInsertId() > 0)) {
                                        echo $query->errorInfo()[2];
                                        exit();
                                    }
                                }
                            }
                        }
                        $credito = 0;
                        break;
                    default:
                        $credito = 0;
                        break;
                }
                if ($credito > 0 && $cuenta != '') {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                        exit();
                    }
                }
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $estado = 4;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `nom_nominas` SET `estado` = ? WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $id_nomina, PDO::PARAM_INT);
    $sql->execute();
    if (!($sql->rowCount() > 0)) {
        echo $sql->errorInfo()[2];
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "UPDATE `nom_nomina_pto_ctb_tes` SET `cnom` = ? WHERE `id_nomina` = ? AND `tipo` = ? AND `crp`  = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
    $query->bindParam(2, $id_nomina, PDO::PARAM_INT);
    $query->bindParam(3, $tipo_nomina, PDO::PARAM_STR);
    $query->bindParam(4, $crp, PDO::PARAM_INT);
    $query->execute();
    if (!($query->rowCount() > 0)) {
        echo $query->errorInfo()[2];
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo 'ok';
