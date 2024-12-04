<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
include '../../conexion.php';
include '../../permisos.php';
$vigencia = $_SESSION['vigencia'];
$id_nomina = $_POST['id_nomina'];
$cedula = isset($_POST['cedula']) ? $_POST['cedula'] : 0;
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT  `razon_social_ips` AS`nombre`, `nit_ips` AS `nit`, `dv` AS `dig_ver` FROM `tb_datos_ips`";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $sql = "SELECT
                CONCAT_WS(' ', `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `nombre`
            FROM
                `seg_usuarios_sistema`
            WHERE (`id_usuario` = $_SESSION[id_user])";
    $res = $cmd->query($sql);
    $usuario = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`, `no_documento`, `estado`, CONCAT_WS(' ',  `nombre1`, `nombre2`, `apellido1`, `apellido2`) AS `empleado`, `representacion`
                ,`nom_municipio`, `codigo`, `cargo`, `fech_inicio`, `correo`, `cuenta_bancaria`, `id_banco`, `tipo_cta`, `apellido1`, `apellido2`, `nombre1`, `nombre2`
            FROM
                (SELECT  
                    `nom_empleado`.`id_empleado`
                    , `nom_empleado`.`tipo_doc`
                    , `nom_empleado`.`no_documento`
                    , `nom_empleado`.`genero`
                    , `nom_empleado`.`nombre1`
                    , `nom_empleado`.`nombre2`
                    , `nom_empleado`.`apellido1`
                    , `nom_empleado`.`apellido2`
                    , `nom_empleado`.`representacion`
                    , `nom_empleado`.`estado`
                    , `nom_salarios_basico`.`id_salario`
                    , `nom_salarios_basico`.`vigencia`
                    , `nom_salarios_basico`.`salario_basico`
                    , `nom_liq_salario`.`id_nomina`
                    , `nom_liq_salario`.`anio`
                    , `nom_liq_salario`.`tipo_liq`
                    , `tb_municipios`.`nom_municipio`
                    , `nom_cargo_empleado`.`codigo`
                    , `nom_cargo_empleado`.`descripcion_carg` AS `cargo`
                    , `nom_empleado`.`fech_inicio`
                    , `nom_empleado`.`correo`
                    , `nom_empleado`.`cuenta_bancaria`
                    , `nom_empleado`.`id_banco`
                    , `nom_empleado`.`tipo_cta`
                FROM `nom_salarios_basico`
                    INNER JOIN `nom_empleado`
                        ON(`nom_salarios_basico`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_liq_salario` 
                        ON (`nom_liq_salario`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_cargo_empleado` 
                        ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
                    INNER JOIN `tb_sedes` 
            ON (`nom_empleado`.`sede_emp` = `tb_sedes`.`id_sede`)
            INNER JOIN `tb_municipios` 
                        ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
                WHERE `nom_salarios_basico`.`id_salario`  
                    IN(SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `vigencia` <= '$vigencia' GROUP BY `id_empleado`)) AS t
            WHERE `id_nomina` = $id_nomina
            GROUP BY `id_empleado`
            ORDER BY `nom_municipio`,`no_documento`,`empleado` ASC";
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
                `id_empleado`, `id_nomina`, `dias_liq`, `pago_empresa`, `pago_eps`, `pago_arl`
            FROM
                `nom_liq_incap`
            INNER JOIN `nom_incapacidad` 
                ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $incap = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_empleado`, `dias_liqs`, `val_liq`, `nom_liq_licmp`.`id_nomina`
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
    $sql = "SELECT `id_empleado`, `dias_liqs`, `val_liq`,`val_prima_vac`,`val_bon_recrea`, `nom_liq_vac`.`id_nomina`
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
    $sql = "SELECT id_empleado, mes_liq, anio_liq, dias_liq, val_liq_dias, val_liq_auxt, aux_alim, nom_liq_dlab_auxt.id_nomina
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
                nom_liq_segsocial_empdo
            WHERE id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_liq_libranza`.`id_nomina`
                , `nom_libranzas`.`id_empleado`
                , `nom_liq_libranza`.`val_mes_lib`
                , `tb_bancos`.`nom_banco`
            FROM
                `nom_liq_libranza`
                INNER JOIN `nom_libranzas` 
                    ON (`nom_liq_libranza`.`id_libranza` = `nom_libranzas`.`id_libranza`)
                INNER JOIN `tb_bancos` 
                    ON (`nom_libranzas`.`id_banco` = `tb_bancos`.`id_banco`)
            WHERE (`nom_liq_libranza`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $lib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_liq_embargo`.`id_nomina`
                , `nom_embargos`.`id_empleado`
                , `nom_liq_embargo`.`val_mes_embargo`
                , `nom_juzgados`.`nom_juzgado`
            FROM
                `nom_liq_embargo`
                INNER JOIN `nom_embargos` 
                    ON (`nom_liq_embargo`.`id_embargo` = `nom_embargos`.`id_embargo`)
                INNER JOIN `nom_tipo_embargo` 
                    ON (`nom_embargos`.`tipo_embargo` = `nom_tipo_embargo`.`id_tipo_emb`)
                INNER JOIN `nom_juzgados` 
                    ON (`nom_embargos`.`id_juzgado` = `nom_juzgados`.`id_juzgado`)
            WHERE (`nom_liq_embargo`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_liq_sindicato_aportes`.`id_nomina`
                , `nom_liq_sindicato_aportes`.`val_aporte`
                , `nom_cuota_sindical`.`id_empleado`
                , `nom_sindicatos`.`nom_sindicato`
            FROM
                `nom_liq_sindicato_aportes`
                INNER JOIN `nom_cuota_sindical` 
                    ON (`nom_liq_sindicato_aportes`.`id_cuota_sindical` = `nom_cuota_sindical`.`id_cuota_sindical`)
                INNER JOIN `nom_sindicatos` 
                    ON (`nom_cuota_sindical`.`id_sindicato` = `nom_sindicatos`.`id_sindicato`)
            WHERE (`nom_liq_sindicato_aportes`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_horas_ex_trab`.`id_empleado`
                , `nom_tipo_horaex`.`desc_he`
                , `nom_horas_ex_trab`.`cantidad_he`
                , `nom_liq_horex`.`val_liq`
                , `nom_liq_horex`.`id_nomina`
            FROM
                `nom_liq_horex`
                INNER JOIN `nom_horas_ex_trab` 
                    ON (`nom_liq_horex`.`id_he_lab` = `nom_horas_ex_trab`.`id_he_trab`)
                INNER JOIN `nom_tipo_horaex` 
                    ON (`nom_horas_ex_trab`.`id_he` = `nom_tipo_horaex`.`id_he`)
            WHERE (`nom_liq_horex`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_liq, fec_reg, id_nomina
            FROM nom_liq_salario
            WHERE id_nomina = $id_nomina";
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
            WHERE id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $pfis = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_rte_fte, id_empleado, val_ret, id_nomina, base
            FROM
                nom_retencion_fte
            WHERE id_nomina = $id_nomina";
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
                `id_empleado`, `val_bsp`, `id_nomina`
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
                , `nom_liq_indemniza_vac`.`id_nomina`
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
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_nomina`, `estado`, `planilla`, `mes`, `vigencia`, `tipo` 
            FROM `nom_nominas` WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $nom = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_concepto`, `concepto`
            FROM `nom_conceptos_liquidacion` ORDER BY `concepto` ASC";
    $rs = $cmd->query($sql);
    $conceptos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_banco`, `cod_banco`, `nom_banco` FROM `tb_bancos`";
    $rs = $cmd->query($sql);
    $bancos = $rs->fetchAll(PDO::FETCH_ASSOC);
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
                , `nom_liq_cesantias`.`cant_dias`
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
                , `nom_liq_prima`.`val_liq_ps`
                , `nom_liq_prima`.`id_nomina`
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
                , `nom_liq_prima_nav`.`val_liq_pv`
                , `nom_liq_prima_nav`.`cant_dias`
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
                , `nom_liq_compesatorio`.`val_compensa`
                , `nom_liq_compesatorio`.`id_nomina`
                , `nom_liq_compesatorio`.`dias`
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
$concepto = isset($_POST['concepto']) ? $_POST['concepto'] : 'A';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$meses = [
    '01' => 'ENERO',
    '02' => 'FEBRERO',
    '03' => 'MARZO',
    '04' => 'ABRIL',
    '05' => 'MAYO',
    '06' => 'JUNIO',
    '07' => 'JULIO',
    '08' => 'AGOSTO',
    '09' => 'SEPTIEMBRE',
    '10' => 'OCTUBRE',
    '11' => 'NOVIEMBRE',
    '12' => 'DICIEMBRE'
];
?>
<div class="form-row" py-3>
    <input type="hidden" id="id_nomina" value="<?php echo $id_nomina ?>">
    <div class="form-group col-md-5">
        <label for="concepto" class="small">CONCEPTO LIQUIDADO</label>
        <select class="form-control form-control-sm" id="concepto">
            <option value="A">--Seleccionar--</option>
            <option value="0" <?php echo $concepto == '0' ? 'selected' : '' ?>>TODOS</option>
            <?php foreach ($conceptos as $c) {
                $slc = $c['id_concepto'] == $concepto ? 'selected' : '';
                echo '<option value="' . $c['id_concepto'] . '" ' . $slc . '>' . $c['concepto'] . '</option>';
            } ?>
        </select>
    </div>
    <div class="form-group col-md-1">
        <label for="buscar" class="small">&nbsp;</label>
        <button type="button" class="btn btn-light btn-sm btn-block" id="conceptos_nomina">Filtrar</button>
    </div>
    <div class="form-group col-md-5">
        <label for="buscar" class="small">&nbsp;</label>
        <div class="text-right">
            <?php if (PermisosUsuario($permisos, 5115, 6) || $id_rol == 1) { ?>
                <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
                    <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                </a>
                <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"> Imprimir</a>
            <?php } ?>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cerrar</a>
        </div>
    </div>
</div>
<div class="contenedor bg-light" id="areaImprimir">
    <style>
        .resaltar:nth-child(even) {
            background-color: #F8F9F9;
        }

        .resaltar:nth-child(odd) {
            background-color: #ffffff;
        }
    </style>
    <div class="p-4 text-left">
        <?php
        if ($concepto == 'A') {
            echo '<div class="alert alert-warning text-center" role="alert">
                    <strong>Seleccionar un concepto de liquidación</strong>
                </div>';
            exit();
        }
        $nomes =  $meses[$nom['mes']];
        $emision = $date->format('d/m/Y');
        $encabezadoo = <<<EOT
        <table style="width:100% !important; font-size:10px !important;">
            <tr>
                <td colspan="8">
                    <table style="width:100% !important;">
                        <tr>
                            <td rowspan="3" class="text-center" style="width:18%"><img src="../../images/logos/logo.png" width="100"></td>
                            <td colspan="7" style="text-align:center;">
                                <strong> $empresa[nombre] </strong>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="7" style="text-align:center">
                                NIT  $empresa[nit] - $empresa[dig_ver] 
                            </td>
                        </tr>
                        <tr style="text-align:left !important;">
                            <td colspan="7">
                                <table style="width: 100%;">
                                    <tr>
                                        <td colspan="2">
                                            NÓMINA No.:  $id_nomina 
                                        </td>
                                        <td colspan="2">
                                            MES: $nomes 
                                        </td>
                                        <td colspan="2">
                                            AÑO:  $nom[vigencia] 
                                        </td>
                                        <td colspan="2">
                                            EMISIÓN: $emision 
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8" style="text-align:center">
                                <b>REPORTE POR CONCEPTOS DE NÓMINA</b>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="8">
                                <div style="border-top: 3px solid black; margin: 5px 0;"></div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
EOT;
        echo $encabezadoo;
        $listado = '';
        $topdf = '';
        $diasLab = 0;
        $total_conceptos = 0;
        $nom_concepto = '';
        $netos = [];
        foreach ($obj as $o) {
            $devengado = 0;
            $deducido = 0;
            $id_empleado = $o['id_empleado'];
            $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
            $val_sueldo = $key !== false ? $dlab[$key]['val_liq_dias'] : 0;
            $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
            $val_auxt = $key !== false ? $dlab[$key]['val_liq_auxt'] : 0;
            $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
            $val_auxal = $key !== false ? $dlab[$key]['aux_alim'] : 0;
            $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
            $val_bsp = $key !== false ? $bsp[$key]['val_bsp'] : 0;
            $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
            $val_vac = $key !== false ? $vac[$key]['val_liq'] : 0;
            $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
            $val_pri_vac = $key !== false ? $vac[$key]['val_prima_vac'] : 0;
            $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
            $val_recrea = $key !== false ? $vac[$key]['val_bon_recrea'] : 0;
            $key = array_search($id_empleado, array_column($lic, 'id_empleado'));
            $val_lic = $key !== false ? $lic[$key]['val_liq'] : 0;
            $key = array_search($id_empleado, array_column($indemnizaciones, 'id_empleado'));
            $val_indem = $key !== false ? $indemnizaciones[$key]['val_liq'] : 0;
            $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
            $val_salud = $key !== false ? $segsoc[$key]['aporte_salud_emp'] : 0;
            $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
            $val_pension = $key !== false ? $segsoc[$key]['aporte_pension_emp'] : 0;
            $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
            $val_solidaria = $key !== false ? $segsoc[$key]['aporte_solidaridad_pensional'] : 0;
            $key = array_search($id_empleado, array_column($retfte, 'id_empleado'));
            $val_rtefte = $key !== false ? $retfte[$key]['val_ret'] : 0;
            $representacion = $o['representacion'] == 1 ? $grepre['valor'] : 0;
            $representacion = $nom['tipo'] == 'N' ? $representacion : 0;
            $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
            $val_ces = $key !== false ? $cesantias[$key]['val_cesantias'] : 0;
            $val_ices = $key !== false ? $cesantias[$key]['val_icesantias'] : 0;
            $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
            $val_prim_sv = $key !== false ? $prima_sv[$key]['val_liq_ps'] : 0;
            $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
            $val_prim_nav = $key !== false ? $prima_nav[$key]['val_liq_pv'] : 0;
            $key = array_search($id_empleado, array_column($compensatorios, 'id_empleado'));
            $val_compensa = $key !== false ? $compensatorios[$key]['val_compensa'] : 0;
            $filtro = [];
            $filtro = array_filter($hoex, function ($hoex) use ($id_empleado) {
                return $hoex["id_empleado"] == $id_empleado;
            });
            $val_hoext = 0;
            if (count($filtro) > 0) {
                foreach ($filtro as $f) {
                    $val_hoext += $f['val_liq'];
                }
            }
            $filtro = [];
            $filtro = array_filter($incap, function ($incap) use ($id_empleado) {
                return $incap["id_empleado"] == $id_empleado;
            });
            $val_incap = 0;
            if (count($filtro) > 0) {
                foreach ($filtro as $f) {
                    $valor_cp = $f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl'];
                    $val_incap += $valor_cp;
                }
            }
            $filtro = [];
            $filtro = array_filter($lib, function ($lib) use ($id_empleado) {
                return $lib["id_empleado"] == $id_empleado;
            });
            $val_libr = 0;
            if (count($filtro) > 0) {
                foreach ($filtro as $f) {
                    $val_libr += $f['val_mes_lib'];
                }
            }
            $filtro = [];
            $filtro = array_filter($emb, function ($emb) use ($id_empleado) {
                return $emb["id_empleado"] == $id_empleado;
            });
            $val_embar = 0;
            if (count($filtro) > 0) {
                foreach ($filtro as $f) {
                    $val_embar += $f['val_mes_embargo'];
                }
            }
            $filtro = [];
            $filtro = array_filter($sind, function ($sind) use ($id_empleado) {
                return $sind["id_empleado"] == $id_empleado;
            });
            $val_sind = 0;
            if (count($filtro) > 0) {
                foreach ($filtro as $f) {
                    $val_sind += $f['val_aporte'];
                }
            }
            $devengado = $val_sueldo + $val_auxt + $val_auxal + $val_bsp + $val_vac + $val_pri_vac + $val_recrea + $val_lic + $val_indem + $representacion + $val_hoext + $val_incap + $val_ces + $val_ices + $val_prim_sv + $val_prim_nav + $val_compensa;
            $deducido = $val_salud + $val_pension + $val_solidaria + $val_rtefte + $val_libr + $val_embar + $val_sind;
            $val_neto = $devengado - $deducido;
            $netos[$id_empleado] = $val_neto;
        }
        foreach ($obj as $o) {
            $id_empleado = $o['id_empleado'];
            $nom_empleado = mb_strtoupper($o['nombre1'] . ' ' . $o['nombre2'] . ' ' . $o['apellido1'] . ' ' . $o['apellido2']);
            $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
            $diasLab = $dlab[$key]['dias_liq'];
            switch ($concepto) {
                case '0':
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $sueldo = $dlab[$key]['val_liq_dias'];
                    if ($sueldo > 0) {
                        if ($key !== false) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>SUELDO BÁSICO</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $dlab[$key]['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($sueldo) . '</td>
                            </tr>';
                        }
                    }
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $auxt = $dlab[$key]['val_liq_auxt'];
                    if ($key !== false) {
                        if ($auxt > 0) {
                            $topdf .= '
                                <tr class="resaltar">
                                    <td>AUXILIO DE TRANSPORTE</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $dlab[$key]['dias_liq'] . '</td>
                                    <td style="text-align: right;">' . pesos($auxt) . '</td>
                                </tr>';
                        }
                    }
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $auxal = $dlab[$key]['aux_alim'];
                    if ($key !== false) {
                        if ($auxal > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>AUXILIO DE ALIMENTACIÓN</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $dlab[$key]['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($auxal) . '</td>
                            </tr>';
                        }
                    }
                    //horas extras
                    $filtro = [];
                    $filtro = array_filter($hoex, function ($hoex) use ($id_empleado) {
                        return $hoex["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . mb_strtoupper($f['desc_he']) . '</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $f['cantidad_he'] . '</td>
                                <td style="text-align: right;">' . pesos($f['val_liq']) . '</td>
                            </tr>';
                        }
                    }
                    //bonificacion por servicios
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    if ($key !== false) {
                        $val = $bsp[$key]['val_bsp'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>BONIFICACIÓN POR SERVICIOS PRESTADOS</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>360</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                        }
                    }
                    //vacaiones
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>VACACIONES</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $vac[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_liq']) . '</td>
                            </tr>';
                        }
                    }
                    //vacaiones
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_prima_vac'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>PRIMA DE VACACIONES</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $vac[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_prima_vac']) . '</td>
                            </tr>';
                        }
                    }
                    //vacaiones
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_bon_recrea'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>BONIFICACIÓN DE RECREACIÓN</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>2</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_bon_recrea']) . '</td>
                            </tr>';
                        }
                    }
                    //incapacidad
                    $filtro = [];
                    $filtro = array_filter($incap, function ($incap) use ($id_empleado) {
                        return $incap["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>INCAPACIDAD</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $f['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl']) . '</td>
                            </tr>';
                        }
                    }
                    //licencia remunerada
                    $key = array_search($id_empleado, array_column($lic, 'id_empleado'));
                    if ($key !== false) {
                        $val = $lic[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>LICENCIA MATERNA/PATERNA</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $lic[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                        }
                    }
                    //otros pagos   
                    $key = array_search($id_empleado, array_column($indemnizaciones, 'id_empleado'));
                    if ($key !== false) {
                        $val = $indemnizaciones[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>INDEMNIZACIÓN POR VACACIONES</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $indemnizaciones[$key]['cant_dias'] . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                        }
                    }
                    //Gastos de representación
                    if ($o['representacion'] == 1) {

                        $topdf .= '
                        <tr class="resaltar">
                            <td>GASTOS DE REPRESENTACIÓN</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $diasLab . '</td>
                            <td style="text-align: right;">' . pesos($grepre['valor']) . '</td>
                        </tr>';
                    }
                    //Prima servicios
                    $key = array_search($id_empleado, array_column($prima_sv, 'id_empleado'));
                    if ($key !== false) {
                        $val_ps = $prima_sv[$key]['val_liq_ps'];
                        $days = $prima_sv[$key]['cant_dias'] > 0 ? $prima_sv[$key]['cant_dias'] : 0;
                        if ($val_ps > 0) {
                            $devengos += $val_ps;
                            $topdf .= '
                                    <tr class="resaltar">
                                        <td>PRIMA DE SERVICIOS</td>
                                        <td>' . $o['no_documento'] . '</td>
                                        <td>' . $days . '</td>
                                        <td style="text-align: right;">' . pesos($val_ps) . '</td>
                                    </tr>';
                        }
                    }
                    //Prima Navidad
                    $key = array_search($id_empleado, array_column($prima_nav, 'id_empleado'));
                    if ($key !== false) {
                        $val_nav = $prima_nav[$key]['val_liq_pv'];
                        $dias_nav = $prima_nav[$key]['cant_dias'];
                        if ($val_nav > 0) {
                            $devengos += $val_nav;
                            $topdf .= '
                                <tr class="resaltar">
                                    <td>PRIMA DE NAVIDAD</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $dias_nav . '</td>
                                    <td style="text-align: right;">' . pesos($val_nav) . '</td>
                                </tr>';
                        }
                    }
                    //Cesantias
                    $key = array_search($id_empleado, array_column($cesantias, 'id_empleado'));
                    if ($key !== false) {
                        $val_ces = $cesantias[$key]['val_cesantias'];
                        $val_ices = $cesantias[$key]['val_icesantias'];
                        if ($val_ces > 0) {
                            $devengos += $val_ces;
                            $topdf .= '
                        <tr class="resaltar">
                            <td>CESANTÍAS</td>
                            <td>' . $o['no_documento'] . '</td>
                            <td>' . $cesantias[$key]['cant_dias'] . '</td>
                            <td style="text-align: right;">' . pesos($val_ces) . '</td>
                        </tr>';
                        }
                        if ($val_ices > 0) {
                            $devengos += $val_ices;
                            $topdf .= '
                        <tr class="resaltar">
                            <td>INTERESES A CESANTIAS</td>
                            <td>' . $o['no_documento'] . '</td>
                            <td>' . $cesantias[$key]['cant_dias'] . '</td>
                            <td style="text-align: right;">' . pesos($val_ices) . '</td>
                        </tr>';
                        }
                    }
                    //Compensatorios 
                    $key = array_search($id_empleado, array_column($compensatorios, 'id_empleado'));
                    if ($key !== false) {
                        $val_cp = $compensatorios[$key]['val_compensa'];
                        $dias_comp = $compensatorios[$key]['dias'];
                        if ($val_cp > 0) {
                            $devengos += $val_cp;
                            $topdf .= '
                        <tr class="resaltar">
                            <td>COMPENSATORIO</td>
                            <td>' . $o['no_documento'] . '</td>
                            <td>' . $dias_comp . '</td>
                            <td style="text-align: right;">' . pesos($val_cp) . '</td>
                        </tr>';
                        }
                    }
                    //salud
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $vals = $segsoc[$key]['aporte_salud_emp'];
                    if ($key !== false) {
                        if ($vals > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>APORTE A SALUD</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($vals) . '</td>
                            </tr>';
                        }
                    }
                    //pension
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $valp = $segsoc[$key]['aporte_pension_emp'];
                    if ($key !== false) {
                        if ($valp > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>APORTE A PENSIÓN</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($valp) . '</td>
                            </tr>';
                        }
                    }
                    //pen. solidaridad
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $valps = $segsoc[$key]['aporte_solidaridad_pensional'];
                    if ($key !== false) {
                        if ($valps > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>APORTE A SOLIDARIDAD PENSIONAL</td>
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($valps) . '</td>
                            </tr>';
                        }
                    }
                    //libranzas
                    $filtro = [];
                    $filtro = array_filter($lib, function ($lib) use ($id_empleado) {
                        return $lib["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_mes_lib'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>LIBRANZA - ' . $f['nom_banco'] . '</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_mes_lib']) . '</td>
                                </tr>';
                            }
                        }
                    }
                    //embargos
                    $filtro = [];
                    $filtro = array_filter($emb, function ($emb) use ($id_empleado) {
                        return $emb["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_mes_embargo'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>EMBARGO - ' . $f['nom_juzgado'] . '</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_mes_embargo']) . '</td>
                                </tr>';
                            }
                        }
                    }
                    //sindicatos
                    $filtro = [];
                    $filtro = array_filter($sind, function ($sind) use ($id_empleado) {
                        return $sind["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_aporte'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>SINDICATO - ' . $f['nom_sindicato'] . '</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_aporte']) . '</td>
                                </tr>';
                            }
                        }
                    }
                    //Retencion en la fuente
                    $key = array_search($id_empleado, array_column($retfte, 'id_empleado'));
                    if ($key !== false) {
                        $val = $retfte[$key]['val_ret'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>RETENCIÓN EN LA FUENTE</td>
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                        }
                    }
                    $pagado = isset($netos[$id_empleado]) ? $netos[$id_empleado] : 0;
                    $topdf .= '
                    <tr>
                        <td>NETO</td>
                        <td>' . $o['no_documento'] . '</td>
                        <td>' . $diasLab . '</td>
                        <td style="text-align: right;">' . pesos($pagado) . '</td>
                    </tr>';

                    break;
                case '1':
                    //sueldo   
                    $nom_concepto = 'SUELDO BÁSICO';
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $sueldo = $dlab[$key]['val_liq_dias'];
                    if ($sueldo > 0) {
                        if ($key !== false) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $dlab[$key]['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($sueldo) . '</td>
                            </tr>';
                            $total_conceptos += $sueldo;
                        }
                    }
                    break;
                case '2':
                    //auxilio de transporte
                    $nom_concepto = 'AUXILIO DE TRANSPORTE';
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $auxt = $dlab[$key]['val_liq_auxt'];
                    if ($key !== false) {
                        if ($auxt > 0) {
                            $topdf .= '
                                <tr class="resaltar">
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $nom_empleado . '</td>
                                    <td>' . $dlab[$key]['dias_liq'] . '</td>
                                    <td style="text-align: right;">' . pesos($auxt) . '</td>
                                </tr>';
                            $total_conceptos += $auxt;
                        }
                    }
                    break;
                case '3':
                    //auxilio de alimentacion
                    $nom_concepto = 'AUXILIO DE ALIMENTACIÓN';
                    $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
                    $auxal = $dlab[$key]['aux_alim'];
                    if ($key !== false) {
                        if ($auxal > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $dlab[$key]['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($auxal) . '</td>
                            </tr>';
                            $total_conceptos += $auxal;
                        }
                    }
                    break;
                case '4':
                    //horas extras
                    $nom_concepto = 'HORAS EXTRAS';
                    $filtro = [];
                    $filtro = array_filter($hoex, function ($hoex) use ($id_empleado) {
                        return $hoex["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . ' - ' . mb_strtoupper($f['desc_he']) . '</td>
                                <td>' . $f['cantidad_he'] . '</td>
                                <td style="text-align: right;">' . pesos($f['val_liq']) . '</td>
                            </tr>';
                            $total_conceptos += $f['val_liq'];
                        }
                    }
                    break;
                case '5':
                    //bonificacion por servicios
                    $nom_concepto = 'BONIFICACIÓN POR SERVICIOS PRESTADOS';
                    $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                    if ($key !== false) {
                        $val = $bsp[$key]['val_bsp'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>360</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '6':
                    //vacaiones
                    $nom_concepto = 'VACACIONES';
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $vac[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_liq']) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '7':
                    //prima vacaiones
                    $nom_concepto = 'PRIMA DE VACACIONES';
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_prima_vac'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $vac[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_prima_vac']) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '8':
                    //bonificacion de recreacion
                    $nom_concepto = 'BONIFICACIÓN DE RECREACIÓN';
                    $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                    if ($key !== false) {
                        $val = $vac[$key]['val_bon_recrea'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>2</td>
                                <td style="text-align: right;">' . pesos($vac[$key]['val_bon_recrea']) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '9':
                    //incapacidad
                    $nom_concepto = 'INCAPACIDAD';
                    $filtro = [];
                    $filtro = array_filter($incap, function ($incap) use ($id_empleado) {
                        return $incap["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $f['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl']) . '</td>
                            </tr>';
                            $total_conceptos += $f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl'];
                        }
                    }
                    break;
                case '10':
                    //licencia remunerada
                    $nom_concepto = 'LICENCIA MATERNA/PATERNA';
                    $key = array_search($id_empleado, array_column($lic, 'id_empleado'));
                    if ($key !== false) {
                        $val = $lic[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $lic[$key]['dias_liqs'] . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '11':
                    //Gastos de representación
                    $nom_concepto = 'GASTOS DE REPRESENTACIÓN';
                    if ($o['representacion'] == 1) {
                        $topdf .= '
                        <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $diasLab . '</td>
                            <td style="text-align: right;">' . pesos($grepre['valor']) . '</td>
                        </tr>';
                        $total_conceptos += $grepre['valor'];
                    }
                    break;
                case '12':
                    //otros pagos   
                    $nom_concepto = 'INDEMNIZACIÓN POR VACACIONES';
                    $key = array_search($id_empleado, array_column($indemnizaciones, 'id_empleado'));
                    if ($key !== false) {
                        $val = $indemnizaciones[$key]['val_liq'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $indemnizaciones[$key]['cant_dias'] . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '13':
                    //salud
                    $nom_concepto = 'APORTE A SALUD';
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $vals = $segsoc[$key]['aporte_salud_emp'];
                    if ($key !== false) {
                        if ($vals > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($vals) . '</td>
                            </tr>';
                            $total_conceptos += $vals;
                        }
                    }
                    break;
                case '14':
                    //pension
                    $nom_concepto = 'APORTE A PENSIÓN';
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $valp = $segsoc[$key]['aporte_pension_emp'];
                    if ($key !== false) {
                        if ($valp > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($valp) . '</td>
                            </tr>';
                            $total_conceptos += $valp;
                        }
                    }
                    break;
                case '15':
                    //pen. solidaridad
                    $nom_concepto = 'APORTE A SOLIDARIDAD PENSIONAL';
                    $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                    $valps = $segsoc[$key]['aporte_solidaridad_pensional'];
                    if ($key !== false) {
                        if ($valps > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                <td>' . $o['no_documento'] . '</td>
                                <td>' . $nom_empleado . '</td>
                                <td>' . $diasLab . '</td>
                                <td style="text-align: right;">' . pesos($valps) . '</td>
                            </tr>';
                            $total_conceptos += $valps;
                        }
                    }
                    break;
                case '16':
                    //libranzas
                    $nom_concepto = 'LIBRANZAS';
                    $filtro = [];
                    $filtro = array_filter($lib, function ($lib) use ($id_empleado) {
                        return $lib["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_mes_lib'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $nom_empleado . ' - ' . $f['nom_banco'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_mes_lib']) . '</td>
                                </tr>';
                                $total_conceptos += $f['val_mes_lib'];
                            }
                        }
                    }
                    break;
                case '17':
                    //embargos
                    $nom_concepto = 'EMBARGOS';
                    $filtro = [];
                    $filtro = array_filter($emb, function ($emb) use ($id_empleado) {
                        return $emb["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_mes_embargo'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $nom_empleado . ' - ' . $f['nom_juzgado'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_mes_embargo']) . '</td>
                                </tr>';
                                $total_conceptos += $f['val_mes_embargo'];
                            }
                        }
                    }
                    break;
                case '18':
                    //sindicatos
                    $nom_concepto = 'SINDICATOS';
                    $filtro = [];
                    $filtro = array_filter($sind, function ($sind) use ($id_empleado) {
                        return $sind["id_empleado"] == $id_empleado;
                    });
                    if (count($filtro) > 0) {
                        foreach ($filtro as $f) {
                            if ($f['val_aporte'] > 0) {
                                $topdf .= '
                                <tr class="resaltar">
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $nom_empleado . ' - ' . $f['nom_sindicato'] . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td style="text-align: right;">' . pesos($f['val_aporte']) . '</td>
                                </tr>';
                                $total_conceptos += $f['val_aporte'];
                            }
                        }
                    }
                    break;
                case '19':
                    //Retencion en la fuente
                    $nom_concepto = 'RETENCIÓN EN LA FUENTE';
                    $key = array_search($id_empleado, array_column($retfte, 'id_empleado'));
                    if ($key !== false) {
                        $val = $retfte[$key]['val_ret'];
                        if ($val > 0) {
                            $topdf .= '
                            <tr class="resaltar">
                                    <td>' . $o['no_documento'] . '</td>
                                    <td>' . $nom_empleado . '</td>
                                    <td>' . $diasLab . '</td>
                                    <td>' . pesos($retfte[$key]['base']) . '</td>
                                <td style="text-align: right;">' . pesos($val) . '</td>
                            </tr>';
                            $total_conceptos += $val;
                        }
                    }
                    break;
                case '20':
                    $id_banco = $o['id_banco'];
                    $getBanco = '';
                    $key = array_search($id_empleado, array_column($saln, 'id_empleado'));
                    $keybanco = array_search($id_banco, array_column($bancos, 'id_banco'));
                    if ($keybanco !== false) {
                        $tipo = $o['tipo_cta'] == '1' ? 'AHORROS' : 'CORRIENTE';
                        $getBanco = "<td>" . $o['nombre1'] . "</td>
                                    <td>" . $o['nombre2'] . "</td>
                                    <td>" . $o['apellido1'] . "</td>
                                    <td>" . $o['apellido2'] . "</td>
                                    <td>" . $bancos[$keybanco]['nom_banco'] . "</td>
                                    <td>'" . $bancos[$keybanco]['cod_banco'] . "</td>
                                    <td>" . $tipo . "</td>
                                    <td>" . $o['cuenta_bancaria'] . "</td>";
                    }
                    $pagado = isset($netos[$id_empleado]) ? $netos[$id_empleado] : 0;
                    $topdf .= '
                    <tr>
                        <td>' . $o['nom_municipio'] . '</td>
                        <td>' . $o['no_documento'] . '</td>' . $getBanco .
                        '<td>' . $diasLab . '</td>
                        <td>' . pesos($pagado) . '</td>
                    </tr>';
                    $total_conceptos += $pagado;
                    break;
            }
        }
        ?>
        <div style="overflow-x: scroll;">
            <table style="width:100% !important; font-size:10px !important;" id="tableImpConceptos">
                <tr>
                    <th colspan="10" style="background-color: #D7DBDD; text-align:center;"><?php echo $nom_concepto ?></th>
                </tr>
                <tr style="background-color: #D7DBDD; text-align:center;">
                    <?php if ($concepto == 0) { ?>
                        <td><b>CONCEPTO</b></td>
                        <td><b>DOCUMENTO</b></td>
                    <?php } else { ?>
                        <td><b>DOCUMENTO</b></td>
                        <?php if ($concepto != 20) { ?>
                            <td><b>NOMBRE</b></td>
                        <?php } ?>
                    <?php }
                    if ($concepto == '20') { ?>
                        <td><b>MUNICIPIO</b></td>
                        <td><b>NOMBRE1</b></td>
                        <td><b>NOMBRE2</b></td>
                        <td><b>APELLIDO1</b></td>
                        <td><b>APELLIDO2</b></td>
                        <td><b>BANCO</b></td>
                        <td><b>COD_BANCO</b></td>
                        <td><b>TIPO</b></td>
                        <td><b>CUENTA</b></td>
                    <?php } ?>
                    <td><b>DIAS</b></td>
                    <?php if ($concepto == '19') { ?>
                        <td><b>BASE</b></td>
                    <?php } ?>
                    <td><b>LIQUIDADO</b></td>
                </tr>
                <?php if ($concepto != '0' && $total_conceptos > 0) { ?>
                    <tr>
                        <th colspan="3">TOTAL POR CONCEPTO</th>
                        <td style="text-align: right;"><b><?php echo pesos($total_conceptos); ?></b></td>
                    </tr>
                <?php }
                if ($total_conceptos <= 0) { ?>
                    <tr>
                        <td colspan="n" style="text-align: center;">No hay registros para mostrar</td>
                    </tr>
                <?php } ?>
                <?php echo $topdf; ?>
            </table>
        </div>
    </div>
</div>