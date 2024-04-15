<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_mmto = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$id_serie = isset($_POST['id_serie']) ? $_POST['id_serie'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_ubica_traslado_centro_costo`.`id_serial`
                , `seg_ubica_traslado_centro_costo`.`id_traslado`
                , `seg_ubica_traslado_centro_costo`.`estado`
                , `seg_ubica_traslado_centro_costo`.`mmto_causas`
                , `seg_mantenimiento_acfijo`.`id_mmto`
                , `seg_mantenimiento_acfijo`.`tipo`
                , `tb_departamentos`.`nom_departamento`
                , `tb_municipios`.`nom_municipio`
                , `tb_sedes`.`nom_sede` AS `sede`
                , `tb_sedes`.`direcccion`
                , `seg_num_serial`.`tipo_entra`
                , `seg_num_serial`.`recomendaciones`
                , `tb_centros_costo` .`descripcion` AS `centro_costo`
            FROM
                `seg_ubica_traslado_centro_costo`
                INNER JOIN `seg_num_serial` 
                    ON (`seg_ubica_traslado_centro_costo`.`id_serial` = `seg_num_serial`.`id_serial`)
                INNER JOIN `tb_centro_costo_x_sede` 
                    ON (`seg_ubica_traslado_centro_costo`.`id_centro_costo` = `tb_centro_costo_x_sede`.`id_x_sede`)
                INNER JOIN `nom_estado_acfijo` 
                    ON (`seg_ubica_traslado_centro_costo`.`estado` = `nom_estado_acfijo`.`id_estado`)
                INNER JOIN `tb_centros_costo` 
                    ON (`tb_centro_costo_x_sede`.`id_centro_c` = `tb_centros_costo`.`id_centro`)
                INNER JOIN `tb_sedes` 
                    ON (`tb_centro_costo_x_sede`.`id_sede` = `tb_sedes`.`id_sede`)
                INNER JOIN `tb_departamentos` 
                    ON (`tb_sedes`.`id_dpto` = `tb_departamentos`.`id_departamento`)
                INNER JOIN `tb_municipios` 
                    ON (`tb_municipios`.`id_departamento` = `tb_departamentos`.`id_departamento`) AND (`tb_sedes`.`id_municipio` = `tb_municipios`.`id_municipio`)
                INNER JOIN `seg_mantenimiento_acfijo` 
                    ON (`seg_mantenimiento_acfijo`.`id_serial` = `seg_num_serial`.`id_serial`)
            WHERE `seg_ubica_traslado_centro_costo`.`id_traslado` = (SELECT MAX(`id_traslado`) FROM `seg_ubica_traslado_centro_costo` WHERE `id_serial` = '$id_serie') AND `seg_mantenimiento_acfijo`.`id_mmto`='$id_mmto'";
    $rs = $cmd->query($sql);
    $obj_hv = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_num_serial`.`id_serial`
                , `seg_num_serial`.`num_serial`
                , `ctt_bien_servicio`.`bien_servicio`
                , `seg_entra_detalle_activos_fijos`.`marca`
                , `seg_entra_detalle_activos_fijos`.`modelo`
            FROM
                `seg_num_serial`
                INNER JOIN `seg_entra_detalle_activos_fijos` 
                    ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
            WHERE `seg_num_serial`.`num_serial` IN(SELECT `num_serial` FROM `seg_num_serial` WHERE `id_ser_componente` = '$id_serie')";
    $rs = $cmd->query($sql);
    $componentes = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_hv`, `id_serie`, `lote`, `anio_fab`, `reg_invima`, `fabricante`, `origen`, `representante`, `direccion`, `telefono`, `url_img`, `nomb_img`, `estado`
            FROM
                `seg_hv_equipo`
            WHERE `id_serie` = '$id_serie'";
    $rs = $cmd->query($sql);
    $hvgral = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_compra`, `fec_ini` FROM `ctt_contratos` 
            WHERE `id_compra` = (SELECT 
            SUBSTRING_INDEX(
            SUBSTRING_INDEX(
            (SELECT
                `acf_entrada`.`identificador`
            FROM
                `seg_num_serial`
                INNER JOIN `seg_entra_detalle_activos_fijos` 
                    ON (`seg_num_serial`.`id_activo_fijo` = `seg_entra_detalle_activos_fijos`.`id_acfijo`)
                INNER JOIN `acf_entrada` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do` = `acf_entrada`.`id_entra_af`)
            WHERE `seg_num_serial`.`id_serial` = '$id_serie' LIMIT 1) ,'|',3),'|',-1) AS `id_adquisicion`)";
    $rs = $cmd->query($sql);
    $fecadq = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_fmto`, `id_serie`, `v_max`, `v_min`, `hz_min`, `hz_max`, `w_min`, `w_max`, `ma_min`, `ma_max`, `gc_min`, `gc_max`
            FROM
                `seg_reg_tecnico_fmto`
            WHERE `id_serie` = '$id_serie'";
    $rs = $cmd->query($sql);
    $rangos = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_apoyo`, `riesgo`, `uso`, `diagnostico`, `prevencion`, `rehabilitacion`, `analis_lab`, `tratamiento`, `id_user_reg`
            FROM
                `seg_hv_apoyo_tecnico`
            WHERE  `id_serie` = '$id_serie'";
    $rs = $cmd->query($sql);
    $apoyos = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_hv_manuales`.`id_manual`, `seg_hv_manuales`.`id_tipo`, `seg_hv_manuales`.`ruta`, `seg_hv_manuales`.`nombre`, `seg_hv_manuales`.`estado`, `seg_tipo_manual`.`descripcion`
            FROM
                `seg_hv_manuales`
                INNER JOIN `seg_tipo_manual` 
                    ON (`seg_hv_manuales`.`id_tipo` = `seg_tipo_manual`.`id_manual`)
            WHERE `seg_hv_manuales`.`id_serie` = '$id_serie'";
    $rs = $cmd->query($sql);
    $manuales = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_funca`, `estado`, `anios_fuera_servicio`, `causas`
            FROM
                `seg_funcionamiento_acfijo`
            WHERE `id_funca` = (SELECT MAX(`id_funca`) FROM `seg_funcionamiento_acfijo` WHERE `id_serie` = '$id_serie') LIMIT 1";
    $rs = $cmd->query($sql);
    $funcionamiento = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_registro`, `id_serie`, `fecha`, `tipo_mmto`, `descripcion`, `no_reporte`, `tercero_resp`, `observaciones`
            FROM
                `seg_registro_mantenimiento`
            WHERE `id_serie` = '$id_serie' AND `id_mmto` <= '$id_mmto' ORDER BY `fecha` ASC";
    $rs = $cmd->query($sql);
    $lismmtos = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$id_ter = '0';
foreach ($lismmtos as $l) {
    $id_ter .= ',' . $l['tercero_resp'];
}
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $id_ter;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res_api = curl_exec($ch);
curl_close($ch);
$dat_ter = json_decode($res_api, true);
$filas = count($componentes);
$tipoIN = $_POST['tip_in'];
$do = $pr = $ot = null;
if ($tipoIN == 'DO') {
    $do = 'checked';
} else if ($tipoIN == 'PR') {
    $pr = 'checked';
} else {
    $ot = 'checked';
}
$ruta_del = $hvgral['url_img'] == '' ? '' : $hvgral['url_img'] . $hvgral['nomb_img'];
$ruta_img = $ruta_del != '' ? $_SESSION['urlin'] . substr($ruta_del, 5) : '';
$v_max_ref = 300;
$hz_max_ref = 50000;
$w_max_ref = 1000;
$ma_max_ref = 10000;
$gc_max_ref = 1000;
if ($rangos['id_fmto'] == '') {
    $id_rango = 0;
    $v_min = $hz_min = $w_min = $ma_min = $gc_min = 0;
    $v_max = $v_max_ref;
    $hz_max = $hz_max_ref;
    $w_max = $w_max_ref;
    $ma_max = $ma_max_ref;
    $gc_max = $gc_max_ref;
} else {
    $id_rango = $rangos['id_fmto'];
    $v_min = $rangos['v_min'];
    $v_max = $rangos['v_max'];
    $hz_min = $rangos['hz_min'];
    $hz_max = $rangos['hz_max'];
    $w_min = $rangos['w_min'];
    $w_max = $rangos['w_max'];
    $ma_min = $rangos['ma_min'];
    $ma_max = $rangos['ma_max'];
    $gc_min = $rangos['gc_min'];
    $gc_max = $rangos['gc_max'];
}
$r1 = $r2 = $r3 = $u1 = $u2 = $u3 = null;
if ($apoyos['id_apoyo'] == '') {
    $id_apoyo = 0;
    $riesgo = $uso = $diagnostico = $prevencion = $rehabilitacion = $analis_lab = $tratamiento = $id_user_reg = '';
} else {
    $id_apoyo = $apoyos['id_apoyo'];
    $riesgo = $apoyos['riesgo'];
    $uso = $apoyos['uso'];
    $diagnostico = $apoyos['diagnostico'];
    $prevencion = $apoyos['prevencion'];
    $rehabilitacion = $apoyos['rehabilitacion'];
    $analis_lab = $apoyos['analis_lab'];
    $tratamiento = $apoyos['tratamiento'];
    $id_user_reg = $apoyos['id_user_reg'];
    if ($riesgo == 1) {
        $r1 = 'checked';
    } else if ($riesgo == 2) {
        $r2 = 'checked';
    } else {
        $r3 = 'checked';
    }
    if ($uso == 1) {
        $u1 = 'checked';
    } else if ($uso == 2) {
        $u2 = 'checked';
    } else {
        $u3 = 'checked';
    }
}
$i1 = $i2 = $i3 = $p1 = $p2 = $p3 = $mst1 = $mst2 = $mst3 = $mu1 = $mu2 = $mu3 = $mf1 = $mf2 = $mf3 = null;
$ruta_i = $ruta_p = $ruta_mst = $ruta_mu = $ruta_mf = null;
$id_i = $id_p = $id_mst = $id_mu = $id_mf = 0;
if (!empty($manuales)) {
    foreach ($manuales as $m) {
        if ($m['id_tipo'] == 1) {
            $estado = $m['estado'];
            if ($estado == 1) {
                $i1 = 'checked';
            } else if ($estado == 2) {
                $i2 = 'checked';
            } else {
                $i3 = 'checked';
            }
            $ruta_i = $m['ruta'] . $m['nombre'];
            $id_i = $m['id_manual'];
        }
        if ($m['id_tipo'] == 2) {
            $estado = $m['estado'];
            if ($estado == 1) {
                $p1 = 'checked';
            } else if ($estado == 2) {
                $p2 = 'checked';
            } else {
                $p3 = 'checked';
            }
            $ruta_p = $m['ruta'] . $m['nombre'];
            $id_p = $m['id_manual'];
        }
        if ($m['id_tipo'] == 3) {
            $estado = $m['estado'];
            if ($estado == 1) {
                $mst1 = 'checked';
            } else if ($estado == 2) {
                $mst2 = 'checked';
            } else {
                $mst3 = 'checked';
            }
            $ruta_mst = $m['ruta'] . $m['nombre'];
            $id_mst = $m['id_manual'];
        }
        if ($m['id_tipo'] == 4) {
            $estado = $m['estado'];
            if ($estado == 1) {
                $mu1 = 'checked';
            } else if ($estado == 2) {
                $mu2 = 'checked';
            } else {
                $mu3 = 'checked';
            }
            $ruta_mu = $m['ruta'] . $m['nombre'];
            $id_mu = $m['id_manual'];
        }
        if ($m['id_tipo'] == 5) {
            $estado = $m['estado'];
            if ($estado == 1) {
                $mf1 = 'checked';
            } else if ($estado == 2) {
                $mf2 = 'checked';
            } else {
                $mf3 = 'checked';
            }
            $ruta_mf = $m['ruta'] . $m['nombre'];
            $id_mf = $m['id_manual'];
        }
    }
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-2" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR HOJA DE VIDA DE ACTIVO FIJO</h5>
        </div>
        <?php
        if (!empty($obj_hv)) {
            $cmd = null;
        ?>
            <div class="px-2 pt-0" style="overflow-y: scroll;height: 70vh; width: 100%;">
                <div id="accordion" style="height: auto;">
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="moduno">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra" data-toggle="collapse" data-target="#collapsemoduno" aria-expanded="true" aria-controls="collapsemoduno">
                                    <div class="form-row">
                                        <div>
                                            1. IDENTIFICACIÓN
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemoduno" class="collapse" aria-labelledby="moduno">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                        <tr>
                                            <td class="w-15" style="background-color: #e9ecef;">DEPARTAMENTO</td>
                                            <td><?php echo $obj_hv['nom_departamento'] ?></td>
                                            <td class="w-15" style="background-color: #e9ecef;">MUNICIPIO</td>
                                            <td><?php echo $obj_hv['nom_municipio'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="w-15" style="background-color: #e9ecef;">SEDE</td>
                                            <td><?php echo $obj_hv['sede'] ?></td>
                                            <td class="w-15" style="background-color: #e9ecef;">DIRECCIÓN</td>
                                            <td><?php echo $obj_hv['direcccion'] ?></td>
                                        </tr>
                                        <tr>
                                            <td class="w-15" style="background-color: #e9ecef;">SERVICIO</td>
                                            <td colspan="3">Descripción del servicio</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modDos">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodDos" aria-expanded="true" aria-controls="collapsemodDos">
                                    <div class="form-row">
                                        <div>
                                            2. DATOS GENERALES DEL EQUIPO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodDos" class="collapse" aria-labelledby="modDos">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <input type="hidden" id="valEstado" value="<?php echo $hvgral['id_hv'] == '' ? 0 : 1 ?>">
                                    <input type="hidden" id="id_mmto" value="<?php echo $id_mmto ?>">
                                    <input type="hidden" id="ruta_del" value="<?php echo $ruta_del ?>">
                                    <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                        <form id="formHVEquipoGral">
                                            <tr>
                                                <td class="w-35 p-0_5"><input type="file" class="form-control-file form-contro-sm altura border-0 rounded-0" name="upImageAF" id="upImageAF"></td>
                                                <td class="w-25 div-gris">Nombre del equipo</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $_POST['bien_servicio'] ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-35 text-center" rowspan="13"><img src="<?php echo $ruta_img ?>" alt="equipo"></td>
                                                <td class="div-gris">Marca</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $_POST['marca'] ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Modelo</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $_POST['modelo'] ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">N°. de Inventario</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $_POST['id_serial'] ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Serie</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $_POST['num_serial'] ?></div>
                                                </td>
                                                <input type="hidden" id="id_serial_hv" name="id_serial_hv" value="<?php echo $_POST['id_serial'] ?>">
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Lote</td>
                                                <td class="p-0_5"><input type="text" id="txtLoteAF" name="txtLoteAF" class="form-control form-control-sm altura border-0 rounded-0 " value="<?php echo $hvgral['lote'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Año de fabricación</td>
                                                <td class="p-0_5"><input type="date" id="fecFabricacion" name="fecFabricacion" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['anio_fab'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Registro INVIMA</td>
                                                <td class="px-0_5"><input type="text" id="txtRegINVIMA" name="txtRegINVIMA" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['reg_invima'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Ubicación</td>
                                                <td class="px-2">
                                                    <div class="px-0_5"><?php echo $obj_hv['sede'] . ' ' . $obj_hv['centro_costo'] . ', ' . $obj_hv['direcccion'] . ' ' . $obj_hv['nom_municipio'] . ' (' . $obj_hv['nom_departamento'] . ')' ?></div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Fabricante</td>
                                                <td class="p-0_5"><input type="text" id="txtFabricante" name="txtFabricante" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['fabricante'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Lugar de origen</td>
                                                <td class="p-0_5"><input type="text" id="txtOrigen" name="txtOrigen" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['origen'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Representante en Colombia</td>
                                                <td class="p-0_5"><input type="text" id="txtRepre" name="txtRepre" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['representante'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Dirección</td>
                                                <td class="p-0_5"><input type="text" id="txtDirRepre" name="txtDirRepre" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['direccion'] ?>"></td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris">Teléfono</td>
                                                <td class="p-0_5"><input type="text" id="txtTelRepre" name="txtTelRepre" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $hvgral['telefono'] ?>"></td>
                                            </tr>
                                        </form>
                                    </table>
                                </div>
                                <div class="text-center pt-3 mb-0">
                                    <button type="button" class="btn btn-info btn-sm" id="btnUpHVEquipoGral">Actualizar Datos Generales</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modTres">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodTres" aria-expanded="true" aria-controls="collapsemodTres">
                                    <div class="form-row">
                                        <div>
                                            3. FORMA DE ADQUISICIÓN
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodTres" class="collapse" aria-labelledby="modTres">
                            <div class="card-body nopadding">
                                <div class="px-1 overflow">
                                    <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                        <tr class="p-0">
                                            <?php for ($i = 0; $i < 20; $i++) { ?><td class="w-5 border-0 p-0"></td><?php } ?>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="div-gris">Propiedad del Equipo</td>
                                            <td colspan="16" class="p-0_5">
                                                <input type="text" class="form-control form-control-sm altura border-0 rounded-0">
                                            </td>
                                        </tr>
                                        <tr class="div-gris">
                                            <td colspan="8">Forma de Adquisición</td>
                                            <td colspan="2" class="text-center">Compra</td>
                                            <td colspan="2" class="text-center"><input type="radio" name="numFormAdq" value="1" <?php echo $pr ?> class="centro-vertical"></td>
                                            <td colspan="2" class="text-center">Donación</td>
                                            <td colspan="2" class="text-center"><input type="radio" name="numFormAdq" value="2" <?php echo $do ?> class="centro-vertical"></td>
                                            <td colspan="2" class="text-center">Comodato</td>
                                            <td colspan="2" class="text-center"><input type="radio" name="numFormAdq" value="3" <?php echo $ot ?> class="centro-vertical"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="div-gris px-2">Fecha de adquisición</td>
                                            <td colspan="6" class="px-2">
                                                <div class="px-0_5"><?php echo $fecadq['fec_ini'] ?></div>
                                            </td>
                                            <td colspan="4" class="div-gris">Valor de la compra</td>
                                            <td colspan="6" class="px-2">
                                                <div class="px-0_5"><?php echo pesos($_POST['val_unit']) ?></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="div-gris">Fecha de instalación</td>
                                            <td colspan="6" class="p-0_5"><input type="date" class="form-control form-control-sm altura border-0 rounded-0"></td>
                                            <td colspan="4" class="div-gris">Fabricante</td>
                                            <td colspan="6" class="px-2">
                                                <div class="px-0_5"><?php echo $hvgral['fabricante'] ?></div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="4" class="div-gris">Periodo de garantía</td>
                                            <td colspan="6" class="p-0_5"><input type="text" class="form-control form-control-sm altura border-0 rounded-0"></td>
                                            <td colspan="4" class="div-gris">Vida útil</td>
                                            <td colspan="6" class="p-0_5"><input type="text" class="form-control form-control-sm altura border-0 rounded-0"></td>
                                        </tr>
                                        <tr class="div-gris text-center">
                                            <td colspan="8" class="div-gris text-left">Clasificación según el Riesgo (Decreto 4725 de 2005)</td>
                                            <td colspan="2">I</td>
                                            <td colspan="1"><input type="radio" name="clasRiesgo" value="1" class="centro-vertical"></td>
                                            <td colspan="2">IIA</td>
                                            <td colspan="1"><input type="radio" name="clasRiesgo" value="2" class="centro-vertical"></td>
                                            <td colspan="2">IIB</td>
                                            <td colspan="1"><input type="radio" name="clasRiesgo" value="3" class="centro-vertical"></td>
                                            <td colspan="2">III</td>
                                            <td colspan="1"><input type="radio" name="clasRiesgo" value="4" class="centro-vertical"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="8" class="div-gris">Calibración (Tipo y periodicidad)</td>
                                            <td colspan="12" class="p-0_5"><input type="text" class="form-control form-control-sm altura border-0 rounded-0"></td>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modCuatro">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapesemodCuatro" aria-expanded="true" aria-controls="collapesemodCuatro">
                                    <div class="form-row">
                                        <div>
                                            4. REGISTRO TECNICO DE FUNCIONAMIENTO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapesemodCuatro" class="collapse" aria-labelledby="modCuatro">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formRangosVFPCT">
                                        <input type="hidden" name="id_hv_reg_tec" value="<?php echo $id_rango ?>">
                                        <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr>
                                                <td class="div-gris w-20">Rango de Voltaje</td>
                                                <td class="w-25 px-1 py-0">
                                                    <div class="form-row p-y0">
                                                        <div class="col-md-2 text-center p-0_5">
                                                            <span id="vMin" class="w-100 badge badge-info"><?php echo $v_min ?></span>
                                                            <input type="hidden" name="vMin" id="vMinInput" value="<?php echo $v_min ?>">
                                                        </div>
                                                        <div class="col-md-8 p-0_5 px-0 text-center">
                                                            <div slider id="slider-distance" class="pt-2">
                                                                <div>
                                                                    <div inverse-left style="width:70%;"></div>
                                                                    <div inverse-right style="width:70%;"></div>
                                                                    <div range style="left:<?php echo $v_min * 100 / $v_max_ref ?>%;right:<?php echo 100 - $v_max * 100 / $v_max_ref ?>%;"></div>
                                                                    <span thumb style="left:<?php echo $v_min * 100 / $v_max_ref ?>%;"></span>
                                                                    <span thumb style="left:<?php echo $v_max * 100 / $v_max_ref ?>%;"></span>
                                                                </div>
                                                                <input type="range" tabindex="0" value="<?php echo $v_min ?>" max="<?php echo $v_max_ref ?>" min="0" step="1" class="valueMin" id="v" />
                                                                <input type="range" tabindex="0" value="<?php echo $v_max ?>" max="<?php echo $v_max_ref ?>" min="0" step="1" class="valueMax" id="v" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center p-0_5 w-100">
                                                            <span id="vMax" class="w-100 badge badge-primary"><?php echo $v_max ?></span>
                                                            <input type="hidden" name="vMax" id="vMaxInput" value="<?php echo $v_max ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="div-gris w-5 text-center">V</td>
                                                <td class="div-gris w-20">Rango de Frecuencia</td>
                                                <td class="w-25 px-1 py-0">
                                                    <div class="form-row p-y0">
                                                        <div class="col-md-2 text-center p-0_5">
                                                            <span id="hzMin" class="w-100 badge badge-info"><?php echo $hz_min ?></span>
                                                            <input type="hidden" name="hzMin" id="hzMinInput" value="<?php echo $hz_min ?>">
                                                        </div>
                                                        <div class="col-md-8 p-0_5 px-0 text-center">
                                                            <div slider id="slider-distance" class="pt-2">
                                                                <div>
                                                                    <div inverse-left style="width:70%;"></div>
                                                                    <div inverse-right style="width:70%;"></div>
                                                                    <div range style="left:<?php echo $hz_min * 100 / $hz_max_ref ?>%;right:<?php echo 100 - $hz_max * 100 / $hz_max_ref ?>%;"></div>
                                                                    <span thumb style="left:<?php echo $hz_min * 100 / $hz_max_ref ?>%;"></span>
                                                                    <span thumb style="left:<?php echo $hz_max * 100 / $hz_max_ref ?>%;"></span>
                                                                </div>
                                                                <input type="range" tabindex="0" value="<?php echo $hz_min ?>" max="<?php echo $hz_max_ref ?>" min="0" step="1" class="valueMin" id="hz" />
                                                                <input type="range" tabindex="0" value="<?php echo $hz_max ?>" max="<?php echo $hz_max_ref ?>" min="0" step="1" class="valueMax" id="hz" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center p-0_5 w-100">
                                                            <span id="hzMax" class="w-100 badge badge-primary"><?php echo $hz_max ?></span>
                                                            <input type="hidden" name="hzMax" id="hzMaxInput" value="<?php echo $hz_max ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="div-gris w-5 text-center">MHz</td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris w-20">Rango de Potencia</td>
                                                <td class="w-25 px-1 py-0">
                                                    <div class="form-row p-y0">
                                                        <div class="col-md-2 text-center p-0_5">
                                                            <span id="wMin" class="w-100 badge badge-info"><?php echo $w_min ?></span>
                                                            <input type="hidden" name="wMin" id="wMinInput" value="<?php echo $w_min ?>">
                                                        </div>
                                                        <div class="col-md-8 p-0_5 px-0 text-center">
                                                            <div slider id="slider-distance" class="pt-2">
                                                                <div>
                                                                    <div inverse-left style="width:70%;"></div>
                                                                    <div inverse-right style="width:70%;"></div>
                                                                    <div range style="left:<?php echo $w_min * 100 / $w_max_ref ?>%;right:<?php echo 100 - $w_max * 100 / $w_max_ref ?>%;"></div>
                                                                    <span thumb style="left:<?php echo $w_min * 100 / $w_max_ref ?>%;"></span>
                                                                    <span thumb style="left:<?php echo $w_max * 100 / $w_max_ref ?>%;"></span>
                                                                </div>
                                                                <input type="range" tabindex="0" value="<?php echo $w_min ?>" max="<?php echo $w_max_ref ?>" min="0" step="1" class="valueMin" id="w" />
                                                                <input type="range" tabindex="0" value="<?php echo $w_max ?>" max="<?php echo $w_max_ref ?>" min="0" step="1" class="valueMax" id="w" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center p-0_5 w-100">
                                                            <span id="wMax" class="w-100 badge badge-primary"><?php echo $w_max ?></span>
                                                            <input type="hidden" name="wMax" id="wMaxInput" value="<?php echo $w_max ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="div-gris w-5 text-center">W</td>
                                                <td class="div-gris w-20">Rango de Corriente</td>
                                                <td class="w-25 px-1 py-0">
                                                    <div class="form-row p-y0">
                                                        <div class="col-md-2 text-center p-0_5">
                                                            <span id="mAMin" class="w-100 badge badge-info"><?php echo $ma_min ?></span>
                                                            <input type="hidden" name="mAMin" id="mAMinInput" value="<?php echo $ma_min ?>">
                                                        </div>
                                                        <div class="col-md-8 p-0_5 px-0 text-center">
                                                            <div slider id="slider-distance" class="pt-2">
                                                                <div>
                                                                    <div inverse-left style="width:70%;"></div>
                                                                    <div inverse-right style="width:70%;"></div>
                                                                    <div range style="left:<?php echo $ma_min * 100 / $ma_max_ref ?>%;right:<?php echo 100 - $ma_max * 100 / $ma_max_ref ?>%;"></div>
                                                                    <span thumb style="left:<?php echo $ma_min * 100 / $ma_max_ref ?>%;"></span>
                                                                    <span thumb style="left:<?php echo $ma_max * 100 / $ma_max_ref ?>%;"></span>
                                                                </div>
                                                                <input type="range" tabindex="0" value="<?php echo $ma_min ?>" max="<?php echo $ma_max_ref ?>" min="0" step="1" class="valueMin" id="mA" />
                                                                <input type="range" tabindex="0" value="<?php echo $ma_max ?>" max="<?php echo $ma_max_ref ?>" min="0" step="1" class="valueMax" id="mA" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center p-0_5 w-100">
                                                            <span id="mAMax" class="w-100 badge badge-primary"><?php echo $ma_max ?></span>
                                                            <input type="hidden" name="mAMax" id="mAMaxInput" value="<?php echo $ma_max ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="div-gris w-5 text-center">mA</td>
                                            </tr>
                                            <tr>
                                                <td class="div-gris w-20">Rango de Temperatura</td>
                                                <td class="w-25 px-1 py-0">
                                                    <div class="form-row p-y0">
                                                        <div class="col-md-2 text-center p-0_5">
                                                            <span id="gCMin" class="w-100 badge badge-info"><?php echo $gc_min ?></span>
                                                            <input type="hidden" name="gCMin" id="gCMinInput" value="<?php echo $gc_min ?>">
                                                        </div>
                                                        <div class="col-md-8 p-0_5 px-0 text-center">
                                                            <div slider id="slider-distance" class="pt-2">
                                                                <div>
                                                                    <div inverse-left style="width:70%;"></div>
                                                                    <div inverse-right style="width:70%;"></div>
                                                                    <div range style="left:<?php echo $gc_min * 100 / $gc_max_ref ?>%;right:<?php echo 100 - $gc_max * 100 / $gc_max_ref ?>%;"></div>
                                                                    <span thumb style="left:<?php echo $gc_min * 100 / $gc_max_ref ?>%;"></span>
                                                                    <span thumb style="left:<?php echo $gc_max * 100 / $gc_max_ref ?>%;"></span>
                                                                </div>
                                                                <input type="range" tabindex="0" value="<?php echo $gc_min ?>" max="<?php echo $gc_max_ref ?>" min="0" step="1" class="valueMin" id="gC" />
                                                                <input type="range" tabindex="0" value="<?php echo $gc_max ?>" max="<?php echo $gc_max_ref ?>" min="0" step="1" class="valueMax" id="gC" />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2 text-center p-0_5 w-100">
                                                            <span id="gCMax" class="w-100 badge badge-primary"><?php echo $gc_max ?></span>
                                                            <input type="hidden" name="gCMax" id="gCMaxInput" value="<?php echo $gc_max ?>">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div class="text-center pt-3 mb-0">
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpRegTecFmto">Actualizar Funcionamiento</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modCinco">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapesemodCinco" aria-expanded="true" aria-controls="collapesemodCinco">
                                    <div class="form-row">
                                        <div>
                                            5. INFORMACION TECNICA DEL EQUIPO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapesemodCinco" class="collapse" aria-labelledby="modCinco">
                            <div class="card-body">
                                <div class="px-1 overflow">
                                    <table class="w-100 table-bordered table-sm" style="color:#495057;font-size:85%; white-space: nowrap;">
                                        <tr style="background-color: #e9ecefcc;">
                                            <td rowspan="<?php echo $filas + 1 ?>" class="text-left div-gris">Componentes y accesorios</td>
                                            <td>Serial</td>
                                            <td>Nombre</td>
                                            <td>Marca</td>
                                            <td>Modelo</td>
                                        </tr>
                                        <?php
                                        foreach ($componentes as $c) {
                                            echo '<tr>';
                                            echo '<td class="text-left">' . $c['num_serial'] . '</td>';
                                            echo '<td class="text-left">' . $c['bien_servicio'] . '</td>';
                                            echo '<td>' . $c['marca'] . '</td>';
                                            echo '<td>' . $c['modelo'] . '</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                        <tr style="background-color: #e9ecefcc;">
                                            <td rowspan="<?php echo $filas + 1 ?>" class="text-left" style="background-color: #e9ecef;">Insumos</td>
                                            <td>Serial</td>
                                            <td>Nombre</td>
                                            <td>Marca</td>
                                            <td>Modelo</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modSeis">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodSeis" aria-expanded="true" aria-controls="collapsemodSeis">
                                    <div class="form-row">
                                        <div>
                                            6. REGISTRO DE APOYO TECNICO DEL EQUIPO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodSeis" class="collapse" aria-labelledby="modSeis">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formApoyoTecnico">
                                        <input type="hidden" name="id_apoyo" value="<?php echo $id_apoyo ?>">
                                        <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr class="p-0">
                                                <?php for ($i = 0; $i < 20; $i++) { ?><td class="w-5 border-0 p-0"></td><?php } ?>
                                            </tr>
                                            <tr class="div-gris text-center">
                                                <td colspan="5" class="text-left">Riesgo</td>
                                                <td colspan="3">Alto</td>
                                                <td colspan="2"><input type="radio" name="riesgoApoyoTec" value="1" class="centro-vertical" <?php echo $r1 ?>></td>
                                                <td colspan="3">Medio</td>
                                                <td colspan="2"><input type="radio" name="riesgoApoyoTec" value="2" class="centro-vertical" <?php echo $r2 ?>></td>
                                                <td colspan="3">Bajo</td>
                                                <td colspan="2"><input type="radio" name="riesgoApoyoTec" value="3" class="centro-vertical" <?php echo $r3 ?>></td>
                                            </tr>
                                            <tr class="div-gris text-center">
                                                <td colspan="5" class="text-left">Uso</td>
                                                <td colspan="3">Médico</td>
                                                <td colspan="2"><input type="radio" name="usoApoyoTec" value="1" class="centro-vertical" <?php echo $u1 ?>></td>
                                                <td colspan="3">Básico</td>
                                                <td colspan="2"><input type="radio" name="usoApoyoTec" value="2" class="centro-vertical" <?php echo $u2 ?>></td>
                                                <td colspan="3">Apoyo</td>
                                                <td colspan="2"><input type="radio" name="usoApoyoTec" value="3" class="centro-vertical" <?php echo $u3 ?>></td>
                                            </tr>
                                            <tr>
                                                <td colspan="6" rowspan="5" class="div-gris">Clasificación biomédica</td>
                                                <td colspan="5" class="text-left div-gris">Diagnostico</td>
                                                <td colspan="9" class="p-0_5"><input type="text" name="txtDiag" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $diagnostico ?>"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-left div-gris">Prevención</td>
                                                <td colspan="9" class="p-0_5"><input type="text" name="txtPrev" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $prevencion ?>"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-left div-gris">Rehabilitación</td>
                                                <td colspan="9" class="p-0_5"><input type="text" name="txtRehab" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $rehabilitacion ?>"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-left div-gris">Análisis de laboratorio</td>
                                                <td colspan="9" class="p-0_5"><input type="text" name="txtLab" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $analis_lab ?>"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="5" class="text-left div-gris">Tratamiento y mantenimiento de la vida</td>
                                                <td colspan="9" class="p-0_5"><input type="text" name="txttmnto" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $tratamiento ?>"></td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div class="text-center pt-3 mb-0">
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpApoyoTecnico">Actualizar Apoyo Técnico</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modSiete">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodSiete" aria-expanded="true" aria-controls="collapsemodSiete">
                                    <div class="form-row">
                                        <div>
                                            7. PLANOS
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodSiete" class="collapse" aria-labelledby="modSiete">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formHVPlanos">
                                        <input type="hidden" id="id_instal" name="id_instal" value="<?php echo $id_i ?>">
                                        <input type="hidden" id="id_partes" name="id_partes" value="<?php echo $id_p ?>">
                                        <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr class="div-gris text-center">
                                                <td class="w-25">PLANO</td>
                                                <td class="w-10">SI</td>
                                                <td class="w-10">NO</td>
                                                <td class="w-10">N/A</td>
                                                <td class="w-45">UBICACIÓN</td>
                                            </tr>
                                            <tr>
                                                <td class="w-25 div-gris">Instalación</td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="instalPlano" value="1" class="centro-vertical" <?php echo $i1 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="instalPlano" value="2" class="centro-vertical" <?php echo $i2 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="instalPlano" value="3" class="centro-vertical" <?php echo $i3 ?>></td>
                                                <td class="w-45 p-0_5">
                                                    <div class="form-row">
                                                        <div class="col-md-10">
                                                            <input type="file" class="form-control-file form-control-sm" id="fileInstal" name="fileInstal">
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <button class="btn btn-sm btn-<?php echo $ruta_i == '' ? 'secondary' : 'warning' ?> btn-block descargaManual" <?php echo $ruta_i == '' ? 'disabled' : '' ?> title="Descargar Plano de Instalación" value="<?php echo base64_encode($ruta_i) ?>"><span class="fas fa-download fa-lg"></span></button>
                                                            <?php echo $ruta_i != '' ? '<input type="hidden" id="rai" value ="' . base64_encode($ruta_i) . '">' : '' ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-25 div-gris">Partes</td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="partePlano" value="1" class="centro-vertical" <?php echo $p1 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="partePlano" value="2" class="centro-vertical" <?php echo $p2 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="partePlano" value="3" class="centro-vertical" <?php echo $p3 ?>></td>
                                                <td class="w-45 p-0_5">
                                                    <div class="form-row">
                                                        <div class="col-md-10">
                                                            <input type="file" class="form-control-file form-control-sm" id="fileParts" name="fileParts">
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <button class="btn btn-sm btn-<?php echo $ruta_p == '' ? 'secondary' : 'warning' ?> btn-block descargaManual" <?php echo $ruta_p == '' ? 'disabled' : '' ?> title="Descargar Plano de Partes" value="<?php echo base64_encode($ruta_p) ?>"><span class="fas fa-download fa-lg"></span></button>
                                                            <?php echo $ruta_p != '' ? '<input type="hidden" id="rap" value ="' . base64_encode($ruta_p) . '">' : '' ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div class="text-center pt-3 mb-0">
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpHVPlanos">Actualizar Planos</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modOcho">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodOcho" aria-expanded="true" aria-controls="collapsemodOcho">
                                    <div class="form-row">
                                        <div>
                                            8. MANUALES
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodOcho" class="collapse" aria-labelledby="modOcho">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formHVManuales">
                                        <input type="hidden" id="id_mSerTec" name="id_mSerTec" value="<?php echo $id_mst ?>">
                                        <input type="hidden" id="id_mUser" name="id_mUser" value="<?php echo $id_mu ?>">
                                        <input type="hidden" id="id_mGFast" name="id_mGFast" value="<?php echo $id_mf ?>">
                                        <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr class="div-gris text-center">
                                                <td class="w-25">MANUAL</td>
                                                <td class="w-10">SI</td>
                                                <td class="w-10">NO</td>
                                                <td class="w-10">N/A</td>
                                                <td class="w-45">UBICACIÓN</td>
                                            </tr>
                                            <tr>
                                                <td class="w-25 div-gris">De Servicio Técnico</td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="servTecManual" value="1" class="centro-vertical" <?php echo $mst1 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="servTecManual" value="2" class="centro-vertical" <?php echo $mst2 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="servTecManual" value="3" class="centro-vertical" <?php echo $mst3 ?>></td>
                                                <td class="w-45 p-0_5">
                                                    <div class="form-row">
                                                        <div class="col-md-10">
                                                            <input type="file" class="form-control-file form-control-sm" id="filemst" name="filemst">
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <button class="btn btn-sm btn-<?php echo $ruta_mst == '' ? 'secondary' : 'warning' ?> btn-block descargaManual" <?php echo $ruta_mst == '' ? 'disabled' : '' ?> title="Descargar Plano de Instalación" value="<?php echo base64_encode($ruta_mst) ?>"><span class="fas fa-download fa-lg"></span></button>
                                                            <?php echo $ruta_mst != '' ? '<input type="hidden" id="ramst" value ="' . base64_encode($ruta_mst) . '">' : '' ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-25 div-gris">De Usuario</td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="userManual" value="1" class="centro-vertical" <?php echo $mu1 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="userManual" value="2" class="centro-vertical" <?php echo $mu2 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="userManual" value="3" class="centro-vertical" <?php echo $mu3 ?>></td>
                                                <td class="w-45 p-0_5">
                                                    <div class="form-row">
                                                        <div class="col-md-10">
                                                            <input type="file" class="form-control-file form-control-sm" id="filemu" name="filemu">
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <button class="btn btn-sm btn-<?php echo $ruta_mu == '' ? 'secondary' : 'warning' ?> btn-block descargaManual" <?php echo $ruta_mu == '' ? 'disabled' : '' ?> title="Descargar Plano de Partes" value="<?php echo base64_encode($ruta_mu) ?>"><span class="fas fa-download fa-lg"></span></button>
                                                            <?php echo $ruta_mu != '' ? '<input type="hidden" id="ramu" value ="' . base64_encode($ruta_mu) . '">' : '' ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="w-25 div-gris">Guía de manejo rápido</td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="guiaFastManual" value="1" class="centro-vertical" <?php echo $mf1 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="guiaFastManual" value="2" class="centro-vertical" <?php echo $mf2 ?>></td>
                                                <td class="w-10 div-gris text-center"><input type="radio" name="guiaFastManual" value="3" class="centro-vertical" <?php echo $mf3 ?>></td>
                                                <td class="w-45 p-0_5">
                                                    <div class="form-row">
                                                        <div class="col-md-10">
                                                            <input type="file" class="form-control-file form-control-sm" id="filemf" name="filemf">
                                                        </div>
                                                        <div class="col-md-2 text-center">
                                                            <button class="btn btn-sm btn-<?php echo $ruta_mf == '' ? 'secondary' : 'warning' ?> btn-block descargaManual" <?php echo $ruta_mf == '' ? 'disabled' : '' ?> title="Descargar Plano de Partes" value="<?php echo base64_encode($ruta_mf) ?>"><span class="fas fa-download fa-lg"></span></button>
                                                            <?php echo $ruta_mf != '' ? '<input type="hidden" id="ramf" value ="' . base64_encode($ruta_mf) . '">' : '' ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </form>
                                    <div class="text-center pt-3 mb-0">
                                        <button type="button" class="btn btn-info btn-sm" id="btnUpHVManuales">Actualizar Manuales</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modNueve">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodNueve" aria-expanded="true" aria-controls="collapsemodNueve">
                                    <div class="form-row">
                                        <div>
                                            9. RECOMENDACIONES DEL FABRICANTE Y CONDICIONES AMBIENTALES
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodNueve" class="collapse" aria-labelledby="modNueve">
                            <div class="card-body">
                                <div class="form-row px-1">
                                    <div class="col-md-12">
                                        <textarea class="form-control form-control-sm rounded-0" id="txtaReCons" rows="5"><?php echo $obj_hv['recomendaciones'] ?></textarea>
                                    </div>
                                </div>
                                <div class="text-center pt-3 mb-0">
                                    <button type="button" class="btn btn-info btn-sm" id="btnUpHVReCons">Actualizar Recomendaciones y condiciones</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modDiez">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodDiez" aria-expanded="true" aria-controls="collapsemodDiez">
                                    <div class="form-row">
                                        <div>
                                            10. ESTADO GENERAL DEL EQUIPO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodDiez" class="collapse" aria-labelledby="modDiez">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formEstadoGral">
                                        <input type="hidden" id="id_traslado" name="id_traslado" value="<?php echo $obj_hv['id_traslado'] != '' ? $obj_hv['id_traslado'] : 0 ?>">
                                        <table class="w-100 table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr class="div-gris text-center">
                                                <td class="w-25 border">ESTADO</td>
                                                <td class="w-15 border">Bueno</td>
                                                <td class="w-10 border"><input type="radio" name="estadoGral" value="1" class="centro-vertical" <?php echo $obj_hv['estado'] == 1 ? 'checked' : '' ?>></td>
                                                <td class="w-15 border">Regular</td>
                                                <td class="w-10 border"><input type="radio" name="estadoGral" value="2" class="centro-vertical" <?php echo $obj_hv['estado'] == 2 ? 'checked' : '' ?>></td>
                                                <td class="w-15 border">Malo</td>
                                                <td class="w-10 border"><input type="radio" name="estadoGral" value="3" class="centro-vertical" <?php echo $obj_hv['estado'] == 3 ? 'checked' : '' ?>></td>
                                            </tr>
                                            <tr class="border border-bottom-0">
                                                <td colspan="7"><label for="txtaEstadoGral" class="w-100 mb-0">Causas:</label></td>
                                            </tr>
                                            <tr class="border border-top-0">
                                                <td colspan="7" class="p-0_5"><textarea id="txtaEstadoGral" name="txtaEstadoGral" class="form-control form-control-sm border-0 rounded-0" rows="5"><?php echo $obj_hv['mmto_causas'] ?></textarea></td>
                                            </tr>
                                        </table>
                                        <div class="text-center pt-3 mb-0">
                                            <button type="button" class="btn btn-info btn-sm" id="btnUpHVEstGral">Actualizar Estado General</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modOnce">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodOnce" aria-expanded="true" aria-controls="collapsemodOnce">
                                    <div class="form-row">
                                        <div>
                                            11. FUNCIONAMIENTO DEL EQUIPO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodOnce" class="collapse" aria-labelledby="modOnce">
                            <div class="card-body">
                                <div class="overflow px-1">
                                    <form id="formUpFuncaAcFijo">
                                        <input type="hidden" id="id_funca" name="id_funca" value="<?php echo $funcionamiento['id_funca'] != '' ? $funcionamiento['id_funca'] : 0 ?>">
                                        <table class="w-100 table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                            <tr class="div-gris text-center">
                                                <td rowspan="2" class="w-25 border">FUNCIONAMIENTO</td>
                                                <td class="w-15 border">Bueno</td>
                                                <td class="w-10 border"><input type="radio" name="estadoFunca" value="1" class="centro-vertical" <?php echo $funcionamiento['estado'] == 1 ? 'checked' : '' ?>></td>
                                                <td class="w-15 border">Regular</td>
                                                <td class="w-10 border"><input type="radio" name="estadoFunca" value="2" class="centro-vertical" <?php echo $funcionamiento['estado'] == 2 ? 'checked' : '' ?>></td>
                                                <td class="w-15 border">Malo</td>
                                                <td class="w-10 border"><input type="radio" name="estadoFunca" value="3" class="centro-vertical" <?php echo $funcionamiento['estado'] == 3 ? 'checked' : '' ?>></td>
                                            </tr>
                                            <tr class="text-center">
                                                <td class="div-gris border">Fuera de Servicio</td>
                                                <td class="div-gris border"><input type="radio" name="estadoFunca" value="4" class="centro-vertical" <?php echo $funcionamiento['estado'] == 4 ? 'checked' : '' ?>></td>
                                                <td class="div-gris border">Años fuera de servicio</td>
                                                <td colspan="3" class="p-0_5 border"><input type="number" id="numAniosOut" name="numAniosOut" class="form-control form-control-sm altura border-0 rounded-0" value="<?php echo $funcionamiento['anios_fuera_servicio'] ?>"></td>
                                            </tr>
                                            <tr class="border border-bottom-0">
                                                <td colspan="7"><label for="txtaFuncionamiento" class="w-100 mb-0">Causas:</label></td>
                                            </tr>
                                            <tr class="border border-top-0">
                                                <td colspan="7" class="p-0_5"><textarea class="form-control form-control-sm border-0 rounded-0" id="txtaFuncionamiento" name="txtaFuncionamiento" rows="5"><?php echo $funcionamiento['causas'] ?></textarea></td>
                                            </tr>
                                        </table>
                                        <div class="text-center pt-3 mb-0">
                                            <button type="button" class="btn btn-info btn-sm" id="btnUpHVFuncionamiento">Actualizar Funcionamiento</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- parte-->
                    <div class="card">
                        <div class="card-header card-header-detalles py-0 headings" id="modDoce">
                            <h5 class="mb-0">
                                <a class="btn btn-link-acordeon sombra collapsed" data-toggle="collapse" data-target="#collapsemodDoce" aria-expanded="true" aria-controls="collapsemodDoce">
                                    <div class="form-row">
                                        <div>
                                            12. REGISTRO DE LAS ACTIVIDADES DE MANTENIMIENTO PREVENTIVO Y/O CORRECTIVO
                                        </div>
                                    </div>
                                </a>
                            </h5>
                        </div>
                        <div id="collapsemodDoce" class="collapse" aria-labelledby="modDoce">
                            <div class="card-body">
                                <div class="px-1">
                                    <div class="overflow">
                                        <form id="formRegMmtoCoPr">
                                            <table class="w-100 table-bordered table-sm text-left" style="font-size:85%; white-space: nowrap;">
                                                <tr>
                                                    <td class="div-gris">Nombre del equipo</td>
                                                    <td colspan="4" class="px-2"><?php echo $_POST['bien_servicio'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="div-gris">Marca</td>
                                                    <td colspan="4" class="px-2"><?php echo $_POST['marca'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="div-gris">Modelo</td>
                                                    <td colspan="4" class="px-2"><?php echo $_POST['modelo'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="div-gris">N°. de Inventario</td>
                                                    <td colspan="4" class="px-2"><?php echo $_POST['id_serial'] ?></td>
                                                </tr>
                                                <tr>
                                                    <td class="div-gris">Serie</td>
                                                    <td colspan="4" class="px-2"><?php echo $_POST['num_serial'] ?></td>
                                                </tr>
                                                <tr class="text-center div-gris">
                                                    <th>FECHA</th>
                                                    <th>TIPO DE<br>MANTENIMIENTO</th>
                                                    <th>DESCRIPCION DE LAS ACTIVIDADES<br>DE MANTENIMIENTO</th>
                                                    <th>REPORTE No.</th>
                                                    <th>PERSONAL RESPONSABLE</th>
                                                </tr>

                                                <?php
                                                $lastdate = '0001-01-01';
                                                $obsvnes = '<ul class="mb-0">';
                                                foreach ($lismmtos as $lm) { ?>
                                                    <tr>
                                                        <td class="px-2">
                                                            <?php
                                                            echo $lm['fecha'];
                                                            $lastdate = $lm['fecha'];
                                                            $obsvnes .= '<li>' . $lm['observaciones'] . '</li>';
                                                            ?>
                                                        </td>
                                                        <td class="px-2">
                                                            <?php echo $lm['tipo_mmto'] == 1 ? 'Preventivo' : 'Correctivo' ?>
                                                        </td>
                                                        <td class="p-0_5">
                                                            <textarea class="form-control form-control-sm border-0 rounded-0" style="height: 27px;" readonly><?php echo $lm['descripcion'] ?></textarea>
                                                        </td>
                                                        <td class="px-2">
                                                            <?php echo $lm['no_reporte'] ?>
                                                        </td>
                                                        <td class="p-0_5">
                                                            <div class="form-row w-100 p-0">
                                                                <div class="col-md-9 centro-vertical pt-2 pl-2">
                                                                    <?php
                                                                    $id_ter = $lm['tercero_resp'];
                                                                    $key = array_search($id_ter, array_column($dat_ter, 'id_tercero'));
                                                                    if (false !== $key) {
                                                                        $tercer = $dat_ter[$key]['apellido1'] . ' ' . $dat_ter[$key]['apellido2'] . ' ' . $dat_ter[$key]['nombre2'] . ' ' . $dat_ter[$key]['nombre1'] . ' ' . $dat_ter[$key]['razon_social'];
                                                                    } else {
                                                                        $tercer = '';
                                                                    }
                                                                    echo $tercer;
                                                                    if (end($lismmtos) == $lm) {
                                                                    ?></div>
                                                                <div class="col-md-3 text-center p-0">
                                                                    <button value="<?php echo $lm['id_registro'] ?>" class="btn btn-outline-danger btn-sm btn-circle shadow-gb borrar" title="Eliminar registro"><i class="fas fa-trash-alt"></i></button>
                                                                </div>
                                                            <?php } ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php
                                                }
                                                $obsvnes .= '</ul>'; ?>

                                                <tr>
                                                    <td class="p-0_5">
                                                        <input type="date" id="fecMmto" name="fecMmto" class="form-control form-control-sm altura border-0 rounded-0">
                                                    </td>
                                                    <td class="p-0_5">
                                                        <select id="tipoMmnto" name="tipoMmnto" class="form-control form-control-sm altura border-0 rounded-0">
                                                            <option value="0">--Seleccione--</option>
                                                            <?php
                                                            if ($obj_hv['tipo'] == 1) {
                                                                echo '<option value="1">Preventivo</option>';
                                                            } else {
                                                                echo '<option value="2">Correctivo</option>';
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td class="p-0_5">
                                                        <textarea name="txtaDescribe" class="form-control form-control-sm border-0 rounded-0" style="height: 27px;"></textarea>
                                                    </td>
                                                    <td class="p-0_5">
                                                        <input type="number" id="numReporte" name="numReporte" class="form-control form-control-sm altura border-0 rounded-0">
                                                    </td>
                                                    <td class="p-0_5">
                                                        <input type="text" id="buscaTercero" class="form-control form-control-sm altura border-0 rounded-0">
                                                        <input type="hidden" id="idTercero" name="idTercero" value="0">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="p-0_5">
                                                        <div class="form-control form-control-sm rounded-0 text-center border-0">
                                                            OBSERVACIONES (Registre la fecha y la información relevante sobre mantenimiento del equipo).
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="p-0_5">
                                                        <?php echo $obsvnes ?>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="5" class="p-0_5">
                                                        <textarea name="txtaObservaciones" class="form-control form-control-sm border-0 rounded-0" rows="5"></textarea>
                                                    </td>
                                                </tr>

                                            </table>
                                        </form>
                                        <div class="text-center pt-3 mb-0">
                                            <button type="button" class="btn btn-info btn-sm" id="btnUpHVRegMmtoCoPr">Registro Mantinimeinto Correctivo o Preventivo </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
        } else {
            echo 'Error al obtener datos<br>' . $cmd->errorInfo()[2];
            echo '1. Comprobar conexión a la base de datos<br>';
            echo '2. Comprobar que el activo fijo tenga un traslado a un centro de costo<br>';
            $cmd = null;
        }
        ?>
        <div class="text-center pt-3 pb-3">
            <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>