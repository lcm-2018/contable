<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
include '../../conexion.php';
include '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$id_nomina = $_POST['id'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT  `razon_social_ips`, `nit_ips`, `dv` FROM `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                `nom_nominas`.`id_nomina`
                , `nom_nominas`.`descripcion`
                , `nom_nominas`.`mes`, `vigencia`
                , `nom_nominas`.`tipo`
                , `nom_nominas`.`estado`
                , `nom_nominas`.`id_user_reg`
                , CONCAT_WS(' ', `seg_usuarios_sistema`.`nombre1`
                , `seg_usuarios_sistema`.`nombre2`
                , `seg_usuarios_sistema`.`apellido1`
                , `seg_usuarios_sistema`.`apellido2`) AS `usuario`
            FROM
                `nom_nominas`
                LEFT JOIN `seg_usuarios_sistema` 
                    ON (`nom_nominas`.`id_user_reg` = `seg_usuarios_sistema`.`id_usuario`)            
            WHERE (`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $nomina = $rs->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios_sistema`
            WHERE (`id_usuario` = $nomina[id_user_reg])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT  
                `nom_empleado`.`id_empleado`
                ,`nom_empleado`.`sede_emp`
                , `nom_empleado`.`tipo_doc`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`genero`
                ,  CONCAT_WS(' ', `nom_empleado`.`nombre1`
                , `nom_empleado`.`nombre2`
                , `nom_empleado`.`apellido1`
                , `nom_empleado`.`apellido2`) AS `nombre`
                , `nom_empleado`.`representacion`
                , `nom_empleado`.`estado`
                , `nom_liq_salario`.`id_nomina`
                , `nom_liq_salario`.`sal_base` AS `salario_basico`
                , `nom_cargo_empleado`.`descripcion_carg` AS `cargo`
                , `tb_sedes`.`nom_sede` AS `sede`
            FROM `nom_empleado`
                INNER JOIN `nom_liq_salario` 
                    ON (`nom_liq_salario`.`id_empleado` = `nom_empleado`.`id_empleado`)
                LEFT JOIN `nom_cargo_empleado` 
                    ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
                LEFT JOIN `tb_sedes` 
                    ON (`nom_empleado`.`sede_emp` = `tb_sedes`.`id_sede`)
            WHERE `nom_liq_salario`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $obj = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
            `tb_vigencias`.`anio`
                , `nom_valxvigencia`.`valor`
                , `nom_valxvigencia`.`id_concepto`
            FROM
                `nom_valxvigencia`
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE `id_concepto` = 8 AND `anio` = '$vigencia' LIMIT 1";
    $rs = $cmd->query($sql);
    $grepre = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `mes`, `anios`, `dias_liq`, `pago_empresa`, `pago_eps`, `pago_arl`
            FROM
                `nom_liq_incap`
            INNER JOIN `nom_incapacidad` 
                ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
            WHERE `nom_liq_incap`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `mes_lic`, `anio_lic`, `dias_liqs`, `val_liq`
            FROM
                `nom_liq_licmp`
            INNER JOIN `nom_licenciasmp` 
                ON (`nom_liq_licmp`.`id_licmp` = `nom_licenciasmp`.`id_licmp`)
            WHERE `nom_liq_licmp`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $lic = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_licenciasnr`.`id_empleado`
                , `nom_liq_licnr`.`dias_licnr`
            FROM
                `nom_liq_licnr`
                INNER JOIN `nom_licenciasnr` 
                    ON (`nom_liq_licnr`.`id_licnr` = `nom_licenciasnr`.`id_licnr`)
            WHERE `nom_liq_licnr`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $licnr = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_licencia_luto`.`id_empleado`
                , `nom_liq_licluto`.`dias_licluto`
                , `nom_liq_licluto`.`val_liq`
                , `nom_liq_licluto`.`id_nomina`
            FROM
                `nom_liq_licluto`
                INNER JOIN `nom_licencia_luto` 
                    ON (`nom_liq_licluto`.`id_licluto` = `nom_licencia_luto`.`id_licluto`)
            WHERE `nom_liq_licluto`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $licluto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `mes_vac`, `anio_vac`, `dias_liqs`, `val_liq`,`val_prima_vac`,`val_bon_recrea`
            FROM
                `nom_liq_vac`
            INNER JOIN `nom_vacaciones`
                ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
            WHERE `nom_liq_vac`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_liq, anio_liq, dias_liq, val_liq_dias, val_liq_auxt, aux_alim, g_representa
            FROM
                nom_liq_dlab_auxt
            WHERE nom_liq_dlab_auxt.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $dlab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_liq_prestaciones_sociales
            WHERE nom_liq_prestaciones_sociales.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM
                nom_liq_segsocial_empdo
            WHERE nom_liq_segsocial_empdo.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_mes_lib
            FROM
                nom_liq_libranza
            INNER JOIN nom_libranzas 
                ON (nom_liq_libranza.id_libranza = nom_libranzas.id_libranza)
            WHERE nom_liq_libranza.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_mes_embargo
            FROM
                nom_liq_embargo
            INNER JOIN nom_embargos
                ON (nom_liq_embargo.id_embargo = nom_embargos.id_embargo)
            WHERE nom_liq_embargo.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_aporte
            FROM
                nom_liq_sindicato_aportes
            INNER JOIN nom_cuota_sindical
                ON (nom_liq_sindicato_aportes.id_cuota_sindical = nom_cuota_sindical.id_cuota_sindical)
            WHERE nom_liq_sindicato_aportes.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, SUM(val_liq) AS tot_he
            FROM
                (SELECT id_empleado,val_liq, mes_he, anio_he
                FROM
                    nom_liq_horex
                INNER JOIN nom_horas_ex_trab 
                    ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
                WHERE nom_liq_horex.id_nomina = $id_nomina) AS t
            GROUP BY id_empleado";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_liq, fec_reg
            FROM nom_liq_salario
            WHERE nom_liq_salario.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $saln = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_liq_parafiscales
            WHERE nom_liq_parafiscales.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $pfis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_rte_fte, id_empleado, val_ret, mes, anio
            FROM
                nom_retencion_fte
            WHERE nom_retencion_fte.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $retfte = $rs->fetchAll();
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
                `nom_liq_indemniza_vac`.`id_liq`
                , `nom_indemniza_vac`.`cant_dias`
                , `nom_indemniza_vac`.`id_empleado`
                , `nom_liq_indemniza_vac`.`val_liq`
            FROM
                `nom_liq_indemniza_vac`
                INNER JOIN `nom_indemniza_vac` 
                    ON (`nom_liq_indemniza_vac`.`id_indemnizacion` = `nom_indemniza_vac`.`id_indemniza`)
            WHERE (`nom_liq_indemniza_vac`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $indemnizaciones = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                , `nom_liq_prima_nav`.`cant_dias`
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
                , `nom_liq_prima`.`cant_dias`
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
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_otros_descuentos`.`id_empleado`
                , SUM(`nom_liq_descuento`.`valor`) AS `valor`
            FROM
                `nom_liq_descuento`
                INNER JOIN `nom_otros_descuentos` 
                    ON (`nom_liq_descuento`.`id_dcto` = `nom_otros_descuentos`.`id_dcto`)
            WHERE (`nom_liq_descuento`.`id_nomina` = $id_nomina)
            GROUP BY `nom_otros_descuentos`.`id_empleado`";
    $rs = $cmd->query($sql);
    $descuentos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$fecha = date('Y-m-d', strtotime($nomina['vigencia'] . '-' . $nomina['mes'] . '-01'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `fin_maestro_doc`.`control_doc`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
                , `tb_terceros`.`genero`
                , `fin_respon_doc`.`cargo`
                , `fin_respon_doc`.`tipo_control`
                , `fin_tipo_control`.`descripcion` AS `nom_control`
                , `fin_respon_doc`.`fecha_ini`
                , `fin_respon_doc`.`fecha_fin`
            FROM
                `fin_respon_doc`
                INNER JOIN `fin_maestro_doc` 
                    ON (`fin_respon_doc`.`id_maestro_doc` = `fin_maestro_doc`.`id_maestro`)
                INNER JOIN `tb_terceros` 
                    ON (`fin_respon_doc`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
                INNER JOIN `fin_tipo_control` 
                    ON (`fin_respon_doc`.`tipo_control` = `fin_tipo_control`.`id_tipo`)
            WHERE (`fin_maestro_doc`.`id_modulo` = 51 
                AND `fin_respon_doc`.`fecha_fin` >= '$fecha' 
                AND `fin_respon_doc`.`fecha_ini` <= '$fecha'
                AND `fin_respon_doc`.`estado` = 1
                AND `fin_maestro_doc`.`estado` = 1)";
    $res = $cmd->query($sql);
    $responsables = $res->fetchAll(PDO::FETCH_ASSOC);
    $key = array_search('4', array_column($responsables, 'tipo_control'));
    $nom_respon = $key !== false ? $responsables[$key]['nom_tercero'] : '';
    $cargo_respon = $key !== false ? $responsables[$key]['cargo'] : '';
    $gen_respon = $key !== false ? $responsables[$key]['genero'] : '';
    $control = isset($responsables[0]['control_doc']) ? $responsables[0]['control_doc'] : '';
    $control = $control == '' || $control == '0' ? false : true;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
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
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$iduser = $_SESSION['id_user'];
$logo = $_SERVER['HTTP_HOST'] . $_SESSION['urlin'] . '/images/logos/logo.png';
?>
<div class="text-right py-3">
    <?php if (PermisosUsuario($permisos, 5115, 6) || $id_rol == 1) { ?>
        <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
        </a>
        <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"> Imprimir</a>
    <?php } ?>
    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
</div>
<div class="contenedor bg-light" id="areaImprimir">

    <head>
        <style>
            @media print {
                .page_break_avoid {
                    page-break-inside: avoid;
                }

                @page {
                    size: A4 landscape;
                    margin: 0.8cm;
                }

                @page: right {
                    @bottom-right {
                        content: counter(pagina);
                    }
                }
            }
        </style>
    </head>
    <div class="p-4 text-left">
        <div class="overflow">
            <table class="page_break_avoid" style="width:100% !important; border-collapse: collapse;">
                <thead style="background-color: white !important;">
                    <tr style="padding: bottom 3px; color:black">
                        <td colspan="38">
                            <table style="width:100% !important;">
                                <tr>
                                    <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="../../../images/logos/logo.png" width="100"></label></td>
                                    <td colspan="30" style="text-align:center; font-size: 20px">
                                        <strong><?php echo $empresa['razon_social_ips']; ?> </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="30" style="text-align:center">
                                        NIT <?php echo $empresa['nit_ips'] . '-' . $empresa['dv']; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="30" style="text-align:center">
                                        <b>REPORTE DE LIQUIDACIÓN DE EMPLEADOS</b>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="38" style="text-align: right; font-size: 14px">
                                        Estado: <?php echo $nomina['estado'] == 1 ? 'PARCIAL' : 'DEFINITIVA' ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr style="color: black;font-size:14px;text-align:left">
                        <?php
                        if (isset($meses[$nomina['mes']])) {
                            $texto = $meses[$nomina['mes']];
                        } else {
                            $texto = $nomina['descripcion'];
                        }
                        ?>
                        <th colspan="8">OBJETO: </th>
                        <th colspan="30" style="text-align: left;">PAGO NOMINA N° <?php echo $nomina['id_nomina'] ?>, <?php echo mb_strtoupper($texto) ?> VIGENCIA <?php echo  $nomina['vigencia'] ?>, ADMINISTRATIVO-ASISTENCIAL, <?php echo count($obj) ?> EMPLEADOS ADSCRITOS A <?php echo $empresa['razon_social_ips']; ?></th>
                    </tr>
                    <tr style="color: black;font-size:9px">
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Nombre completo</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">No. Doc.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Sal. Base</th>
                        <th style="border: 1px solid black; " colspan="5" class="text-center centro-vertical">Días</th>
                        <th style="border: 1px solid black; " colspan="5" class="text-center centro-vertical">Valor</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Aux. Transp.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Aux. Alim.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Val. HoEx</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">BSP</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Pri. Vac.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Repre.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Bon. Recrea</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Pri. Serv.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Pri. Nav.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Ces.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">I. Ces.</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">Compen.</th>
                        <th style="border: 1px solid black; " colspan="3" class="text-center centro-vertical">Seguridad Social</th>
                        <th style="border: 1px solid black; " colspan="5" class="text-center centro-vertical">Deducciones</th>
                        <th style="border: 1px solid black; " rowspan="2" class="text-center centro-vertical">NETO</th>
                    </tr>
                    <tr style="color: black;font-size:9px">
                        <th style=" border: 1px solid black; ">Incap.</th>
                        <th style=" border: 1px solid black; ">Lic.</th>
                        <th style=" border: 1px solid black; ">Vac.</th>
                        <th style=" border: 1px solid black; ">Otros</th>
                        <th style=" border: 1px solid black; ">Lab.</th>
                        <th style=" border: 1px solid black; ">Incap.</th>
                        <th style=" border: 1px solid black; ">Lic.</th>
                        <th style=" border: 1px solid black; ">Vac.</th>
                        <th style=" border: 1px solid black; ">Otros</th>
                        <th style=" border: 1px solid black; ">Lab.</th>
                        <th style=" border: 1px solid black; ">Salud</th>
                        <th style=" border: 1px solid black; ">Pensión</th>
                        <th style=" border: 1px solid black; ">Solidaria</th>
                        <th style=" border: 1px solid black; ">Libranza</th>
                        <th style=" border: 1px solid black; ">Embargo</th>
                        <th style=" border: 1px solid black; ">Sindicato</th>
                        <th style=" border: 1px solid black; ">Ret.Fte.</th>
                        <th style=" border: 1px solid black; ">Otros</th>
                    </tr>
                </thead>
                <tbody style=" font-size:9px">
                    <?php
                    $tot_incap = $tot_lic = $tot_vac = $tot_indem = $tot_pordias = $tot_auxtra = $tot_auxalim = $tot_he = $tot_bsps = $tot_prima_vac = $tot_grpre = $tot_bon_recrea = $tot_prim_serv = $tot_prim_nav = $tot_ces = $tot_ices = $tot_comp = $tot_aport_salud = $tot_aport_pension = $tot_aport_solidaridad = $tot_lib = $tot_emb = $tot_sind = $tot_ret = $tot_saln = $tot_otros = 0;
                    foreach ($obj as $o) {
                        $id = $o["id_empleado"];
                        $keysaln = array_search($id, array_column($saln, 'id_empleado'));
                        $status = true;
                        if ($o['estado'] == '0') {
                            if ($keysaln === false) {
                                $status = false;
                            }
                        }
                        if ($status) {
                    ?>
                            <tr>
                                <td style="border: 1px solid black;"> <?php echo str_replace('-', '', mb_strtoupper($o['nombre'])) ?> </td>
                                <td style="border: 1px solid black;"><?php echo $o['no_documento'] ?></td>
                                <td style="border: 1px solid black;" class="text-right"><?php echo pesos($o['salario_basico']) ?></td>
                                <?php
                                $keyincap = array_search($id, array_column($incap, 'id_empleado'));
                                $keylic = array_search($id, array_column($lic, 'id_empleado'));
                                $keylicnr = array_search($id, array_column($licnr, 'id_empleado'));
                                $keyvac = array_search($id, array_column($vac, 'id_empleado'));
                                $keydlab = array_search($id, array_column($dlab, 'id_empleado'));
                                $keypresoc = array_search($id, array_column($presoc, 'id_empleado'));
                                $keysegsoc = array_search($id, array_column($segsoc, 'id_empleado'));
                                $keyemb = array_search($id, array_column($emb, 'id_empleado'));
                                $keysind = array_search($id, array_column($sind, 'id_empleado'));
                                $keyhoex = array_search($id, array_column($hoex, 'id_empleado'));
                                $keypfis = array_search($id, array_column($pfis, 'id_empleado'));
                                $keybsp = array_search($id, array_column($bsp, 'id_empleado'));
                                $keyIndem = array_search($id, array_column($indemnizaciones, 'id_empleado'));
                                $keypn = array_search($id, array_column($prima_nav, 'id_empleado'));
                                $keyps = array_search($id, array_column($prima_sv, 'id_empleado'));
                                $keyces = array_search($id, array_column($cesantias, 'id_empleado'));
                                $keycomp = array_search($id, array_column($compensatorios, 'id_empleado'));
                                $keylicluto = array_search($id, array_column($licluto, 'id_empleado'));
                                if ($keylicluto !== false) {
                                    $dialcluto = $licluto[$keylicluto]['dias_licluto'];
                                    $valluto = $licluto[$keylicluto]['val_liq'];
                                } else {
                                    $dialcluto = 0;
                                    $valluto = 0;
                                }
                                ?>
                                <td style="border: 1px solid black;">
                                    <?php
                                    if (false !== $keyincap) {
                                        $filtro = [];
                                        $filtro = array_filter($incap, function ($incap) use ($id) {
                                            return ($incap['id_empleado'] == $id);
                                        });
                                        foreach ($filtro as $f) {
                                            $dIncap += $f['dias_liq'];
                                        }
                                    } else {
                                        $dIncap = 0;
                                    }
                                    echo $dIncap;
                                    ?>
                                </td>
                                <td style="border: 1px solid black;">
                                    <?php
                                    if (false !== $keylicnr) {
                                        $dialnr = $licnr[$keylicnr]['dias_licnr'] + $dialcluto;
                                    } else {
                                        $dialnr = 0;
                                    }
                                    if (false !== $keylic) {
                                        echo $lic[$keylic]['dias_liqs'] + $dialnr + $dialcluto;
                                    } else {
                                        echo 0 + $dialnr + $dialcluto;
                                    }
                                    ?>
                                </td>
                                <td style="border: 1px solid black;"><?php
                                                                        if (false !== $keyvac) {
                                                                            echo $vac[$keyvac]['dias_liqs'];
                                                                        } else {
                                                                            echo '0';
                                                                        } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if ($nomina['tipo'] == 'PV') {
                                        $dias_psv = false !== $keyps ? $prima_sv[$keyps]['cant_dias'] : 0;
                                        echo $dias_psv;
                                    } else if ($nomina['tipo'] == 'PN') {
                                        $diasPN = false !== $keypn ? $prima_nav[$keypn]['cant_dias'] : 0;
                                        echo $diasPN;
                                    } else {
                                        if (false !== $keyIndem) {
                                            echo $indemnizaciones[$keyIndem]['cant_dias'];
                                            $d2 = $indemnizaciones[$keyIndem]['cant_dias'];
                                        } else {
                                            echo '0';
                                            $d2 = 0;
                                        }
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;"><?php
                                                                        if (false !== $keydlab) {
                                                                            echo $dlab[$keydlab]['dias_liq'];
                                                                        } else {
                                                                            echo '0';
                                                                        } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $a = 0;
                                    if (false !== $keyincap) {
                                        $filtro = [];
                                        $filtro = array_filter($incap, function ($incap) use ($id) {
                                            return ($incap['id_empleado'] == $id);
                                        });
                                        foreach ($filtro as $f) {
                                            $a += $f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl'];
                                        }
                                    }
                                    $tot_incap += $a;
                                    echo pesos($a);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keylic) {
                                        $b = $lic[$keylic]['val_liq'];
                                    } else {
                                        $b = 0;
                                    }
                                    echo pesos($b + $valluto);
                                    $tot_lic += $b + $valluto;
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keyvac) {
                                        echo pesos($vac[$keyvac]['val_liq']);
                                        $c = $vac[$keyvac]['val_liq'];
                                        $tot_vac += $c;
                                    } else {
                                        echo '$0.00';
                                        $c = 0;
                                    } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keyIndem) {
                                        echo pesos($indemnizaciones[$keyIndem]['val_liq']);
                                        $d1 = $indemnizaciones[$keyIndem]['val_liq'];
                                        $tot_indem += $d1;
                                    } else {
                                        echo '$0.00';
                                        $d1 = 0;
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keydlab) {
                                        $d = $dlab[$keydlab]['val_liq_dias'];
                                    } else {
                                        $d = 0;
                                    }
                                    $d = $d - $valluto;
                                    echo pesos($d);
                                    $tot_pordias += $d;
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keydlab) {
                                        echo pesos($dlab[$keydlab]['val_liq_auxt']);
                                        $e1 = $dlab[$keydlab]['val_liq_auxt'];
                                        $tot_auxtra += $e1;
                                    } else {
                                        echo '$0.00';
                                        $e1 = 0;
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keydlab) {
                                        echo pesos($dlab[$keydlab]['aux_alim']);
                                        $e = $dlab[$keydlab]['aux_alim'];
                                        $tot_auxalim += $e;
                                    } else {
                                        echo '$0.00';
                                        $e = 0;
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keyhoex) {
                                        echo pesos($hoex[$keyhoex]['tot_he']);
                                        $f = $hoex[$keyhoex]['tot_he'];
                                        $tot_he += $f;
                                    } else {
                                        echo '$0.00';
                                        $f = 0;
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keybsp) {
                                        echo pesos($bsp[$keybsp]['val_bsp']);
                                        $c3 = $bsp[$keybsp]['val_bsp'];
                                        $tot_bsps += $c3;
                                    } else {
                                        echo '$0.00';
                                        $c3 = 0;
                                    } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keyvac) {
                                        echo pesos($vac[$keyvac]['val_prima_vac']);
                                        $c4 = $vac[$keyvac]['val_prima_vac'];
                                        $tot_prima_vac += $c4;
                                    } else {
                                        echo '$0.00';
                                        $c4 = 0;
                                    } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keydlab) {
                                        $cgrp = $dlab[$keydlab]['g_representa'];
                                        $tot_grpre += $cgrp;
                                    }
                                    echo pesos($cgrp);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keyvac) {
                                        echo pesos($vac[$keyvac]['val_bon_recrea']);
                                        $c5 = $vac[$keyvac]['val_bon_recrea'];
                                        $tot_bon_recrea += $c5;
                                    } else {
                                        echo '$0.00';
                                        $c5 = 0;
                                    } ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $ps = false !== $keyps ? $prima_sv[$keyps]['val_liq_ps'] : 0;
                                    $tot_prim_serv += $ps;
                                    echo pesos($ps);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $pn = false !== $keypn ? $prima_nav[$keypn]['val_liq_pv'] : 0;
                                    $tot_prim_nav += $pn;
                                    echo pesos($pn);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $ces = false !== $keyces ? $cesantias[$keyces]['val_cesantias'] : 0;
                                    $tot_ces += $ces;
                                    echo pesos($ces);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $ices = false !== $keyces ? $cesantias[$keyces]['val_icesantias'] : 0;
                                    $tot_ices += $ices;
                                    echo pesos($ices);
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $comp = false !== $keycomp ? $compensatorios[$keycomp]['val_compensa'] : 0;
                                    $tot_comp += $comp;
                                    echo pesos($comp);
                                    ?>
                                </td>
                                <?php
                                if (false !== $keysegsoc) {
                                    $g = $segsoc[$keysegsoc]['aporte_salud_emp'];
                                    $i = $segsoc[$keysegsoc]['aporte_pension_emp'];
                                    $j = $segsoc[$keysegsoc]['aporte_solidaridad_pensional'];
                                    $tot_aport_salud += $g;
                                    $tot_aport_pension += $i;
                                    $tot_aport_solidaridad += $j;
                                } else {
                                    $g = '0';
                                    $ge = '0';
                                    $rl = '0';
                                    $i = '0';
                                    $ie = '0';
                                    $j = '0';
                                } ?>
                                <td style="border: 1px solid black;" class="text-right"><?php echo pesos($g); ?></td>
                                <td style="border: 1px solid black;" class="text-right"><?php echo pesos($i); ?></td>
                                <td style="border: 1px solid black;" class="text-right"><?php echo pesos($j); ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $k = 0;
                                    foreach ($lib as $lb) {
                                        if ($lb['id_empleado'] == $id) {
                                            $k += $lb['val_mes_lib'];
                                        }
                                    }
                                    echo pesos($k);
                                    $tot_lib += $k;
                                    ?></td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $l = 0;
                                    foreach ($emb as $em) {
                                        if ($em['id_empleado'] == $id) {
                                            $l += $em['val_mes_embargo'];
                                        }
                                    }
                                    echo pesos($l);
                                    $tot_emb += $l;
                                    ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    if (false !== $keysind) {
                                        echo pesos($sind[$keysind]['val_aporte']);
                                        $m = $sind[$keysind]['val_aporte'];
                                        $tot_sind += $m;
                                    } else {
                                        echo '$0.00';
                                        $m = 0;
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $n = 0;
                                    $keyretfte = array_search($id, array_column($retfte, 'id_empleado'));
                                    if (false !== $keyretfte) {
                                        echo pesos($retfte[$keyretfte]['val_ret']);
                                        $n = $retfte[$keyretfte]['val_ret'];
                                        $tot_ret += $n;
                                    } else {
                                        echo '$0.00';
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $nda = 0;
                                    $key_dcto = array_search($id, array_column($descuentos, 'id_empleado'));
                                    if (false !== $key_dcto) {
                                        echo pesos($descuentos[$key_dcto]['valor']);
                                        $nda = $descuentos[$key_dcto]['valor'];
                                        $tot_otros += $nda;
                                    } else {
                                        echo '$0.00';
                                    } ?>
                                </td>
                                <td style="border: 1px solid black;" class="text-right">
                                    <?php
                                    $deducido = $g + $i + $j + $k + $l + $m + $n + $nda;
                                    $devengado = $valluto + $a + $b + $c + $d1 + $d + $e + $e1 + $f + $c3 + $c4 + $cgrp + $c5 + $ps + $pn + $ces + $ices + $comp;
                                    $netop = $devengado - $deducido;
                                    $tot_saln += $netop;
                                    echo pesos($netop);
                                    ?>
                                </td>
                            </tr>
                    <?php
                        }
                    }
                    ?>
                    <tr>
                        <th colspan="8" style="border: 1px solid black;" class="text-right">TOTAL</th>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_incap) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_lic) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_vac) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_indem) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_pordias) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_auxtra) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_auxalim) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_he) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_bsps) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_prima_vac) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_grpre) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_bon_recrea) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_prim_serv) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_prim_nav) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_ces) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_ices) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_comp) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_aport_salud) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_aport_pension) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_aport_solidaridad) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_lib) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_emb) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_sind) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_ret) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_otros) ?></td>
                        <td style="border: 1px solid black;" class="text-right"><?php echo pesos($tot_saln) ?></td>
                    </tr>
                    <tr>
                        <td colspan="38" style="padding: 30px;"></td>
                    </tr>
                    <tr style="font-size: 14px;">
            </table>
            </br>
            </br>
            <table style="width: 100%;">
                <tr>
                    <td style="text-align: center">
                        <div>___________________________________</div>
                        <div><?php echo $nom_respon; ?> </div>
                        <div><?php echo $cargo_respon; ?> </div>
                    </td>
            </table>
            </br> </br> </br>
            <?php
            if ($control) {
            ?>
                <table class="table-bordered bg-light" style="width:100% !important;font-size: 10px;">
                    <tr style="text-align:left">
                        <td style="width:33%">
                            <strong>Elaboró:</strong>
                        </td>
                        <td style="width:33%">
                            <strong>Revisó:</strong>
                        </td>
                        <td style="width:33%">
                            <strong>Aprobó:</strong>
                        </td>
                    </tr>
                    <tr style="text-align:center">
                        <td>
                            <?= trim($nomina['usuario']) ?>
                        </td>
                        <td>
                            <br>
                            <br>
                            <?php
                            $key = array_search('2', array_column($responsables, 'tipo_control'));
                            $nombre = $key !== false ? $responsables[$key]['nom_tercero'] : '';
                            $cargo = $key !== false ? $responsables[$key]['cargo'] : '';
                            echo $nombre . '<br> ' . $cargo;
                            ?>
                        </td>
                        <td>
                            <br>
                            <br>
                            <?php
                            $key = array_search('3', array_column($responsables, 'tipo_control'));
                            $nombre = $key !== false ? $responsables[$key]['nom_tercero'] : '';
                            $cargo = $key !== false ? $responsables[$key]['cargo'] : '';
                            echo $nombre . '<br> ' . $cargo;
                            ?>
                        </td>
                    </tr>
                </table>
            <?php
            }
            ?>
            </tr>
            </tbody>
            <tfoot style="background-color: white !important; font-size: 10px !important;">
                <tr>
                    <td colspan="38">
                        <br>Fecha Imp: <?php echo $date->format('Y-m-d H:m:s') . ' CRONHIS' ?>
                    </td>
                </tr>
            </tfoot>
            </table>
        </div>
    </div>

</div>