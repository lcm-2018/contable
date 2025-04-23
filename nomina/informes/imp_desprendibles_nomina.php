<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
require_once '../../vendor/autoload.php';
require_once '../../libs/PHPMailer/src/Exception.php';
require_once '../../libs/PHPMailer/src/PHPMailer.php';
require_once '../../libs/PHPMailer/src/SMTP.php';

use Dompdf\Dompdf;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
if (isset($_POST['cedula'])) {
    if ($_POST['cedula'] == "%%") {
        $condicion = "";
    } else {
        $condicion = " AND `no_documento` = '$cedula'";
    }
} else {
    $condicion = "AND `no_documento` = 0";
}
if (isset($_POST['sede'])) {
    $sede = $_POST['sede'];
    if ($sede == 0) {
        $condicion .= "";
    } else {
        $condicion .= " AND `id_sede` = $sede";
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_empleado`, `vigencia`, `salario_basico`, `no_documento`, `estado`, CONCAT_WS(' ', `apellido1`, `apellido2`, `nombre1`, `nombre2`) AS `empleado`, `representacion`
                ,`nom_municipio`, `codigo`, `cargo`, `fech_inicio`, `correo`, `sede`
            FROM
                (SELECT  
                    `nom_empleado`.`id_empleado`
                    , `nom_empleado`.`tipo_doc`
                    , `nom_empleado`.`sede_emp` AS `id_sede`
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
                    , `tb_sedes`.`nom_sede` AS `sede`
                    , `nom_cargo_empleado`.`codigo`
                    , `nom_cargo_empleado`.`descripcion_carg` AS `cargo`
                    , `nom_empleado`.`fech_inicio`
                    , `nom_empleado`.`correo`
                FROM `nom_salarios_basico`
                    INNER JOIN `nom_empleado`
                        ON(`nom_salarios_basico`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_liq_salario` 
                        ON (`nom_liq_salario`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `tb_sedes` 
            ON (`nom_empleado`.`sede_emp` = `tb_sedes`.`id_sede`)
            INNER JOIN `tb_municipios` 
            ON (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
                    INNER JOIN `nom_cargo_empleado` 
                        ON (`nom_empleado`.`cargo` = `nom_cargo_empleado`.`id_cargo`)
                WHERE `nom_salarios_basico`.`id_salario`  
                    IN(SELECT MAX(`id_salario`) FROM `nom_salarios_basico` WHERE `vigencia` <= '$vigencia' GROUP BY `id_empleado`)) AS t
            WHERE `id_nomina` = $id_nomina $condicion
            GROUP BY `id_empleado`
            ORDER BY `nom_municipio`,`empleado`,`no_documento` ASC";
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
    $sql = "SELECT `id_empleado`, `dias_liqs`,`dias_liquidar`, `val_liq`,`val_prima_vac`,`val_bon_recrea`, `nom_liq_vac`.`id_nomina`
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
    $sql = "SELECT id_rte_fte, id_empleado, val_ret, id_nomina
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
    $sql = "SELECT `id_nomina`, `estado`, `planilla`, `mes`, `vigencia` 
            FROM `nom_nominas` WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $nom = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$index_mes = !isset($nom['mes']) &&  $nom['mes'] == '' ? '00' : $nom['mes'];
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_sede`, `nom_sede` AS `nombre` FROM `tb_sedes` ORDER BY `nom_sede` ASC";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$meses = [
    '00' => 'NINGUNO',
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
    <div class="form-group col-md-3">
        <label for="cedula" class="small">No Documento</label>
        <input type="text" class="form-control form-control-sm" id="cedula" value="<?php echo $cedula ?>">
    </div>
    <div class="form-group col-md-4">
        <label for="slcSede" class="small">SEDE</label>
        <select id="slcSede" class="form-control form-control-sm">
            <option value="0">TODAS</option>
            <?php
            foreach ($sedes as $sede) {
                $id_sede = isset($_POST['sede']) ? $_POST['sede'] : 0;
                $slc = $sede['id_sede'] == $id_sede ? 'selected' : '';
                echo '<option value="' . $sede['id_sede'] . '" ' . $slc . '>' . $sede['nombre'] . '</option>';
            }
            ?>
        </select>
    </div>
    <div class="form-group col-md-1">
        <label for="buscar" class="small">&nbsp;</label>
        <button type="button" class="btn btn-light btn-sm btn-block desprendible" value="0">Filtrar</button>
    </div>
    <div class="form-group col-md-1">
        <label for="buscar" class="small">&nbsp;</label>
        <button type="button" class="btn btn-light btn-sm btn-block desprendible" value="1">Enviar</button>
    </div>
    <div class="form-group col-md-3">
        <label for="buscar" class="small">&nbsp;</label>
        <div class="text-right">
            <?php if (PermisosUsuario($permisos, 5115, 6) || $id_rol == 1) { ?>
                <a type="button" id="btnReporteGral" class="btn btn-outline-success btn-sm" value="01" title="Exprotar a Excel">
                    <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                </a>

                <a type="button" class="btn btn-primary btn-sm" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"> Imprimir</a>
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
        if (empty($obj)) {
            echo '<div class="alert alert-danger text-center" role="alert">
                    <strong>No hay datos relacionados, comprobar No.Documento</strong>
                </div>';
            exit();
        }
        $topdf = '';
        foreach ($obj as $o) {
            $id_empleado = $o['id_empleado'];
            if (isset($_POST['accion'])) {
                if ($_POST['accion'] == 1) {
                    $topdf = '';
                }
            }
            $topdf .= '
            <table style="width:100% !important; font-size:10px !important;">
                <!--<thead style="background-color: white !important;font-size:80%">-->
                <tr>
                    <td colspan="8">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class="text-center" style="width:18%"><img src="../../images/logos/logo.png" width="100"></td>
                                <td colspan="7" style="text-align:center;">
                                    <strong>' . $empresa['nombre'] . '</strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT ' . $empresa['nit'] . '-' . $empresa['dig_ver'] . '
                                </td>
                            </tr>
                            <tr style="text-align:left !important;">
                                <td colspan="7">
                                    <table style="width: 100%;">
                                        <tr>
                                            <td colspan="2">
                                                NÓMINA No.: ' . $id_nomina . '
                                            </td>
                                            <td colspan="2">
                                                MES: ' . $index_mes . ' - ' . $meses[$index_mes] . '
                                            </td>
                                            <td colspan="2">
                                                AÑO: ' . $nom['vigencia'] . '
                                            </td>
                                            <td colspan="2">
                                                EMISIÓN: ' . $date->format('d/m/Y') . '
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align:center">
                                    <b>DESPRENDIBLE DE NÓMINA</b>
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
                <tr>
                    <td colspan="8">
                        <table style="width:100% !important;">

                            <tr>
                                <td colspan="8">
                                    <table style="width: 100%;">
                                        <tr>
                                            <th style="width: 20%; text-align:left !important;">
                                                MUNICIPIO
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . mb_strtoupper($o['nom_municipio'] . ' - ' . $o['sede']) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left !important;">
                                                NO. DOC.
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . mb_strtoupper($o['no_documento']) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left !important;">
                                                EMPLEADO
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . mb_strtoupper($o['empleado']) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left !important;">
                                                CARGO
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . mb_strtoupper($o['codigo'] . ' - ' . $o['cargo']) . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left !important;">
                                                FEC_INGRESO
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . $o['fech_inicio'] . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <th style="text-align:left !important;">
                                                BASE SALARIAL
                                            </th>
                                            <td style="text-align:left !important;">
                                                ' . pesos($o['salario_basico']) . '
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8">
                                    <table style="width: 100%;">
                                        <tr style="background-color: #D7DBDD; text-align:center;">
                                            <td><b>CONCEPTO</b></td>
                                            <td><b>DIAS</b></td>
                                            <td><b>DEVENGADO</b></td>
                                            <td><b>DEDUCIDO</b></td>
                                        </tr>';
            $diasLab = 0;
            $devengos = 0;
            $deducciones = 0;
            $key = array_search($id_empleado, array_column($dlab, 'id_empleado'));
            if ($key !== false) {
                $sueldo = $dlab[$key]['val_liq_dias'];
                $auxt = $dlab[$key]['val_liq_auxt'];
                $auxal = $dlab[$key]['aux_alim'];
                $diasLab = $dlab[$key]['dias_liq'];
                if ($sueldo > 0) {
                    $devengos += $sueldo;
                    $topdf .= '
                            <tr class="resaltar">
                                <td>SUELDO BÁSICO</td>
                                <td>' . $dlab[$key]['dias_liq'] . '</td>
                                <td style="text-align: right;">' . pesos($sueldo) . '</td>
                                <td style="text-align: right;">' . pesos(0) . '</td>
                            </tr>';
                    if ($auxt > 0) {
                        $devengos += $auxt;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>AUXILIO DE TRANSPORTE</td>
                        <td>' . $dlab[$key]['dias_liq'] . '</td>
                        <td style="text-align: right;">' . pesos($auxt) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                    if ($auxal > 0) {
                        $devengos += $auxal;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>AUXILIO DE ALIMENTACIÓN</td>
                        <td>' . $dlab[$key]['dias_liq'] . '</td>
                        <td style="text-align: right;">' . pesos($auxal) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
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
                        $devengos += $f['val_liq'];
                        $topdf .= '
                    <tr class="resaltar">
                        <td>' . mb_strtoupper($f['desc_he']) . '</td>
                        <td>' . $f['cantidad_he'] . '</td>
                        <td style="text-align: right;">' . pesos($f['val_liq']) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                }
                //bonificacion por servicios
                $key = array_search($id_empleado, array_column($bsp, 'id_empleado'));
                if ($key !== false) {
                    $val = $bsp[$key]['val_bsp'];
                    if ($val > 0) {
                        $devengos += $val;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>BONIFICACIÓN POR SERVICIOS PRESTADOS</td>
                        <td>360</td>
                        <td style="text-align: right;">' . pesos($val) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                }
                //vacaiones
                $key = array_search($id_empleado, array_column($vac, 'id_empleado'));
                if ($key !== false) {
                    $val = $vac[$key]['val_liq'];
                    if ($val > 0) {
                        $devengos += $vac[$key]['val_liq'];
                        $devengos += $vac[$key]['val_prima_vac'];
                        $devengos += $vac[$key]['val_bon_recrea'];
                        $topdf .= '
                    <tr class="resaltar">
                        <td>VACACIONES</td>
                        <td>' . $vac[$key]['dias_liquidar'] . '</td>
                        <td style="text-align: right;">' . pesos($vac[$key]['val_liq']) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>
                    <tr class="resaltar">
                        <td>PRIMA DE VACACIONES</td>
                        <td>' . $vac[$key]['dias_liquidar'] . '</td>
                        <td style="text-align: right;">' . pesos($vac[$key]['val_prima_vac']) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>
                    <tr class="resaltar">
                        <td>BONIFICACIÓN DE RECREACIÓN</td>
                        <td>2</td>
                        <td style="text-align: right;">' . pesos($vac[$key]['val_bon_recrea']) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
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
                        $devengos += $f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl'];
                        $topdf .= '
                    <tr class="resaltar">
                        <td>INCAPACIDAD</td>
                        <td>' . $f['dias_liq'] . '</td>
                        <td style="text-align: right;">' . pesos($f['pago_empresa'] + $f['pago_eps'] + $f['pago_arl']) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                }
                //licencia remunerada
                $key = array_search($id_empleado, array_column($lic, 'id_empleado'));
                if ($key !== false) {
                    $val = $lic[$key]['val_liq'];
                    if ($val > 0) {
                        $devengos += $val;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>LICENCIA REMUNERADA</td>
                        <td>' . $lic[$key]['dias_liqs'] . '</td>
                        <td style="text-align: right;">' . pesos($val) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                }
                //otros pagos   
                $key = array_search($id_empleado, array_column($indemnizaciones, 'id_empleado'));
                if ($key !== false) {
                    $val = $indemnizaciones[$key]['val_liq'];
                    if ($val > 0) {
                        $devengos += $val;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>OTROS PAGOS</td>
                        <td>' . $indemnizaciones[$key]['cant_dias'] . '</td>
                        <td style="text-align: right;">' . pesos($val) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                    </tr>';
                    }
                }
                //Gastos de representación
                if ($o['representacion'] == 1) {

                    $topdf .= '
                <tr class="resaltar">
                    <td>GASTOS DE REPRESENTACIÓN</td>
                    <td>' . $diasLab . '</td>
                    <td style="text-align: right;">' . pesos($grepre['valor']) . '</td>
                    <td style="text-align: right;">' . pesos(0) . '</td>
                </tr>';
                    $devengos += $grepre['valor'];
                }
                /*prima_sv
prima_nav
cesantias
compensatorios*/
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
                        <td>' . $days . '</td>
                        <td style="text-align: right;">' . pesos($val_ps) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
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
                        <td>' . $dias_nav . '</td>
                        <td style="text-align: right;">' . pesos($val_nav) . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
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
                            <td>' . $cesantias[$key]['cant_dias'] . '</td>
                            <td style="text-align: right;">' . pesos($val_ces) . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
                        </tr>';
                    }
                    if ($val_ices > 0) {
                        $devengos += $val_ices;
                        $topdf .= '
                        <tr class="resaltar">
                            <td>INTERESES A CESANTIAS</td>
                            <td>' . $cesantias[$key]['cant_dias'] . '</td>
                            <td style="text-align: right;">' . pesos($val_ices) . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
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
                            <td>' . $dias_comp . '</td>
                            <td style="text-align: right;">' . pesos($val_cp) . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
                        </tr>';
                    }
                }
                //DEDUCCIONES
                //salud
                $key = array_search($id_empleado, array_column($segsoc, 'id_empleado'));
                if ($key !== false) {
                    $vals = $segsoc[$key]['aporte_salud_emp'];
                    $valp = $segsoc[$key]['aporte_pension_emp'];
                    $valps = $segsoc[$key]['aporte_solidaridad_pensional'];
                    if ($vals > 0) {
                        $deducciones += $vals;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>APORTE A SALUD</td>
                        <td>' . $diasLab . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                        <td style="text-align: right;">' . pesos($vals) . '</td>
                    </tr>';
                    }
                    if ($valp > 0) {
                        $deducciones += $valp;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>APORTE A PENSIÓN</td>
                        <td>' . $diasLab . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                        <td style="text-align: right;">' . pesos($valp) . '</td>
                    </tr>';
                    }
                    if ($valps > 0) {
                        $deducciones += $valps;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>APORTE A SOLIDARIDAD PENSIONAL</td>
                        <td>' . $diasLab . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
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
                            $deducciones += $f['val_mes_lib'];
                            $topdf .= '
                        <tr class="resaltar">
                            <td>LIBRANZA - ' . $f['nom_banco'] . '</td>
                            <td>' . $diasLab . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
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
                            $deducciones += $f['val_mes_embargo'];
                            $topdf .= '
                        <tr class="resaltar">
                            <td>EMBARGO - ' . $f['nom_juzgado'] . '</td>
                            <td>' . $diasLab . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
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
                            $deducciones += $f['val_aporte'];
                            $topdf .= '
                        <tr class="resaltar">
                            <td>SINDICATO - ' . $f['nom_sindicato'] . '</td>
                            <td>' . $diasLab . '</td>
                            <td style="text-align: right;">' . pesos(0) . '</td>
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
                        $deducciones += $val;
                        $topdf .= '
                    <tr class="resaltar">
                        <td>RETENCIÓN EN LA FUENTE</td>
                        <td>' . $diasLab . '</td>
                        <td style="text-align: right;">' . pesos(0) . '</td>
                        <td style="text-align: right;">' . pesos($val) . '</td>
                    </tr>';
                    }
                }
                $topdf .= '
            <tr>
                <td colspan="2" style="text-align: right;"><b>SUBTOTAL</b></td>
                <td style="text-align: right;border-top: 3px double black;">' . pesos($devengos) . '</td>
                <td style="text-align: right;border-top: 3px double black;">' . pesos($deducciones) . '</td>
            </tr>
            <tr>
                <td colspan="2" style="text-align: right;"><b>NETO</b></td>
                <td colspan="2" style="text-align: right;">
                    <b>';
                $topdf .= pesos($devengos - $deducciones);
                $topdf .= '
                    </b>
                </td>
            </tr>

            </table>
            </td>
            </tr>
            </table>
            </td>
            </tr>
            <div style="page-break-before: always;"></div>
            </table>';
                if (isset($_POST['accion'])) {
                    if ($_POST['accion'] == 1 && $o['correo'] != '') {
                        $doc = $o['no_documento'];
                        $dompdf = new Dompdf();
                        $dompdf->loadHtml($topdf);
                        $dompdf->render();
                        $pdf_content = $dompdf->output();
                        file_put_contents($doc . '.pdf', $pdf_content);
                        $to = $o['correo'];
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->SMTPOptions = [
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true
                            ]
                        ];                                           //Send using SMTP
                        $mail->Host       = 'mail.lcm.com.co';                     //Set the SMTP server to send through
                        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
                        $mail->Username   = 'mail@lcm.com.co';                     //SMTP username
                        $mail->Password   = 'Lcm2021*';                               //SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
                        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

                        //Recipients
                        $mail->setFrom('mail@lcm.com.co', 'Info-LCM');
                        $mail->addAddress($to);     //Add a recipient
                        // $mail->addAddress('ellen@example.com');               //Name is optional
                        //$mail->addReplyTo('info@example.com', 'Information');
                        //$mail->addCC('cc@example.com');
                        //$mail->addBCC('bcc@example.com');

                        //Attachments
                        $mail->addAttachment($doc . '.pdf');         //Add attachments
                        //Content
                        $mail->isHTML(true);                                  //Set email format to HTML
                        $mail->Subject = 'Desprendible de nómina del mes de ' . $meses[$index_mes];
                        $mail->Body    = 'Se adjunta Desprendible soporte de nómina del mes de ' . $meses[$index_mes] . ' de ' . $vigencia . '.';
                        $mail->AltBody = '';

                        $mail->send();
                        unlink($doc . '.pdf');
                    }
                }
            }
        }
        echo $topdf;
        ?>
    </div>
</div>