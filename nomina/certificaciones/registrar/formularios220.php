<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 0, ",", ".");
}
function pesos2($valor)
{
    return number_format($valor, 2, ",", ".");
}

include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$empleado = isset($_POST['noDocTercero']) ? $_POST['noDocTercero'] : exit('Acción no permitida');
$vigencia = $_SESSION['vigencia'];
$fecIni = $_POST['fecInicia'] == '' ? $vigencia . '-01-01' : $_POST['fecInicia'];
$fecFin = $_POST['fecFin'] == '' ? $vigencia . '-12-31' : $_POST['fecFin'];
$res = [];
$res['status'] = '0';
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
    $ids_nominas = $rs->fetchAll(PDO::FETCH_ASSOC);
    $ids_nominas = implode(',', array_column($ids_nominas, 'id_nomina'));
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_empleado`.`id_empleado`
                , `nom_empleado`.`tipo_doc`
                , `nom_empleado`.`no_documento`
                , `nom_empleado`.`apellido1`
                , `nom_empleado`.`apellido2`
                , `nom_empleado`.`nombre1`
                , `nom_empleado`.`nombre2`
                , `nom_empleado`.`representacion`
                , `tb_tipos_documento`.`codigo_ne`
            FROM
                `nom_empleado`
                INNER JOIN `tb_tipos_documento` 
                    ON (`nom_empleado`.`tipo_doc` = `tb_tipos_documento`.`id_tipodoc`)
            WHERE `nom_empleado`.`no_documento` IN ($empleado)";
    $rs = $cmd->query($sql);
    $list_empdo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (empty($list_empdo)) {
    $res['msg'] = 'Tercero no tiene registros para el periodo seleccionado';
    echo json_encode($res);
    exit();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `tb_datos_ips`.`id_ips` AS `id_empresa`
                , `tb_datos_ips`. `nit_ips` AS `nit`
                , `tb_datos_ips`.`dv` AS `dig_ver`
                , `tb_datos_ips`.`razon_social_ips` AS `nombre`
                , `tb_departamentos`.`nom_departamento` AS `nombre_dpto`
                , `tb_municipios`.`nom_municipio`
                , `tb_departamentos`.`codigo_departamento` AS `codigo_dpto`
                , `tb_municipios`.`codigo_municipio`
            FROM
                `tb_datos_ips`
            INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)
            INNER JOIN `tb_departamentos` 
                ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)
            LIMIT 1";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_valxvigencia`.`id_valxvig`, `tb_vigencias`.`anio`, `nom_valxvigencia`.`id_concepto`, `nom_valxvigencia`.`valor`
            FROM
                `nom_valxvigencia`
                INNER JOIN `tb_vigencias` 
                    ON (`nom_valxvigencia`.`id_vigencia` = `tb_vigencias`.`id_vigencia`)
            WHERE `nom_valxvigencia`.`id_concepto`  = '6' AND `tb_vigencias`.`anio` = '$vigencia' LIMIT 1";
    $rs = $cmd->query($sql);
    $uvts = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , `id_nomina`
                , SUM(`g_representa`) AS `tot_grep`
                , SUM(`val_liq_dias`+`val_liq_auxt`+`aux_alim`+`horas_ext`) AS `tot_salario`
            FROM
                `nom_liq_dlab_auxt`
            WHERE (`id_nomina` IN ($ids_nominas))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $salarios = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , SUM(`aporte_salud_emp`) AS `salud`
                , SUM(`aporte_pension_emp`) AS `pension`
                , SUM(`aporte_solidaridad_pensional`) AS `pension_solidaria`
            FROM
                `nom_liq_segsocial_empdo`
            WHERE `id_nomina` IN ($ids_nominas)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $deduciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, SUM(`val_total`) AS `tot_viaticos`, `vigencia`
            FROM
                `nom_resolucion_viaticos`
            WHERE `vigencia` = '$vigencia'
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $viaticos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, SUM(`val_cesantias`) AS `tot_cesantias`, SUM(`val_icesantias`) AS `tot_icesantias`, `anio`
            FROM
                `nom_liq_cesantias`
            WHERE `id_nomina` IN ($ids_nominas)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $cesantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, SUM(`val_liq_ps`) AS `tot_prima`, `anio`
            FROM
                `nom_liq_prima`
            WHERE `id_nomina` IN ($ids_nominas)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $prima = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`, SUM(`val_liq_pv`) AS `tot_prima_nan`, `anio`
            FROM
                `nom_liq_prima_nav`
            WHERE `id_nomina` IN ($ids_nominas)
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $prima_nav = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_vacaciones`.`id_empleado`
                , SUM(`nom_liq_vac`.`val_liq`) AS `tot_liq`
                , SUM(`nom_liq_vac`.`val_prima_vac`) AS `tot_prima_vac`
                , SUM(`nom_liq_vac`.`val_bon_recrea`) AS `tot_bon_recrea`
                , `nom_liq_vac`.`anio_vac`
            FROM
                `nom_liq_vac`
                INNER JOIN `nom_vacaciones` 
                    ON (`nom_liq_vac`.`id_vac` = `nom_vacaciones`.`id_vac`)
            WHERE  `nom_liq_vac`.`id_nomina` IN ($ids_nominas)
            GROUP BY `nom_vacaciones`.`id_empleado`";
    $rs = $cmd->query($sql);
    $vacaciones = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_incapacidad`.`id_empleado`
                , `nom_liq_incap`.`id_nomina`
                , SUM(`nom_liq_incap`.`pago_eps`) AS `tot_incap`
            FROM
                `nom_liq_incap`
                INNER JOIN `nom_incapacidad` 
                    ON (`nom_liq_incap`.`id_incapacidad` = `nom_incapacidad`.`id_incapacidad`)
            WHERE (`nom_liq_incap`.`id_nomina` IN ($ids_nominas))
            GROUP BY `nom_incapacidad`.`id_empleado`";
    $rs = $cmd->query($sql);
    $incapacidades = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , SUM(`val_bsp`) AS `tot_bsp`
                , `id_nomina`
            FROM
                `nom_liq_bsp`
            WHERE (`id_nomina` IN ($ids_nominas))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $bon_serv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , SUM(`val_compensa`) AS `tot_compensa`
                , `id_nomina`
            FROM
                `nom_liq_compesatorio`
            WHERE (`id_nomina` IN ($ids_nominas))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $compensatorio = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_indemniza_vac`.`id_empleado`
                , SUM(`nom_liq_indemniza_vac`.`val_liq`) AS `tot_indem`
                , `nom_liq_indemniza_vac`.`id_nomina`
            FROM
                `nom_liq_indemniza_vac`
                INNER JOIN `nom_indemniza_vac` 
                    ON (`nom_liq_indemniza_vac`.`id_indemnizacion` = `nom_indemniza_vac`.`id_indemniza`)
            WHERE (`nom_liq_indemniza_vac`.`id_nomina` IN ($ids_nominas))
            GROUP BY `nom_indemniza_vac`.`id_empleado`";
    $rs = $cmd->query($sql);
    $indemniza = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_empleado`
                , `id_nomina`
                , SUM(`val_ret`) AS `tot_rfte`
            FROM
                `nom_retencion_fte`
            WHERE (`id_nomina` IN ($ids_nominas))
            GROUP BY `id_empleado`";
    $rs = $cmd->query($sql);
    $retencion = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

function addEspace($valor)
{
    $array_val = preg_split('//u', $valor, 0, PREG_SPLIT_NO_EMPTY);
    $val = '';
    foreach ($array_val as $key => $value) {
        $val .= ' ' . $value . ' ';
    }
    return $val;
}
require_once '../../../vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;

$patrimonio = 4500 * $uvts['valor'];
$ingresos = 1400 * $uvts['valor'];
$nit = addEspace($empresa['nit']);
$dv = $empresa['dig_ver'];
$razon_soc = $empresa['nombre'];
$periodo_inicia = date('Y - m - d', strtotime($fecIni));
$periodo_fin = date('Y - m - d', strtotime($fecFin));
$fec_expide = date('Y-m-d');
$lugar_retiene = $empresa['nom_municipio'];
$cd = addEspace($empresa['codigo_dpto']);
$c_mun = addEspace($empresa['codigo_municipio']);
$generados = 0;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_user = $_SESSION['id_user'];
$tipo_user = 'user';
foreach ($list_empdo as $le) {
    $res['status'] = 'ok';
    $id_empdo = $le['id_empleado'];
    $plantilla = new TemplateProcessor('plantilla_form220.docx');
    $plantilla->setValue('vigencia', $vigencia);
    $plantilla->setValue('nit', $nit);
    $plantilla->setValue('dv', $dv);
    $plantilla->setValue('razon_soc', $razon_soc);
    $plantilla->setValue('periodo_inicia', $periodo_inicia);
    $plantilla->setValue('periodo_fin', $periodo_fin);
    $plantilla->setValue('fec_expide', $fec_expide);
    $plantilla->setValue('lugar_retiene', $lugar_retiene);
    $plantilla->setValue('cd', $cd);
    $plantilla->setValue('c_mun', $c_mun);
    $plantilla->setValue('patrimonio', pesos($patrimonio));
    $plantilla->setValue('ingresos', pesos($ingresos));
    $key = array_search($id_empdo, array_column($salarios, 'id_empleado'));
    $total_salario = $key !== false ? $salarios[$key]['tot_salario'] : 0;
    $grepresenta = $key !== false ? $salarios[$key]['tot_grep'] : 0;
    $val_representacion = pesos2($grepresenta);
    $key = array_search($id_empdo, array_column($prima, 'id_empleado'));
    $pri = $key !== false ? $prima[$key]['tot_prima'] : 0;
    $key = array_search($id_empdo, array_column($prima_nav, 'id_empleado'));
    $pri_nav = $key !== false ? $prima_nav[$key]['tot_prima_nan'] : 0;
    $key = array_search($id_empdo, array_column($compensatorio, 'id_empleado'));
    $compensacion = $key !== false ? $compensatorio[$key]['tot_compensa'] : 0;
    $val_compesaciones = pesos2($compensacion);
    $key = array_search($id_empdo, array_column($incapacidades, 'id_empleado'));
    $incap = $key !== false ? $incapacidades[$key]['tot_incap'] : 0;
    $salary = $total_salario + $compensacion + $pri + $pri_nav + $incap;
    $val_salarios = pesos2($salary);
    $vario = 0;
    $val_varios = pesos2($vario);
    $honorario = 0;
    $val_honorarios = pesos2($honorario);
    $servicio = 0;
    $val_servicios = pesos2($servicio);
    $comision = 0;
    $val_comisiones = pesos2($comision);
    $key = array_search($id_empdo, array_column($vacaciones, 'id_empleado'));
    $vacs = $key !== false ? $vacaciones[$key]['tot_liq'] + $vacaciones[$key]['tot_prima_vac'] + $vacaciones[$key]['tot_bon_recrea'] : 0;
    $key = array_search($id_empdo, array_column($bon_serv, 'id_empleado'));
    $bsp = $key !== false ? $bon_serv[$key]['tot_bsp'] : 0;
    $prestaciones = $vacs + $bsp;
    $val_presociales = pesos2($prestaciones);
    $key = array_search($id_empdo, array_column($viaticos, 'id_empleado'));
    $viatics = $key !== false ? $viaticos[$key]['tot_viaticos'] : 0;
    $val_viaticos = pesos2($viatics);
    $key = array_search($id_empdo, array_column($indemniza, 'id_empleado'));
    $otros = $key !== false ? $indemniza[$key]['tot_indem'] : 0;
    $val_otros = pesos2($otros);
    $key = array_search($id_empdo, array_column($cesantias, 'id_empleado'));
    $cesant = $key !== false ? $cesantias[$key]['tot_cesantias'] + $cesantias[$key]['tot_icesantias'] : 0;
    $val_cesantias = pesos2($cesant);
    $penson = 0;
    $val_pension = pesos2($penson); //Pensiones de jubilación, vejez o invalidez
    $tot_ing = $salary + $vario + $honorario + $servicio + $comision + $prestaciones + $viatics + $grepresenta + $compensacion + $incap + $otros + $cesant;
    $key = array_search($id_empdo, array_column($deduciones, 'id_empleado'));
    if ($key !== false) {
        $t_salud = $deduciones[$key]['salud'];
        $t_pension = $deduciones[$key]['pension'] + $deduciones[$key]['pension_solidaria'];
    } else {
        $t_salud = 0;
        $t_pension = 0;
    }
    $t_1 = 0;
    $total_salud = pesos2($t_salud);
    $total_pension = pesos2($t_pension);
    $total1 = $total2 = $total3 = $total4 = pesos2($t_1);
    $key = array_search($id_empdo, array_column($retencion, 'id_empleado'));
    $t_aporte = $key !== false ? $retencion[$key]['tot_rfte'] : 0;
    $total_aportes = pesos2($t_aporte);
    $total_ingresos = pesos2($tot_ing);
    $r_covid = 0;
    $ret_covid = pesos2($r_covid);
    $plantilla->setValue('tip_d', $le['codigo_ne']);
    $plantilla->setValue('no_doc', number_format($le['no_documento'], 0, ",", "."));
    $plantilla->setValue('apellido1', mb_strtoupper($le['apellido1']));
    $plantilla->setValue('apellido2', mb_strtoupper($le['apellido2']));
    $plantilla->setValue('nombre1', mb_strtoupper($le['nombre1']));
    $plantilla->setValue('nombre2', mb_strtoupper($le['nombre2']));
    $plantilla->setValue('val_salarios', $val_salarios);
    $plantilla->setValue('val_varios', $val_varios);
    $plantilla->setValue('val_honorarios', $val_honorarios);
    $plantilla->setValue('val_servicios', $val_servicios);
    $plantilla->setValue('val_comisiones', $val_comisiones);
    $plantilla->setValue('val_presociales', $val_presociales);
    $plantilla->setValue('val_viaticos', $val_viaticos);
    $plantilla->setValue('val_representacion', $val_representacion);
    $plantilla->setValue('val_compesaciones', $val_compesaciones);
    $plantilla->setValue('val_otros', $val_otros);
    $plantilla->setValue('val_cesantias', $val_cesantias);
    $plantilla->setValue('val_pension', $val_pension);
    $plantilla->setValue('total_ingresos', $total_ingresos);
    $plantilla->setValue('total_salud', $total_salud);
    $plantilla->setValue('total_pension', $total_pension);
    $plantilla->setValue('total1', $total1);
    $plantilla->setValue('total2', $total2);
    $plantilla->setValue('total3', $total3);
    $plantilla->setValue('total4', $total4);
    $plantilla->setValue('total_aportes', $total_aportes);
    $plantilla->setValue('ret_covid', $ret_covid);
    $archivo = 'F220_' . $empleado . '.docx';
    $plantilla->saveAs($archivo);
    /*
    $pdf = 'f220.pdf';
    $filepdf = $le['no_documento'] . '_' . $vigencia . '.pdf';
    $tempLibreOfficeProfile = sys_get_temp_dir() . "\\LibreOfficeProfile" . rand(100000, 999999);
    $convertir = '"C:\Program Files\LibreOffice\program\soffice.exe" "-env:UserInstallation=file:///' . str_replace("\\", "/", $tempLibreOfficeProfile) . '" --headless --convert-to pdf "' . $archivo . '" --outdir "' . str_replace("\\", "/", dirname($pdf)) . '"';
    //$convertir = '/opt/libreoffice7.5/program/soffice --headless --convert-to pdf "$archivo" --outdir "$pdf"';
    exec($convertir);*/
    $res['msg'] = base64_encode(file_get_contents($archivo));
    $res['name'] = $archivo;
    unlink($archivo);
    //unlink($pdf);
}
echo json_encode($res);
