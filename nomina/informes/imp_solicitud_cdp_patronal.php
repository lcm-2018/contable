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
$id_vigencia = $_SESSION['id_vigencia'];
$id_nomina = $_POST['id'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
// consulto el nombre de la empresa de la tabla tb_datos_ips
try {
    $sql = "SELECT `razon_social_ips`, `nit_ips`, `dv` FROM `tb_datos_ips`";
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
$administrativo = isset($valores['administrativo']) ? $valores['administrativo'] : ['sena' => 0, 'icbf' => 0, 'comfam' => 0, 'eps' => 0, 'arl' => 0, 'afp' => 0,];
$operativo = isset($valores['operativo']) ? $valores['operativo'] : ['sena' => 0, 'icbf' => 0, 'comfam' => 0, 'eps' => 0, 'arl' => 0, 'afp' => 0,];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_tipo_rubro`.`id_rubro`
                , `nom_rel_rubro`.`id_tipo`
                , `nom_tipo_rubro`.`nombre`
                , `nom_rel_rubro`.`r_admin`
                , `nom_rel_rubro`.`r_operativo`
                , `nom_rel_rubro`.`id_vigencia`
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
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_nomina` FROM `nom_cdp_empleados` WHERE (`id_nomina` = $id_nomina AND `tipo` = 'PL')";
    $rs = $cmd->query($sql);
    $val_cdp = $rs->fetch(PDO::FETCH_ASSOC);
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `nom_cdp_empleados` WHERE (`id_nomina` = $id_nomina AND `tipo` = 'PL')";
    $sql = $cmd->prepare($sql);
    $sql->execute();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (true) {
    try {
        $carcater = 'PL';
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $query = "INSERT INTO `nom_cdp_empleados` (`rubro`, `valor`, `id_nomina`, `tipo`) 
                VALUES (?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $rubro, PDO::PARAM_STR);
        $query->bindParam(2, $valorCdp, PDO::PARAM_STR);
        $query->bindParam(3, $id_nomina, PDO::PARAM_INT);
        $query->bindParam(4, $carcater, PDO::PARAM_STR);
        foreach ($rubros as $rb) {
            $tipo = $rb['id_tipo'];
            $valorCdp = 0;
            switch ($tipo) {
                case 11:
                    $valorCdp = $administrativo['comfam'] > 0 ? $administrativo['comfam'] : 0;
                    $rubro = $rb['r_admin'];
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
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `pto_cargue`.`cod_pptal`
                , `pto_cargue`.`nom_rubro`
                , `pto_cargue`.`tipo_dato`
            FROM
                `pto_cargue`
                INNER JOIN `pto_presupuestos` 
                    ON (`pto_cargue`.`id_pto` = `pto_presupuestos`.`id_pto`)
            WHERE (`pto_presupuestos`.`id_tipo` = 2 AND `pto_presupuestos`.`id_vigencia` = $id_vigencia)";
    $res = $cmd->query($sql);
    $codigos = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `nom_cdp_empleados`.`valor`
                , `pto_cargue`.`cod_pptal`
            FROM
                `pto_cargue`
                INNER JOIN `nom_cdp_empleados` 
                    ON (`pto_cargue`.`id_cargue` = `nom_cdp_empleados`.`rubro`)
            WHERE (`nom_cdp_empleados`.`id_nomina` = $id_nomina AND `nom_cdp_empleados`.`tipo` = 'PL')";
    $res = $cmd->query($sql);
    $rubros = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$data = [];
foreach ($codigos as $cd) {
    $raiz = $cd['cod_pptal'];
    foreach ($rubros as $rp) {
        $codigo = $rp['cod_pptal'];
        if (substr($codigo, 0, strlen($raiz)) === $raiz) {
            $data[$raiz]['valor'] = isset($data[$raiz]['valor']) ? $data[$raiz]['valor'] + $rp['valor'] : $rp['valor'];
            $data[$raiz]['nombre'] = $cd['nom_rubro'];
        }
    }
}
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
            WHERE (`nom_liq_dlab_auxt`.`id_nomina` = $id_nomina)";
    $rs = $cmd->query($sql);
    $sueldoBasico = $rs->fetchAll(PDO::FETCH_ASSOC);
    $sql = "SELECT COUNT(`id_empleado`) FROM `nom_liq_salario`  WHERE `id_nomina` = $id_nomina";
    $cantidad_empleados = $cmd->query($sql)->fetchColumn();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
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
                    size: auto;
                    margin: 2cm;
                }
            }
        </style>
    </head>
    <div class="p-4 text-left">
        <table class="page_break_avoid" style="width:100% !important;">
            <thead style="background-color: white !important;">
                <tr style="padding: bottom 3px; color:black">
                    <td colspan="8">
                        <table style="width:100% !important;">
                            <tr>
                                <td rowspan="3" class='text-center' style="width:18%"><label class="small"><img src="../../../images/logos/logo.png" width="100"></label></td>
                                <td colspan="7" style="text-align:center; font-size: 20px">
                                    <strong><?php echo $empresa['razon_social_ips']; ?> </strong>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    NIT <?php echo $empresa['nit_ips'] . '-' . $empresa['dv']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="7" style="text-align:center">
                                    <b>SOLICITUD DE CDP</b>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align: right; font-size: 14px">
                                    Estado: <?php echo $nomina['estado'] == 1 ? 'PARCIAL' : 'DEFINITIVA' ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php
                if ($nomina['tipo'] == 'N') {
                    $cual = 'MENSUAL';
                } else if ($nomina['tipo'] == 'PS') {
                    $cual = 'DE PRESTACIONES SOCIALES';
                }
                $nom_mes = isset($meses[$nomina['mes']]) ? 'MES DE ' . mb_strtoupper($meses[$nomina['mes']]) : '';
                ?>
                <tr style="color: black">
                    <th colspan="1">OBJETO: </th>
                    <th colspan="7" style="text-align: left;">PAGO NOMINA PATRONAL <?php echo $cual ?> N° <?php echo $nomina['id_nomina'] . ' ' . $nom_mes ?> VIGENCIA <?php echo  $nomina['vigencia'] ?>, <?php echo $cantidad_empleados ?> EMPLEADOS ADSCRITOS A <?php echo $empresa['razon_social_ips']; ?></th>
                </tr>
                <tr style="background-color: #CED3D3; text-align:center;">
                    <th colspan="1">Código</th>
                    <th colspan="5">Nombre</th>
                    <th colspan="2">Valor</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($data as $key => $dt) {
                    $rubro = $dt['nombre'];
                    $val = $dt['valor'];
                    echo "<tr>
                            <td colspan='1' class='text-left'>" . $key . "</td>
                            <td colspan='5' class='text-left'>" . $rubro . "</td>
                            <td colspan='2' style='text-align:right'>" . number_format($val, 2, ",", ".")  . "</td>
                        </tr>";
                }
                ?>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
                <tr>
                    <td colspan="8" style="padding: 15px;"></td>
                </tr>
            </tbody>
        </table>
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
    </div>

</div>