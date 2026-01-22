<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
$anio = $_SESSION['vigencia'];
$data = json_decode(file_get_contents('php://input'), true);
$id_nomina = isset($data['id']) ? $data['id'] : exit('Acción no permitida');

include '../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_nomina`, `mes`, `fec_reg`
            FROM
                `nom_nominas`
            WHERE (`id_nomina` = $id_nomina) LIMIT 1";
    $rs = $cmd->query($sql);
    $data_nomina = $rs->fetch();
    $mes = $data_nomina['mes'];
    $fec_liq = date('Y-m-d', strtotime($data_nomina['fec_reg']));
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$dia = '01';
switch ($mes) {
    case '01':
    case '03':
    case '05':
    case '07':
    case '08':
    case '10':
    case '12':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-31';
        break;
    case '02':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        if (date('L', strtotime("$anio-01-01")) === '1') {
            $bis = '29';
        } else {
            $bis = '28';
        }
        $fec_f = $anio . '-' . $mes . '-' . $bis;
        break;
    case '04':
    case '06':
    case '09':
    case '11':
        $fec_i = $anio . '-' . $mes . '-' . $dia;
        $fec_f = $anio . '-' . $mes . '-30';
        break;
    default:
        echo 'Error Fatal';
        exit();
        break;
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_valxvig, id_concepto, valor,concepto
            FROM
                nom_valxvigencia
            INNER JOIN tb_vigencias 
                ON (nom_valxvigencia.id_vigencia = tb_vigencias.id_vigencia)
            INNER JOIN nom_conceptosxvigencia 
                ON (nom_valxvigencia.id_concepto = nom_conceptosxvigencia.id_concp)
            WHERE anio = '$anio' AND id_concepto = 4";
    $rs = $cmd->query($sql);
    $concec = $rs->fetch();
    $iNonce = intval($concec['valor']);
    $idiNonce = $concec['id_valxvig'];
    $sql = "UPDATE nom_valxvigencia SET valor = '$iNonce'+1 WHERE id_valxvig = '$idiNonce'";
    $rs = $cmd->query($sql);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$prima = array();
if ($mes === '06' || $mes === '12') {
    $periodo = $mes == '06' ? '1' : '2';
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $sql = "SELECT id_empleado, cant_dias, val_liq_ps, periodo, anio
                FROM
                    nom_liq_prima
                WHERE periodo = '$periodo' AND anio = '$anio'";
        $rs = $cmd->query($sql);
        $prima = $rs->fetchAll();
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                fech_inicio
                , fec_retiro
                , mes
                , correo
                , telefono
                , codigo_netc
                , nom_tipo_empleado.codigo AS tip_emp
                , nom_subtipo_empl.codigo AS subt_emp,alto_riesgo_pension
                , tb_tipos_documento.codigo AS tip_doc
                , codigo_ne
                , no_documento
                , apellido1
                , apellido2
                , nombre1
                , nombre2
                , codigo_pais
                , codigo_departamento
                , nom_departamento
                , codigo_municipio
                , nom_municipio
                , direccion
                , salario_integral
                , nom_tipo_contrato.codigo AS tip_contrato
                , nom_empleado.id_empleado
            FROM
                nom_empleado
            INNER JOIN tb_tipos_documento 
                ON (nom_empleado.tipo_doc = tb_tipos_documento.id_tipodoc)
            INNER JOIN nom_tipo_empleado 
                ON (nom_empleado.tipo_empleado = nom_tipo_empleado.id_tip_empl)
            INNER JOIN nom_subtipo_empl 
                ON (nom_empleado.subtipo_empleado = nom_subtipo_empl.id_sub_emp)
            INNER JOIN tb_paises 
                ON (nom_empleado.pais = tb_paises.id_pais)
            INNER JOIN tb_departamentos 
                ON (nom_empleado.departamento = tb_departamentos.id_departamento)
            INNER JOIN tb_municipios 
                ON (nom_empleado.municipio = tb_municipios.id_municipio)
            INNER JOIN nom_tipo_contrato 
                ON (nom_empleado.tipo_contrato = nom_tipo_contrato.id_tip_contrato)
            INNER JOIN nom_liq_salario 
                ON (nom_liq_salario.id_empleado = nom_empleado.id_empleado)
            WHERE `nom_liq_salario`.`id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $empleados = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_datos_ips`.`id_ips`
                , `tb_datos_ips`.`nit_ips` AS `nit`
                , `tb_datos_ips`.`email_ips` AS `correo`
                , `tb_datos_ips`.`telefono_ips` AS `telefono`
                , `tb_datos_ips`.`razon_social_fe` AS `nombre`
                , 'COLOMBIA' AS `nom_pais`
                , 'CO' AS `codigo_pais`
                , `tb_departamentos`.`codigo_departamento`
                , `tb_departamentos`.`nom_departamento`
                , `tb_municipios`.`codigo_municipio`
                , `tb_municipios`.`nom_municipio`
                , `tb_municipios`.`cod_postal`
                , `tb_datos_ips`.`direccion_ips` AS `direccion`
                , `tb_datos_ips`.`url_taxxa` AS `endpoint`
                , '2' AS `tipo_organizacion`
                , 'R-99-PN' AS `resp_fiscal`
                , '2' AS `reg_fiscal`
                , `tb_datos_ips`.`sEmail` AS `user_prov`
                , `tb_datos_ips`.`sPass` AS `pass_prov`
            FROM
                `tb_datos_ips`
                INNER JOIN `tb_municipios` 
                    ON (`tb_datos_ips`.`idmcpio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `tb_departamentos`
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`)";
    $rs = $cmd->query($sql);
    $empresa = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT nom_empleado.id_empleado,forma_pago,  nom_metodo_pago.codigo, nom_banco, tb_tipo_cta.tipo_cta, cuenta_bancaria
            FROM
                nom_liq_salario
            INNER JOIN nom_metodo_pago 
                ON (nom_liq_salario.metodo_pago = nom_metodo_pago.id_metodo_pago)
            INNER JOIN nom_empleado 
                ON (nom_liq_salario.id_empleado = nom_empleado.id_empleado)
            INNER JOIN tb_bancos 
                ON (nom_empleado.id_banco = tb_bancos.id_banco)
            INNER JOIN tb_tipo_cta 
                ON (nom_empleado.tipo_cta = tb_tipo_cta.id_tipo_cta)
            WHERE nom_liq_salario.id_nomina = $id_nomina";
    $rs = $cmd->query($sql);
    $bancaria = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT *
            FROM nom_liq_dlab_auxt
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $liqdialab = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, id_tipo, nom_liq_incap.fec_inicio, nom_liq_incap.fec_fin, mes, anios, dias_liq, pago_empresa, pago_eps, pago_arl
            FROM
                nom_liq_incap
            INNER JOIN nom_incapacidad 
                ON (nom_liq_incap.id_incapacidad = nom_incapacidad.id_incapacidad)
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
    $sql = "SELECT id_empleado, mes_lic, anio_lic, nom_liq_licmp.fec_inicio, nom_liq_licmp.fec_fin, dias_liqs, val_liq 
            FROM
                nom_liq_licmp
            INNER JOIN nom_licenciasmp 
                ON (nom_liq_licmp.id_licmp = nom_licenciasmp.id_licmp)
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $lic = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, mes_vac, anio_vac, dias_liqs, val_liq
            FROM
                nom_liq_vac
            INNER JOIN nom_vacaciones
                ON (nom_liq_vac.id_vac = nom_vacaciones.id_vac)
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $vac = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                id_liqpresoc, id_empleado, id_contrato, val_vacacion, val_cesantia, val_interes_cesantia, val_prima,mes_prestaciones, anio_prestaciones, anio_prestaciones
            FROM
                nom_liq_prestaciones_sociales
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $presoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, cant_dias
            FROM nom_liq_dias_lab
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $diaslaborados = $rs->fetchAll();
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
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $segsoc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, descripcion_lib, val_mes_lib
            FROM
                nom_liq_libranza
            INNER JOIN nom_libranzas 
                ON (nom_liq_libranza.id_libranza = nom_libranzas.id_libranza)
            WHERE `id_nomina` = $id_nomina AND estado = 1";
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
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $emb = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, val_aporte, porcentaje_cuota
            FROM
                nom_liq_sindicato_aportes
            INNER JOIN nom_cuota_sindical
                ON (nom_liq_sindicato_aportes.id_cuota_sindical = nom_cuota_sindical.id_cuota_sindical)
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $sind = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_empleado, nom_horas_ex_trab.id_he,codigo, desc_he, factor, fec_inicio, fec_fin, hora_inicio, hora_fin, cantidad_he, val_liq, factor
            FROM
                nom_horas_ex_trab
            INNER JOIN nom_tipo_horaex 
                ON (nom_horas_ex_trab.id_he = nom_tipo_horaex.id_he)
            INNER JOIN nom_liq_horex 
                ON (nom_liq_horex.id_he_lab = nom_horas_ex_trab.id_he_trab)
            WHERE `id_nomina` = $id_nomina
            ORDER BY id_he";
    $rs = $cmd->query($sql);
    $hoex = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

$viaticos = [];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_rte_fte, id_empleado, val_ret, mes, anio
            FROM
                nom_retencion_fte
            WHERE `id_nomina` = $id_nomina";
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
                id_empleado
            FROM
                nom_soporte_ne
            WHERE `mes` = $mes AND `anio` = $anio";
    $rs = $cmd->query($sql);
    $electronica = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_empleado`, `val_liq`
            FROM
                `nom_liq_salario`
            WHERE `id_nomina` = $id_nomina";
    $rs = $cmd->query($sql);
    $neto = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo_envio = 'prod';
$tipo_ref = 'NE';
$response['msg'] = 'ok';
$response['procesados'] = 'No se envió ningún empleado';

$errores = '';
if ($mes) {
    $jParams = [
        'sEmail' => 'hospitaljhu.financiera@gmail.com',
        'sPass' => 'Clave2105!'
    ];

    $jApi = [
        'sMethod' => 'classTaxxa.fjTokenGenerate',
        'jParams' => $jParams
    ];

    $url_taxxa = $empresa['endpoint'];
    $token = ['jApi' => $jApi];
    $datatoken = json_encode($token);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_URL, $url_taxxa);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datatoken);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $restoken = curl_exec($ch);
    $rst = json_decode($restoken);
    $tokenApi = $rst->jret->stoken;
    $hoy = date('Y-m-d');
    $ahora = (new DateTime('now', new DateTimeZone('America/Bogota')))->format('H:i:s');
    $nomindempl = '';
    $c = 1;
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $iduser = $_SESSION['id_user'];
    $procesado = 0;
    $incorrectos = 0;
    foreach ($empleados as $o) {
        $id = $o['id_empleado'];
        $keyelec = array_search($id, array_column($electronica, 'id_empleado'));
        $keyneto = array_search($id, array_column($neto, 'id_empleado'));
        $pagado = $neto[$keyneto]['val_liq'];
        $resnom = [];
        if ($keyelec === false && $pagado > 0) {
            $idempleado = $o['no_documento'] . '_' . $o['nombre1'] . '_' . $o['apellido1'] . '_NE_' . $id;
            $key = array_search($id, array_column($bancaria, 'id_empleado'));
            if (false !== $key) {
                $sPaymentForm = $bancaria[$key]['forma_pago'];
                $sPaymentMethod = $bancaria[$key]['codigo'];
                $sBankName = $bancaria[$key]['nom_banco'];
                $sBankAccountType = $bancaria[$key]['tipo_cta'];
                $sBankAccountNo = $bancaria[$key]['cuenta_bancaria'];
                $lPaymentDates = $fec_liq;
            } else {
                $sPaymentForm = $sPaymentMethod = $sBankName = $sBankAccountType = $sBankAccountNo = $lPaymentDates = $lPaymentDates = null;
            }
            $key = array_search($id, array_column($liqdialab, 'id_empleado'));
            $nDaysWorked = false !== $key ? intval($liqdialab[$key]['dias_liq']) : 0;
            $nAuxilioTransporte = false !== $key ? floatval($liqdialab[$key]['val_liq_auxt']) : 0;
            $salMensual = false !== $key ? floatval($liqdialab[$key]['val_liq_dias']) : 0;
            $nAuxilioAlimenta = false !== $key ? floatval($liqdialab[$key]['aux_alim']) : 0;
            $key = array_search($id, array_column($presoc, 'id_empleado'));
            if (false !== $key) {
                $valcesant = floatval($presoc[$key]['val_cesantia']);
                $porcentaje = 12;
                $nPagoIntereses = floatval($presoc[$key]['val_interes_cesantia']);
                $valprimames = floatval($presoc[$key]['val_prima']);
                $key = array_search($id, array_column($diaslaborados, 'id_empleado'));
                if (false !== $key) {
                    $diasprimames = intval($diaslaborados[$key]['cant_dias']);
                }
            } else {
                $valcesant = $porcentaje = $nPagoIntereses = $valprimames = $diasprimames = null;
            }
            $key = array_search($id, array_column($viaticos, 'id_emplead'));
            $nViaticoManuAlojNS = false !== $key ? floatval($viaticos[$key]['tot_viat']) : null;
            $key = array_search($id, array_column($incap, 'id_empleado'));
            if (false !== $key) {
                $valincap = floatval($incap[$key]['pago_empresa'] + $incap[$key]['pago_eps'] + $incap[$key]['pago_arl']);
                $tipoincap = intval($incap[$key]['id_tipo']);
                $inincap =  $incap[$key]['fec_inicio'];
                $diaincap =  intval($incap[$key]['dias_liq']);
                $finincap =  $incap[$key]['fec_fin'];
            } else {
                $valincap = $tipoincap = $inincap = $finincap = $diaincap = null;
            }
            $key = array_search($id, array_column($lic, 'id_empleado'));
            if (false !== $key) {
                $vallic = floatval($lic[$key]['val_liq']);
                $inlic =  $lic[$key]['fec_inicio'];
                $dialic =  intval($lic[$key]['dias_liqs']);
                $finlic =  $lic[$key]['fec_fin'];
            } else {
                $vallic = $inlic = $dialic = $finlic = null;
            }
            $key = array_search($id, array_column($emb, 'id_empleado'));
            $valEmbargo = false !== $key ? floatval($emb[$key]['val_mes_embargo']) : null;
            $key = array_search($id, array_column($sind, 'id_empleado'));
            if (false !== $key) {
                $valSind = floatval($sind[$key]['val_aporte']);
                $porcSind =  null;
            } else {
                $valSind = $porcSind = null;
            }
            $key = array_search($id, array_column($segsoc, 'id_empleado'));
            if (false !== $key) {
                $salud = floatval($segsoc[$key]['aporte_salud_emp']);
                $pension =  floatval($segsoc[$key]['aporte_pension_emp']);
                $psolid = intval($segsoc[$key]['aporte_solidaridad_pensional']) > 0 ? floatval($segsoc[$key]['aporte_solidaridad_pensional']) : null;
                $pPS = intval($psolid) > 0 ? $segsoc[$key]['porcentaje_ps'] : null;
                if ($psolid > 0) {
                    $psolida =  ($psolid * 0.5) / $pPS;
                    $pPSa = 0.50;
                    $psolidb = $psolid - $psolida;
                    $pPSb = $pPS - 0.50;
                    $pPSa = $pPSa . '0';
                    $pPSb = $pPSb . '0';
                } else {
                    $psolida =  null;
                    $pPSa = null;
                    $psolidb = null;
                    $pPSb = null;
                }
            } else {
                $salud = $pension = $psolid =  $pPS  = null;
            }
            $key = array_search($id, array_column($prima, 'id_empleado'));
            if (false !== $key) {
                $valprima = floatval($prima[$key]['val_liq_ps']);
                $diasprima =  intval($prima[$key]['cant_dias']);
            } else {
                $valprima = $diasprima = null;
            }
            $key = array_search($id, array_column($lib, 'id_empleado'));
            if (false !== $key) {
                $descripLib = $lib[$key]['descripcion_lib'];
                $valLib =  floatval($lib[$key]['val_mes_lib']);
            } else {
                $descripLib = $valLib = null;
            }
            $key = array_search($id, array_column($vac, 'id_empleado'));
            if (false !== $key) {
                $valvac = floatval($vac[$key]['val_liq']);
                $diavac =  intval($vac[$key]['dias_liqs']);
            } else {
                $valvac = $diavac = null;
            }
            $listhoex = [];
            $valHoEx = 0;
            foreach ($hoex as $he) {
                if ($he['id_empleado'] === $o['id_empleado']) {
                    switch (intval($he['codigo'])) {
                        case 1:
                            $tiphe = 'HED';
                            break;
                        case 2:
                            $tiphe =  'HEN';
                            break;
                        case 3:
                            $tiphe = 'HRN';
                            break;
                        case 4:
                            $tiphe = 'HEDDF';
                            break;
                        case 5:
                            $tiphe = 'HRDDF';
                            break;
                        case 6:
                            $tiphe =  'HENDF';
                            break;
                        case 7:
                            $tiphe = 'HRNDF';
                            break;
                    }
                    $listhoex[] = ['wWorktimeCode' => $tiphe, 'nquantity' => $he['cantidad_he'], 'nPaid' => floatval($he['val_liq']), 'nRateDelta' => floatval($he['factor']), 'tSince' =>  $he['fec_inicio'] . 'T' . $he['hora_inicio'], 'tUntil' => $he['fec_fin'] . 'T' . $he['hora_fin']];
                    $valHoEx = $valHoEx +  $he['val_liq'];
                }
            }
            $devengado = floatval($salMensual  + $nAuxilioTransporte + $nViaticoManuAlojNS + $nAuxilioAlimenta + $valincap + $vallic + $valprima + $valvac + $valHoEx);
            $ccesantia = $valcesant > 0 ? ['wIncomeCode' => 'Cesantias', 'nAmount' => $valcesant, 'nPagoIntereses' => $nPagoIntereses, 'nPercentage' => $porcentaje] : null;
            $cprima = $valprimames > 0 ? ['wIncomeCode' => 'Primas', 'nAmount' => $valprimames, 'nPagoNS' => 0, 'nPagoS' => $valprimames, 'nQuantity' => $diasprimames] : null;
            $ctransp = $nAuxilioTransporte > 0 ?  ['wIncomeCode' => 'Transporte', 'nAuxilioTransporte' =>  $nAuxilioTransporte, 'nViaticoManuAlojS' =>  null, 'nViaticoManuAlojNS' =>  $nViaticoManuAlojNS] : null;
            $cAlim = $nAuxilioAlimenta > 0 ?  ['wIncomeCode' => 'Auxilio', 'nAuxilioS' => $nAuxilioAlimenta, 'nAuxilioNS' =>  null] : null;
            $valincap = $valincap > 0 ? ['wIncomeCode' => 'Incapacidad', 'nAmount' =>   $valincap, 'sTipo' =>  $tipoincap, 'nQuantity' =>  $diaincap, 'tSince' =>  $inincap, 'tUntil' => $finincap] : null;
            $vallic = $vallic > 0 ? ['wIncomeCode' => 'LicenciaMP', 'tSince' => $inlic, 'tUntil' => $finlic, 'nAmount' =>  $vallic, 'nQuantity' => $dialic] : null;
            $bsp = ['wIncomeCode' => 'Bonificacion', 'nBonificacionS' =>  null, 'nBonificacionNS' =>  null];
            $aIncomes = [];
            if ($cprima !== null) {
                $aIncomes[] = $cprima;
            }
            if ($ccesantia !== null) {
                $aIncomes[] = $ccesantia;
            }
            if ($ctransp !== null) {
                $aIncomes[] = $ctransp;
            }
            if ($cAlim !== null) {
                $aIncomes[] = $cAlim;
            }
            if ($valincap !== null) {
                $aIncomes[] = $valincap;
            }
            if ($vallic !== null) {
                $aIncomes[] = $vallic;
            }
            if ($bsp !== null) {
                $aIncomes[] = $bsp;
            }
            /*$aIncomes = [
            //['wIncomeCode' => 'Teletrabajo', 'nAmount' => null],
            //['wIncomeCode' => 'ApoyoSost', 'nAmount' => null],
            //['wIncomeCode' => 'BonifRetiro', 'nAmount' => null],
            //['wIncomeCode' => 'Dotacion', 'nAmount' => null],
            //['wIncomeCode' => 'Indemnizacion', 'nAmount' => null],
            //['wIncomeCode' => 'Reintegro', 'nAmount' => null],
            //['wIncomeCode' => 'Comision', 'nAmount' => null],
            //['wIncomeCode' => 'PagoTercero', 'nAmount' => null],
            //['wIncomeCode' => 'Anticipo', 'nAmount' => null],
            //['wIncomeCode' => 'Comision', 'nAmount' => null],
            //['wIncomeCode' => 'Auxilio', 'nAuxilioS' => null, 'nAuxilioNS' =>  null],
            //['wIncomeCode' => 'Compensacion', 'nCompensacionO' =>  null, 'nCompensacionE' =>  null],
            //['wIncomeCode' => 'Bonificacion', 'nBonificacionS' =>  null, 'nBonificacionNS' =>  null],
            //['wIncomeCode' => 'BonoEPCTV', 'nPagoS' =>  null, 'nPagoNS' =>  null, 'nPagoAlimentacionS' =>  null, 'nPagoAlimentacionNS' =>  null],
            //['wIncomeCode' => 'LicenciaR', 'tSince' => null, 'tUntil' => null, 'nAmount' => null, 'nQuantity' =>  null],
            //['wIncomeCode' => 'LicenciaNR', 'tSince' => null, 'tUntil' => null, 'nQuantity' => null],
            //['wIncomeCode' => 'VacacionesComunes', 'nAmount' => null, 'nQuantity' => null, 'tSince' => null, 'tUntil' => null],
            // ['wIncomeCode' => 'VacacionesCompensadas', 'nAmount' => $valvac, 'nQuantity' => $diavac],
            //['wIncomeCode' => 'HuelgaLegal', 'nQuantity' => null, 'tSince' => null, 'tUntil' => null],
            //['wIncomeCode' => 'OtroConcepto', 'nConceptoS' => null, 'nConceptoNS' => null, 'sDescription' => null, 'xDescription' => null]
        ];*/
            $aContract = [
                [
                    'nsalarybase' => floatval($salMensual),
                    'wcontracttype' => mb_strtoupper($o['codigo_netc']),
                    'tcontractsince' => $o['fech_inicio'],
                    'tcontractuntil' => $o['fec_retiro'],
                    'wpayrollperiod' => '5',
                    'wdianemployeetype' => $o['tip_emp'],
                    'wdianemployeesubtype' => $o['subt_emp'],
                    'bAltoRiesgoPension' => ($o['alto_riesgo_pension'] == '1' ? true : false),
                    'bSalarioIntegral' => ($o['salario_integral'] == '1' ? true : false)
                ]
            ];
            $cemba = $valEmbargo > 0 ? ["wDeductionCode" => "EmbargoFiscal", "nAmount" => $valEmbargo] : null;
            $csind = $valSind > 0 ? ["wDeductionCode" => "Sindicato", "nAmount" =>  $valSind, "nPercentage" => $porcSind] : null;
            $cpsolidaria = $psolid > 0 ? ["wDeductionCode" => "FondoSP", "nPercentage" => $pPSa, "nDeduccionsp" => $psolida, "nDeduccionSub" => $psolidb, "nPorcentajeSub" => $pPSb] : null;
            $clib = $valLib > 0 ? ["wDeductionCode" => "Libranza", "nAmount" => $valLib, "sDescription" => $descripLib, "xDescription" => $descripLib == '' ? null : base64_encode($descripLib)] : null;
            $aDeductions = [];
            if ($cemba !== null) {
                $aDeductions[] = $cemba;
            }
            if ($csind !== null) {
                $aDeductions[] = $csind;
            }
            if ($cpsolidaria !== null) {
                $aDeductions[] = $cpsolidaria;
            }
            if ($clib !== null) {
                $aDeductions[] = $clib;
            }
            $aDeductions[] = ["wDeductionCode" => "Salud", "nAmount" => $salud, "nPercentage" => 4];
            $aDeductions[] = ["wDeductionCode" => "FondoPension", "nAmount" => $pension, "nPercentage" => 4];

            $key = array_search($id, array_column($retfte, 'id_empleado'));
            if (false !== $key) {
                $rtefte = floatval($retfte[$key]['val_ret']);
                if (!(intval($rtefte) === 0)) {
                    $aDeductions[] = ["wDeductionCode" => "RetencionFuente", "nAmount" => $rtefte];
                } else {
                    $rtefte = 0;
                }
            }
            $deducciones =  floatval($valEmbargo + $valSind + $salud + $pension + $psolid + $valLib + $rtefte);
            /*$aDeductions = [
            //["wDeductionCode" => "Educacion", "nAmount" => null],
            //["wDeductionCode" => "Reintegro", "nAmount" => null],
            //["wDeductionCode" => "Anticipo", "nAmount" => null],
            //["wDeductionCode" => "PagoTercero", "nAmount" => null],
            //["wDeductionCode" => "OtraDeduccion", "nAmount" => null],
            //["wDeductionCode" => "Deuda", "nAmount" => null],
            //["wDeductionCode" => "Cooperativa", "nAmount" => null],
            //["wDeductionCode" => "AFC", "nAmount" => null],
            //["wDeductionCode" => "PensionVoluntaria", "nAmount" => null],
            //["wDeductionCode" => "PlanComplementarios", "nAmount" => null],            
            //["wDeductionCode" => "Sancion", "nAmount" => null, "nSancionPriv" => null, "nSancionPublic" => null]
        ];*/
            $aWorkTimeDetails = $listhoex;
            try {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $sql = "SELECT consecutivo FROM nom_consecutivo_viaticos LIMIT 1";
                $rs = $cmd->query($sql);
                $cons = $rs->fetch();
                $consecutivo = !empty($cons) ? $cons['consecutivo'] : 1;
                $cmd = null;
            } catch (PDOException $e) {
                echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
            }
            $numero = $anio . $mes . str_pad($consecutivo, 3, "0", STR_PAD_LEFT);
            $idne = $tipo_ref . '-' . $numero;
            $indicene = strtolower($tipo_ref) . $numero;
            $empleado = [];
            $empleado[$idempleado] =
                [
                    'wdoctype' =>  $o['codigo_ne'],
                    'sDocId' => $o['no_documento'],
                    'sworkercode' => $o['id_empleado'],
                    'spersonnamefirst' => $o['nombre1'],
                    'lpersonnamesothers' => $o['nombre2']  == '' ? '-' : $o['nombre2'],
                    'spersonsurname' => $o['apellido1'],
                    'lpersonsurnameothers' => $o['apellido2'],
                    'jcontact' => [
                        "semail" => $o['correo'],
                        'jaddress' => [
                            'wCountrycode' => $o['codigo_pais'],
                            'sStateCode' => $o['codigo_departamento'],
                            'sCityCode' => $o['codigo_departamento'] . $o['codigo_municipio'],
                            'sstreet' => $o['direccion'],
                        ]
                    ],
                    'apayrollinfo' => [
                        'NE-' . $c => [
                            'xnotes' => base64_encode('Comentarios'),
                            'sreference' => $idne,
                            "sprefix" => $tipo_ref,
                            "ssuffix" => $numero,
                            'ndaysworked' => $nDaysWorked,
                            'ntotalincomes' => $devengado,
                            'ntotaldeductions' =>  $deducciones,
                            'nperiodbasesalary' => floatval($salMensual),
                            'npayable' => $devengado - $deducciones,
                            'aIncomes' => $aIncomes,
                            'aDeductions' => $aDeductions,
                            'aWorkTimeDetails' => $aWorkTimeDetails,
                        ]

                    ],
                    'aContract' => $aContract,
                    'aPaymentInfo' => [
                        [
                            'spaymentform' => $sPaymentForm,
                            'spaymentmethod' => $sPaymentMethod,
                            'sbankname' => $sBankName,
                            'sbankaccounttype' => $sBankAccountType,
                            'sbankaccountno' => $sBankAccountNo,
                            'lpaymentdates' => $lPaymentDates
                        ]
                    ],
                ];
            $c++;
            $jPayroll = [
                "wEnvironment" => $tipo_envio,
                'tcalculatedsince' => $fec_i,
                'tcalculateduntil' => $fec_f,
                'tissued' => $hoy,
                'jemployer' => [
                    'sbusinessname' => $empresa['nombre'],
                    'spersonnamefirst' => $empresa['nombre'],
                    'spersonnamesothers' => '',
                    'spersonsurname' => $empresa['nombre'],
                    'spersonsurnameothers' => '',
                    'wdoctype' => 'NIT',
                    'sDocID' => $empresa['nit'],
                    'jcontact' => [
                        'jAddress' => [
                            'wCountrycode' => $empresa['codigo_pais'],
                            'sStateCode' => $empresa['codigo_departamento'],
                            'sCityCode' => $empresa['codigo_departamento'] . $empresa['codigo_municipio'],
                            'sStreet' => $empresa['direccion'],
                        ]
                    ]
                ],
                'aWorkers' => $empleado
            ];
            $jParams = [
                'bAsync' => false,
                'jPayroll' => $jPayroll,
            ];

            $jApi = [
                'sMethod' => "classTaxxa.fjPayrollAdd",
                'jParams' => $jParams
            ];

            //echo json_encode($empjson);
            $nomina = [
                'sToken' => $tokenApi,
                //'iNonce' => $iNonce,
                'jApi' => $jApi
            ];
            $json_string = json_encode($nomina);
            $file = 'empleados' . $c . '.json';
            file_put_contents($file, $json_string);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, $empresa['endpoint']);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($nomina));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $resnom = json_decode(curl_exec($ch), true);
            if ($resnom['rerror'] == 0) {
                $shash = $resnom['aresult'][$indicene]['shash'];
                $sreference = $resnom['aresult'][$indicene]['sreference'];
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                    $sql = "INSERT INTO nom_soporte_ne (id_empleado, shash, referencia, mes, anio, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $sql = $cmd->prepare($sql);
                    $sql->bindParam(1, $id, PDO::PARAM_INT);
                    $sql->bindParam(2, $shash, PDO::PARAM_STR);
                    $sql->bindParam(3, $sreference, PDO::PARAM_STR);
                    $sql->bindParam(4, $mes, PDO::PARAM_STR);
                    $sql->bindParam(5, $anio, PDO::PARAM_STR);
                    $sql->bindParam(6, $iduser, PDO::PARAM_INT);
                    $sql->bindValue(7, $date->format('Y-m-d H:i:s'));
                    $sql->execute();
                    if ($cmd->lastInsertId() > 0) {
                        $consUp = $consecutivo + 1;
                        $sql = "UPDATE nom_consecutivo_viaticos SET consecutivo = ?";
                        $sql = $cmd->prepare($sql);
                        $sql->bindParam(1, $consUp, PDO::PARAM_INT);
                        $sql->execute();
                        $procesado++;
                    } else {
                        echo json_encode($sql->errorInfo()[2]);
                    }
                    $cmd = null;
                } catch (PDOException $e) {
                    echo json_encode($e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage());
                }
            } else {
                $incorrectos++;
                $mnj = '<ul>';
                $mnj .= '<li>' . $resnom['smessage'] . '</li>';
                $mnj .= '</ul>';
                $errores .= 'Error:' . $resnom['rerror'] . '<br>Mensaje: ' . $mnj . '----------<br>';
            }
        }
    }
}
$file = 'loglastsend.txt';
file_put_contents($file, json_encode($resnom));
$response['procesados'] = 'Se han procesado <b>' . $procesado . '</b> soporte(s) para nómina electrónica';
$response['error'] = $errores;
$response['incorrec'] = $incorrectos;
echo json_encode($response);
