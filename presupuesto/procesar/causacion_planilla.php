<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
include '../../conexion.php';
include '../../financiero/consultas.php';

$vigencia = $_SESSION['vigencia'];
$id_vigencia = $_SESSION['id_vigencia'];
$data = explode('|', file_get_contents("php://input"));
$id_nomina = $data[0];
$tipo_nomina = $data[1];
$fec_doc = $data[2];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_parafiscal`,`id_tercero_api`,`tipo`
            FROM `nom_parafiscales`
            ORDER BY `id_parafiscal` DESC";
    $rs = $cmd->query($sql);
    $parafiscales = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$kpf = array_search('SENA', array_column($parafiscales, 'tipo'));
$id_api_sena = $kpf !== false ? $parafiscales[$kpf]['id_tercero_api'] : exit('No se ha configurado el parafiscal SENA');
$kpf = array_search('ICBF', array_column($parafiscales, 'tipo'));
$id_api_icbf = $kpf !== false ? $parafiscales[$kpf]['id_tercero_api'] : exit('No se ha configurado el parafiscal ICBF');
$kpf = array_search('CAJA', array_column($parafiscales, 'tipo'));
$id_api_comfam = $kpf !== false ? $parafiscales[$kpf]['id_tercero_api'] : exit('No se ha configurado el parafiscal CAJA DE COMPENSACION');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `nom_cdp_empleados`.`rubro`
                , `nom_cdp_empleados`.`valor`
                , `pto_cargue`.`cod_pptal`
            FROM
                `nom_cdp_empleados`
                INNER JOIN `pto_cargue` 
                    ON (`nom_cdp_empleados`.`rubro` = `pto_cargue`.`id_cargue`)
            WHERE (`nom_cdp_empleados`.`id_nomina` = $id_nomina AND `nom_cdp_empleados`.`tipo` = 'PL')";
    $rs = $cmd->query($sql);
    $valxrubro = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

if (empty($valxrubro)) {
    echo 'No se ha generado una solicitud de CDP para esta nómina';
    exit();
} else {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $valida = false;
    $tabla = '<table class="table table-bordered table-striped table-hover table-sm" style="font-size: 12px; width: 100%">
                <thead>
                    <tr>
                        <th>Rubro</th>
                        <th>Valor</th>
                        <th>Saldo</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($valxrubro as $vr) {
        $rubro = $vr['rubro'];
        $valor = $vr['valor'];
        $cod_rubro = $vr['cod_pptal'];
        $respuesta = SaldoRubro($cmd, $rubro, $fec_doc, 0);
        $saldo = $respuesta['valor_aprobado'] - $respuesta['debito_cdp'] + $respuesta['credito_cdp'] + $respuesta['debito_mod'] - $respuesta['credito_mod'];
        $estado = $saldo >= $valor ? '<span class="badge badge-success">Disponible</span>' : '<span class="badge badge-danger">Sin Saldo</span>';
        if ($saldo < $valor) {
            $valida = true;
            $tabla .= '<tr>
                        <td>' . $cod_rubro . '</td>
                        <td class="text-right">$ ' . number_format($valor, 2, ',', '.') . '</td>
                        <td class="text-right">$ ' . number_format($saldo, 2, ',', '.') . '</td>
                        <td>' . $estado . '</td>
                    </tr>';
        }
    }
    $tabla .= '</tbody></table>';
    if ($valida) {
        echo $tabla;
        exit();
    }
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
            `id_nomina`, `mes`, `vigencia`, `tipo`
            FROM
                `nom_nominas`
            WHERE (`id_nomina` = $id_nomina) LIMIT 1";
    $rs = $cmd->query($sql);
    $nomina = $rs->fetch(PDO::FETCH_ASSOC);
    $mes = $nomina['mes'] != '' ? $nomina['mes'] : '00';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                * 
            FROM 
                (SELECT
                    `nom_empleado`.`id_empleado`
                    ,`nom_empleado`.`tipo_cargo`
                    , `nom_liq_segsocial_empdo`.`id_eps`
                    , `nom_liq_segsocial_empdo`.`id_arl`
                    , `nom_liq_segsocial_empdo`.`id_afp`
                    , `nom_epss`.`id_tercero_api`AS `id_api_eps`
                    , `nom_arl`.`id_tercero_api` AS `id_api_arl`
                    , `nom_afp`.`id_tercero_api` AS `id_api_afp`
                    , `nom_liq_segsocial_empdo`.`aporte_salud_emp`
                    , `nom_liq_segsocial_empdo`.`aporte_salud_empresa`
                    , `nom_liq_segsocial_empdo`.`aporte_pension_emp`
                    , `nom_liq_segsocial_empdo`.`aporte_solidaridad_pensional`
                    , `nom_liq_segsocial_empdo`.`aporte_pension_empresa`
                    , `nom_liq_segsocial_empdo`.`aporte_rieslab`
                FROM
                    `nom_empleado`
                    INNER JOIN `nom_liq_segsocial_empdo` 
                        ON (`nom_liq_segsocial_empdo`.`id_empleado` = `nom_empleado`.`id_empleado`)
                    INNER JOIN `nom_epss` 
                        ON (`nom_liq_segsocial_empdo`.`id_eps` = `nom_epss`.`id_eps`)
                    INNER JOIN `nom_arl` 
                        ON (`nom_liq_segsocial_empdo`.`id_arl` = `nom_arl`.`id_arl`)
                    INNER JOIN `nom_afp` 
                        ON (`nom_liq_segsocial_empdo`.`id_afp` = `nom_afp`.`id_afp`)
                WHERE  `nom_liq_segsocial_empdo`.`id_nomina` = $id_nomina) AS `t1`
            LEFT JOIN 
                (SELECT 
                    `nom_liq_parafiscales`.`id_empleado`
                    , `nom_liq_parafiscales`.`val_sena`
                    , `nom_liq_parafiscales`.`val_icbf`
                    , `nom_liq_parafiscales`.`val_comfam`
                    , `nom_liq_parafiscales`.`id_nomina`
                FROM 
                    `nom_liq_parafiscales`
                WHERE `id_nomina` =  $id_nomina) AS `t2`
            ON (`t1`.`id_empleado` = `t2`.`id_empleado`)";
    $rs = $cmd->query($sql);
    $patronales = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$totales = [];
$totales['comfam'] = 0;
$totales['icbf'] = 0;
$totales['sena'] = 0;
foreach ($patronales as $p) {
    $id_eps = $p['id_eps'];
    $id_arl = $p['id_arl'];
    $id_afp = $p['id_afp'];
    $totales['comfam'] += $p['val_comfam'];
    $totales['icbf'] += $p['val_icbf'];
    $totales['sena'] += $p['val_sena'];
    $valeps = isset($totales['eps'][$id_eps]) ? $totales['eps'][$id_eps] : 0;
    $valarl = isset($totales['arl'][$id_arl]) ? $totales['arl'][$id_arl] : 0;
    $valafp = isset($totales['afp'][$id_afp]) ? $totales['afp'][$id_afp] : 0;
    $totales['eps'][$id_eps] = $p['aporte_salud_empresa'] + $valeps;
    $totales['arl'][$id_arl] = $p['aporte_rieslab'] + $valarl;
    $totales['afp'][$id_afp] = $p['aporte_pension_empresa'] + $valafp;
}
$descuentos = [];
foreach ($patronales as $p) {
    $id_eps = $p['id_eps'];
    $id_afp = $p['id_afp'];
    $valeps = isset($descuentos['eps'][$id_eps]) ? $descuentos['eps'][$id_eps] : 0;
    $valafp = isset($descuentos['afp'][$id_afp]) ? $descuentos['afp'][$id_afp] : 0;
    $descuentos['eps'][$id_eps] = $p['aporte_salud_emp'] + $valeps;
    $descuentos['afp'][$id_afp] = $p['aporte_pension_emp'] + $valafp + $p['aporte_solidaridad_pensional'];
}
$valore = [];

foreach ($patronales as $p) {
    if ($p['tipo_cargo'] == 1) {
        $tipo = 'administrativo';
    } else if ($p['tipo_cargo'] == 2) {
        $tipo = 'operativo';
    }
    $id_eps = $p['id_eps'];
    $id_arl = $p['id_arl'];
    $id_afp = $p['id_afp'];
    $totsena = isset($valores[$tipo]['sena']) ? $valores[$tipo]['sena'] : 0;
    $toticbf = isset($valores[$tipo]['icbf']) ? $valores[$tipo]['icbf'] : 0;
    $totcomfam = isset($valores[$tipo]['comfam']) ? $valores[$tipo]['comfam'] : 0;
    $valores[$tipo]['sena'] = $p['val_sena'] + $totsena;
    $valores[$tipo]['icbf'] = $p['val_icbf'] + $toticbf;
    $valores[$tipo]['comfam'] = $p['val_comfam'] + $totcomfam;
    $valeps = isset($valores[$tipo]['eps'][$id_eps]) ? $valores[$tipo]['eps'][$id_eps] : 0;
    $valarl = isset($valores[$tipo]['arl'][$id_arl]) ? $valores[$tipo]['arl'][$id_arl] : 0;
    $valafp = isset($valores[$tipo]['afp'][$id_afp]) ? $valores[$tipo]['afp'][$id_afp] : 0;
    $valores[$tipo]['eps'][$id_eps] = $p['aporte_salud_empresa'] + $valeps;
    $valores[$tipo]['arl'][$id_arl] = $p['aporte_rieslab'] + $valarl;
    $valores[$tipo]['afp'][$id_afp] = $p['aporte_pension_empresa'] + $valafp;
}
$administrativo = isset($valores['administrativo']) ? $valores['administrativo'] : [];
$operativo = isset($valores['operativo']) ? $valores['operativo'] : [];
$idsTercer = [];
foreach ($patronales as $p) {
    $id_eps = $p['id_eps'];
    $id_arl = $p['id_arl'];
    $id_afp = $p['id_afp'];
    $idsTercer['eps'][$id_eps] = $p['id_api_eps'];
    $idsTercer['arl'][$id_arl] = $p['id_api_arl'];
    $idsTercer['afp'][$id_afp] = $p['id_api_afp'];
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
$cuentas = [];
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
$cuentas['admin'] = $cAdmin;
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
$cuentas['urg'] = $cUrg;
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
$cuentas['pasivo'] = $cPasivo;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tes_cuentas`.`estado`
                , `tes_cuentas`.`id_tes_cuenta`
                , `ctb_pgcp`.`cuenta` AS `cta_contable`
            FROM
                `tes_cuentas`
                INNER JOIN `ctb_pgcp` 
                    ON (`tes_cuentas`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE (`tes_cuentas`.`estado` = 1)";
    $rs = $cmd->query($sql);
    $banco = $rs->fetch(PDO::FETCH_ASSOC);
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
if ($nomina['tipo'] == 'N') {
    $cual = 'MENSUAL';
} else if ($nomina['tipo'] == 'PS') {
    $cual = 'DE PRESTACIONES SOCIALES';
} else if ($nomina['tipo'] == 'VC') {
    $cual = 'DE VACACIONES';
} else if ($nomina['tipo'] == 'PV') {
    $cual = 'DE PRIMA DE SERVICIOS';
} else if ($nomina['tipo'] == 'RA') {
    $cual = 'DE RETROACTIVO';
} else if ($nomina['tipo'] == 'CE') {
    $cual = 'DE CESANTIAS';
} else if ($nomina['tipo'] == 'IC') {
    $cual = 'DE INTERESES DE CESANTIAS';
} else if ($nomina['tipo'] == 'VS') {
    $cual = 'DE VACACIONES';
} else {
    $cual = 'OTRAS';
}
$nom_mes = isset($meses[$nomina['mes']]) ? 'MES DE ' . mb_strtoupper($meses[$nomina['mes']]) : '';
$id_pto = $pto['id_pto'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $date->format('Y-m-d');
$objeto = "PAGO NOMINA PATRONAL " . $cual . " N° " . $nomina['id_nomina'] . ' ' . $nom_mes . " VIGENCIA " . $nomina['vigencia'];
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
    $sql->bindParam(3, $fec_doc, PDO::PARAM_STR);
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
    $sql = "SELECT `id_tercero_api` FROM `tb_terceros` WHERE `nit_tercero` = " . $_SESSION['nit_emp'];
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
    $sql->bindParam(4, $fec_doc, PDO::PARAM_STR);
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
$contador = 0;
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
    $valor = 0;
    switch ($tipo) {
        case 11:
            $valor = isset($administrativo['comfam']) && $administrativo['comfam'] > 0 ? $administrativo['comfam'] : 0;
            $rubro = $rb['r_admin'];
            $id_tercero = $id_api_comfam;
            if ($valor > 0) {
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
            $rubro = $rb['r_operativo'];
            $valor = isset($operativo['comfam']) && $operativo['comfam'] > 0 ? $operativo['comfam'] : 0;
            if ($valor > 0) {
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
            break;
        case 12:
            if (!empty($administrativo['eps'])) {
                $rubro = $rb['r_admin'];
                $epss = $administrativo['eps'];
                foreach ($epss as $key => $value) {
                    $id_tercero = $idsTercer['eps'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            if (!empty($operativo['eps'])) {
                $rubro = $rb['r_operativo'];
                $epss = $operativo['eps'];
                foreach ($epss as $key => $value) {
                    $id_tercero = $idsTercer['eps'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            break;
        case 13:
            if (!empty($administrativo['arl'])) {
                $rubro = $rb['r_admin'];
                $arls = $administrativo['arl'];
                foreach ($arls as $key => $value) {
                    $id_tercero = $idsTercer['arl'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            if (!empty($operativo['arl'])) {
                $rubro = $rb['r_operativo'];
                $arls = $operativo['arl'];
                foreach ($arls as $key => $value) {
                    $id_tercero = $idsTercer['arl'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            break;
        case 14:
            if (!empty($administrativo['afp'])) {
                $rubro = $rb['r_admin'];
                $afps = $administrativo['afp'];
                foreach ($afps as $key => $value) {
                    $id_tercero = $idsTercer['afp'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            if (!empty($operativo['afp'])) {
                $rubro = $rb['r_operativo'];
                $afps = $operativo['afp'];
                foreach ($afps as $key => $value) {
                    $id_tercero = $idsTercer['afp'][$key];
                    $valor = $value;
                    if ($valor > 0) {
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
            break;
        case 15:
            $valor = isset($administrativo['icbf']) && $administrativo['icbf'] > 0 ? $administrativo['icbf'] : 0;
            $rubro = $rb['r_admin'];
            $id_tercero = $id_api_icbf;
            if ($valor > 0) {
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
            $rubro = $rb['r_operativo'];
            $valor = isset($operativo['icbf']) && $operativo['icbf'] > 0 ? $operativo['icbf'] : 0;
            if ($valor > 0) {
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
            break;
        case 16:
            $valor = isset($administrativo['sena']) && $administrativo['sena'] > 0 ? $administrativo['sena'] : 0;
            $rubro = $rb['r_admin'];
            $id_tercero = $id_api_sena;
            if ($valor > 0) {
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
            $rubro = $rb['r_operativo'];
            $valor = isset($operativo['sena']) && $operativo['sena'] > 0 ? $operativo['sena'] : 0;
            if ($valor > 0) {
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
            break;
        default:
            $valor = 0;
            break;
    }
}
$cmd = null;
$con = null;

try {
    $estado = 3;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `nom_nominas` SET `planilla` = ? WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $id_nomina, PDO::PARAM_INT);
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
    $query->bindParam(1, $id_nomina, PDO::PARAM_INT);
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
