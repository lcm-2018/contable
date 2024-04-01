<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$vigencia = $_SESSION['vigencia'];
$data = explode(',', file_get_contents("php://input"));
$id_nomina = $data[0];
$crp = $data[1];
$tipo_nomina = $data[2];
$id_api_sena = 1245;
$id_api_icbf = 1247;
$id_api_comfam = 1246;
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
$administrativo = $valores['administrativo'];
$operativo = $valores['operativo'];
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
    $sql = "SELECT `id_tes_cuenta`, `cta_contable` FROM `seg_tes_cuentas` WHERE (`id_tes_cuenta` = 1)";
    $rs = $cmd->query($sql);
    $banco = $rs->fetch(PDO::FETCH_ASSOC);
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
}
$nom_mes = isset($meses[$nomina['mes']]) ? 'MES DE ' . mb_strtoupper($meses[$nomina['mes']]) : '';
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha = $date->format('Y-m-d');
$objeto = "PAGO NOMINA PATRONAL " . $cual . " N° " . $nomina['id_nomina'] . ' ' . $nom_mes . "VIGENCIA " . $nomina['vigencia'];
$sede = 1;
$iduser = $_SESSION['id_user'];
$fecha2 = $date->format('Y-m-d H:i:s');
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
try {
    $tipo_mov = 'COP';
    $estado = 0;
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $query = "INSERT INTO `pto_documento_detalles` (`id_pto_doc`, `tipo_mov`, `id_tercero_api`, `rubro`, `valor`,`estado`,`id_auto_dep`,`id_ctb_doc`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $crp, PDO::PARAM_INT);
    $query->bindParam(2, $tipo_mov, PDO::PARAM_STR);
    $query->bindParam(3, $id_tercero, PDO::PARAM_INT);
    $query->bindParam(4, $rubro, PDO::PARAM_STR);
    $query->bindParam(5, $valorCdp, PDO::PARAM_STR);
    $query->bindParam(6, $estado, PDO::PARAM_INT);
    $query->bindParam(7, $id_doc, PDO::PARAM_INT);
    $query->bindParam(8, $id_doc_nom, PDO::PARAM_INT);
    foreach ($rubros as $rb) {
        $tipo = $rb['id_tipo'];
        $valorCdp = 0;
        switch ($tipo) {
            case 11:
                $valorCdp = $administrativo['comfam'] > 0 ? $administrativo['comfam'] : 0;
                $rubro = $rb['r_admin'];
                $id_tercero = $id_api_comfam;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                $rubro = $rb['r_operativo'];
                $valorCdp = $operativo['comfam'] > 0 ? $operativo['comfam'] : 0;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
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
                        $valorCdp = $value;
                        if ($valorCdp > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 15:
                $valorCdp = $administrativo['icbf'] > 0 ? $administrativo['icbf'] : 0;
                $rubro = $rb['r_admin'];
                $id_tercero = $id_api_icbf;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                $rubro = $rb['r_operativo'];
                $valorCdp = $operativo['icbf'] > 0 ? $operativo['icbf'] : 0;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 16:
                $valorCdp = $administrativo['sena'] > 0 ? $administrativo['sena'] : 0;
                $rubro = $rb['r_admin'];
                $id_tercero = $id_api_sena;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                $rubro = $rb['r_operativo'];
                $valorCdp = $operativo['sena'] > 0 ? $operativo['sena'] : 0;
                if ($valorCdp > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            default:
                $valorCdp = 0;
                break;
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
    $query->bindParam(8, $crp, PDO::PARAM_INT);
    $query->bindParam(9, $id_rte, PDO::PARAM_INT);
    $query->bindParam(10, $id_fac, PDO::PARAM_INT);
    $query->bindParam(11, $id_tipo_bn_sv, PDO::PARAM_INT);
    $query->bindParam(12, $iduser, PDO::PARAM_INT);
    $query->bindParam(13, $fecha2);
    foreach ($cuentas['admin'] as $ca) {
        $tipo = $ca['id_tipo'];
        $cuenta = $ca['cuenta'];
        $valor = 0;
        switch ($tipo) {
            case 11:
                $valor = $administrativo['comfam'] > 0 ? $administrativo['comfam'] : 0;
                $id_tercero = $id_api_comfam;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 12:
                if (!empty($administrativo['eps'])) {
                    $epss = $administrativo['eps'];
                    foreach ($epss as $key => $value) {
                        $id_tercero = $idsTercer['eps'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 13:
                if (!empty($administrativo['arl'])) {
                    $arls = $administrativo['arl'];
                    foreach ($arls as $key => $value) {
                        $id_tercero = $idsTercer['arl'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 14:
                if (!empty($administrativo['afp'])) {
                    $afps = $administrativo['afp'];
                    foreach ($afps as $key => $value) {
                        $id_tercero = $idsTercer['afp'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 15:
                $valor = $administrativo['icbf'] > 0 ? $administrativo['icbf'] : 0;
                $id_tercero = $id_api_icbf;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 16:
                $valor = $administrativo['sena'] > 0 ? $administrativo['sena'] : 0;
                $id_tercero = $id_api_sena;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            default:
                $valor = 0;
                break;
        }
    }
    foreach ($cuentas['urg'] as $cu) {
        $tipo = $cu['id_tipo'];
        $cuenta = $cu['cuenta'];
        $valor = 0;
        switch ($tipo) {
            case 11:
                $valor = $operativo['comfam'] > 0 ? $operativo['comfam'] : 0;
                $id_tercero = $id_api_comfam;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 12:
                if (!empty($operativo['eps'])) {
                    $epss = $operativo['eps'];
                    foreach ($epss as $key => $value) {
                        $id_tercero = $idsTercer['eps'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 13:
                if (!empty($operativo['arl'])) {
                    $arls = $operativo['arl'];
                    foreach ($arls as $key => $value) {
                        $id_tercero = $idsTercer['arl'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 14:
                if (!empty($operativo['afp'])) {
                    $afps = $operativo['afp'];
                    foreach ($afps as $key => $value) {
                        $id_tercero = $idsTercer['afp'][$key];
                        $valor = $value;
                        if ($valor > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 15:
                $valor = $operativo['icbf'] > 0 ? $operativo['icbf'] : 0;
                $id_tercero = $id_api_icbf;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 16:
                $valor = $operativo['sena'] > 0 ? $operativo['sena'] : 0;
                $id_tercero = $id_api_sena;
                if ($valor > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            default:
                $valor = 0;
                break;
        }
    }
    $valor = 0;
    foreach ($cuentas['pasivo'] as $cp) {
        $tipo = $cp['id_tipo'];
        $cuenta = $cp['cuenta'];
        $credito = 0;
        switch ($tipo) {
            case 11:
                $credito = $totales['comfam'] > 0 ? $totales['comfam'] : 0;
                $id_tercero = $id_api_comfam;
                if ($credito > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 12:
                if (!empty($totales['eps'])) {
                    $epss = $totales['eps'];
                    foreach ($epss as $key => $value) {
                        $id_tercero = $idsTercer['eps'][$key];
                        $credito = $value;
                        if ($credito > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 13:
                if (!empty($totales['arl'])) {
                    $arls = $totales['arl'];
                    foreach ($arls as $key => $value) {
                        $id_tercero = $idsTercer['arl'][$key];
                        $credito = $value;
                        if ($credito > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 14:
                if (!empty($totales['afp'])) {
                    $afps = $totales['afp'];
                    foreach ($afps as $key => $value) {
                        $id_tercero = $idsTercer['afp'][$key];
                        $credito = $value;
                        if ($credito > 0) {
                            $query->execute();
                            if (!($cmd->lastInsertId() > 0)) {
                                echo $query->errorInfo()[2];
                            }
                        }
                    }
                }
                break;
            case 15:
                $credito = $totales['icbf'] > 0 ? $totales['icbf'] : 0;
                $id_tercero = $id_api_icbf;
                if ($credito > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            case 16:
                $credito = $totales['sena'] > 0 ? $totales['sena'] : 0;
                $id_tercero = $id_api_sena;
                if ($credito > 0) {
                    $query->execute();
                    if (!($cmd->lastInsertId() > 0)) {
                        echo $query->errorInfo()[2];
                    }
                }
                break;
            default:
                $credito = 0;
                break;
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $estado = 4;
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
    $query = "UPDATE `nom_nomina_pto_ctb_tes` SET `cnom` = ? WHERE `id_nomina` = ? AND `tipo` = ? AND `crp`  = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_doc_nom, PDO::PARAM_INT);
    $query->bindParam(2, $id, PDO::PARAM_INT);
    $query->bindParam(3, $tipo_nomina, PDO::PARAM_STR);
    $query->bindParam(4, $crp, PDO::PARAM_INT);
    $query->execute();
    if (!($cmd->lastInsertId() > 0)) {
        echo $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo 'ok';
