<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_aquisicion = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida ');
include '../../../../permisos.php';
$key = array_search('53', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_adquisicion`
                , `id_modalidad`
                , `id_area`
                , `fecha_adquisicion`
                , `val_contrato`
                , `id_tipo_bn_sv`
                , `vigencia`
                , `obligaciones`
                , `objeto`
                , `id_tercero`
                , `estado`
            FROM
                `ctt_adquisiciones`
            WHERE (`id_adquisicion` = $id_aquisicion)";
    $rs = $cmd->query($sql);
    $adquisicion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_adquisiciones`.`id_adquisicion`
                , `ctt_adquisiciones`.`id_modalidad`
                , `ctt_adquisiciones`.`id_area`
                , `ctt_adquisiciones`.`fecha_adquisicion`
                , `ctt_adquisiciones`.`val_contrato`
                , `ctt_adquisiciones`.`id_tipo_bn_sv`
                , `ctt_adquisiciones`.`vigencia`
                , `ctt_adquisiciones`.`obligaciones`
                , `ctt_adquisiciones`.`objeto`
                , `ctt_adquisiciones`.`id_tercero`
                , `ctt_adquisiciones`.`estado`
                , `ctt_adquisicion_detalles`.`id_bn_sv`
                , `ctt_adquisicion_detalles`.`cantidad`
                , `ctt_adquisicion_detalles`.`val_estimado_unid`
            FROM
                `ctt_adquisicion_detalles`
                INNER JOIN `ctt_adquisiciones` 
                    ON (`ctt_adquisicion_detalles`.`id_adquisicion` = `ctt_adquisiciones`.`id_adquisicion`)
            WHERE (`ctt_adquisiciones`.`id_adquisicion` = $id_aquisicion)";
    $rs = $cmd->query($sql);
    $detalles = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_adquisicion`,`id_centro_costo`,`horas_mes` 
            FROM `ctt_destino_contrato` 
            WHERE `id_adquisicion` = $id_aquisicion";
    $rs = $cmd->query($sql);
    $destino = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_est_prev`,
                `id_compra`,
                `fec_ini_ejec`,
                `fec_fin_ejec`,
                `val_contrata`,
                `id_forma_pago`,
                `id_supervisor`,
                `necesidad`,
                `act_especificas`,
                `prod_entrega`,
                `obligaciones`,
                `forma_pago`,
                `num_ds`,
                `requisitos`,
                `garantia`,
                `describe_valor`
            FROM `ctt_estudios_previos`
            WHERE `id_compra` = $id_aquisicion";
    $rs = $cmd->query($sql);
    $estudios = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM ctt_modalidad ORDER BY modalidad ASC";
    $rs = $cmd->query($sql);
    $modalidad = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_area`, `area` FROM `tb_area_c` ORDER BY `area` ASC";
    $rs = $cmd->query($sql);
    $areas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                id_tipo_b_s, tipo_compra, tipo_contrato, tipo_bn_sv
            FROM
                tb_tipo_bien_servicio
            INNER JOIN tb_tipo_contratacion 
                ON (tb_tipo_bien_servicio.id_tipo_cotrato = tb_tipo_contratacion.id_tipo)
            INNER JOIN tb_tipo_compra 
                ON (tb_tipo_contratacion.id_tipo_compra = tb_tipo_compra.id_tipo)
            ORDER BY tipo_compra, tipo_contrato, tipo_bn_sv";
    $rs = $cmd->query($sql);
    $tbnsv = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`,`nom_sede` AS `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_form_pago`, `descripcion`
            FROM
                `tb_forma_pago_compras` ORDER BY `descripcion` ASC ";
    $rs = $cmd->query($sql);
    $forma_pago = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_terceros`.`id_tercero`
                , `tb_rel_tercero`.`id_tercero_api`
            FROM
                `tb_rel_tercero`
                INNER JOIN `seg_terceros` 
                    ON (`tb_rel_tercero`.`id_tercero_api` = `seg_terceros`.`id_tercero_api`)
            WHERE `seg_terceros`.`estado` = 1 AND `tb_rel_tercero`.`id_tipo_tercero` = 3";
    $rs = $cmd->query($sql);
    $terceros_sup = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if (!empty($terceros_sup)) {
    $id_t = [];
    foreach ($terceros_sup as $l) {
        $id_t[] = $l['id_tercero_api'];
    }
    $payload = json_encode($id_t);
    //API URL
    $url = $api . 'terceros/datos/res/lista/terceros';
    $ch = curl_init($url);
    //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $supervisor = json_decode($result, true);
} else {
    $supervisor = [];
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
            `id_poliza`
            , `descripcion`
            , `porcentaje`
        FROM
            `tb_polizas` ORDER BY `descripcion` ASC";
    $rs = $cmd->query($sql);
    $polizas = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$est_prev = $estudios['id_est_prev'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_garantia`, `id_est_prev`, `id_poliza`
            FROM
                `seg_garantias_compra`
            WHERE `id_est_prev`  = $est_prev";
    $rs = $cmd->query($sql);
    $garantias = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DUPLICAR ADQUISICIÓN</h5>
        </div>
        <form id="formDuplicaAdq">
            <input type="hidden" name="id_compra" value="<?php echo $id_aquisicion ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-3">
                    <label for="datFecAdq" class="small">FECHA ADQUISICIÓN</label>
                    <input type="date" name="datFecAdq" id="datFecAdq" class="form-control form-control-sm" value="<?php echo $adquisicion['fecha_adquisicion'] ?>">
                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-3">
                    <label for="slcModalidad" class="small">MODALIDAD CONTRATACIÓN</label>
                    <select id="slcModalidad" name="slcModalidad" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($modalidad as $mo) {
                            $slc = $mo['id_modalidad'] == $adquisicion['id_modalidad'] ? 'selected' : '';
                            echo '<option value="' . $mo['id_modalidad'] . '">' . $mo['modalidad'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="numTotalContrato" class="small">VALOR ESTIMADO</label>
                    <input type="number" name="numTotalContrato" id="numTotalContrato" class="form-control form-control-sm" value="<?php echo $adquisicion['val_contrato'] ?>">
                </div>
                <div class="form-group col-md-3">
                    <label for="slcAreaSolicita" class="small">ÁREA SOLICITANTE</label>
                    <select id="slcAreaSolicita" name="slcAreaSolicita" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($areas as $ar) {
                            $slc = $ar['id_area'] == $adquisicion['id_area'] ? 'selected' : '';
                            echo '<option value="' . $ar['id_area'] . '" ' . $slc . '>' . $ar['area'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="slcTipoBnSv" class="small">TIPO DE BIEN O SERVICIO</label>
                    <select id="slcTipoBnSv" name="slcTipoBnSv" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($tbnsv as $tbs) {
                            $slc = $tbs['id_tipo_b_s'] == $adquisicion['id_tipo_bn_sv'] ? 'selected' : '';
                            echo '<option value="' . $tbs['id_tipo_b_s'] . '" ' . $slc . '>' . $tbs['tipo_compra'] . ' || ' . $tbs['tipo_contrato'] . ' || ' . $tbs['tipo_bn_sv'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">OBJETO</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"><?php echo $adquisicion['objeto'] ?></textarea>
                </div>
            </div>
            <div id="contenedor">
                <?php
                $num = 1;
                foreach ($destino as $des) {
                    $id_cc = $des['id_centro_costo'];
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                        $sql = "SELECT `id_sede` FROM `tb_centro_costo_x_sede` WHERE `id_x_sede` = $id_cc";
                        $rs = $cmd->query($sql);
                        $cencos = $rs->fetch();
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                    $id_sede = $cencos['id_sede'];
                    try {
                        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                        $sql = "SELECT
                                    `tb_centro_costo_x_sede`.`id_x_sede`
                                    , `tb_centros_costo`.`descripcion`
                                FROM
                                    `tb_centro_costo_x_sede`
                                    INNER JOIN `tb_sedes` 
                                        ON (`tb_centro_costo_x_sede`.`id_sede` = `tb_sedes`.`id_sede`)
                                    INNER JOIN `tb_centros_costo` 
                                        ON (`tb_centro_costo_x_sede`.`id_centro_c` = `tb_centros_costo`.`id_centro`)
                                WHERE `tb_centro_costo_x_sede`.`id_sede` = $id_sede";
                        $rs = $cmd->query($sql);
                        $centros = $rs->fetchAll();
                        $cmd = null;
                    } catch (PDOException $e) {
                        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
                    }
                    if ($num == 1) {
                ?>
                        <div class="form-row px-4 pt-2">
                            <div class="form-group col-md-4 mb-2">
                                <label class="small">SEDE</label>
                                <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC">
                                    <?php
                                    foreach ($sedes as $s) {
                                        $slc = $s['id_sede'] == $id_sede ? 'selected' : '';
                                        echo '<option value="' . $s['id_sede'] . '" ' . $slc . '>' . $s['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <label class="small">CENTRO DE COSTO</label>
                                <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto">
                                    <?php
                                    foreach ($centros as $c) {
                                        $slc = $c['id_x_sede'] == $des['id_centro_costo'] ? 'selected' : '';
                                        echo '<option value="' . $c['id_x_sede'] . '" ' . $slc . '>' . $c['descripcion'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <label for="numHorasMes" class="small">Horas asignadas / mes</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="numHorasMes[]" class="form-control" value="<?php echo $des['horas_mes'] ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-success" type="button" id="addRowSedes"><i class="fas fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php
                    } else {
                    ?>
                        <div class="form-row px-4 pt-2">
                            <div class="form-group col-md-4 mb-2">
                                <select name="slcSedeAC[]" class="form-control form-control-sm slcSedeAC">
                                    <?php
                                    foreach ($sedes as $s) {
                                        $slc = $s['id_sede'] == $id_sede ? 'selected' : '';
                                        echo '<option value="' . $s['id_sede'] . '" ' . $slc . '>' . $s['nombre'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <select name="slcCentroCosto[]" class="form-control form-control-sm slcCentroCosto">
                                    <?php
                                    foreach ($centros as $c) {
                                        $slc = $c['id_x_sede'] == $des['id_centro_costo'] ? 'selected' : '';
                                        echo '<option value="' . $c['id_x_sede'] . '" ' . $slc . '>' . $c['descripcion'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-md-4 mb-2">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="numHorasMes[]" class="form-control" value="<?php echo $des['horas_mes'] ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-danger delRowSedes" type="button"><i class="fas fa-minus"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                    $num++;
                }
                ?>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="ccnit" class="small">TERCERO</label>
                    <input type="text" id="SeaTercer" class="form-control form-control-sm">
                    <input type="hidden" id="id_tercero" name="id_tercero" value="0">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="datFecIniEjec" class="small">FECHA INICIAL</label>
                    <input type="date" name="datFecIniEjec" id="datFecIniEjec" class="form-control form-control-sm" value="<?php echo $estudios['fec_ini_ejec'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="datFecFinEjec" class="small">FECHA FINAL</label>
                    <input type="date" name="datFecFinEjec" id="datFecFinEjec" class="form-control form-control-sm" value="<?php echo $estudios['fec_fin_ejec'] ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numValContrata" class="small">Valor total contrata</label>
                    <input type="number" name="numValContrata" id="numValContrata" class="form-control form-control-sm" value="<?php echo $estudios['val_contrata'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="slcFormPago" class="small">FORMA DE PAGO</label>
                    <select id="slcFormPago" name="slcFormPago" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <?php
                        foreach ($forma_pago as $fp) {
                            $slc = $fp['id_form_pago'] == $estudios['id_forma_pago'] ? 'selected' : '';
                            echo '<option value="' . $fp['id_form_pago'] . '" ' . $slc . '>' . $fp['descripcion'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="slcSupervisor" class="small">SUPERVISOR</label>
                    <select id="slcSupervisor" name="slcSupervisor" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="A">PENDIENTE</option>
                        <?php
                        foreach ($supervisor as $s) {
                            $slc = $s['id_tercero'] == $estudios['id_supervisor'] ? 'selected' : '';
                            echo '<option value="' . $s['id_tercero'] . '">' . $s['apellido1'] . ' ' . $s['apellido2'] . ' ' . $s['nombre1'] . ' ' . $s['nombre2'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="numDS" class="small">Número DC</label>
                    <input type="number" name="numDS" id="numDS" class="form-control form-control-sm" value="<?php echo $estudios['num_ds'] ?>">
                </div>
            </div>
            <span class="small">PÓLIZAS</span>
            <div class="form-row px-4 pt-2">
                <?php
                $cant = 1;
                foreach ($polizas as $pz) {
                    $chequeado = '';
                    $idp = $pz['id_poliza'];
                    $key = array_search($idp, array_column($garantias, 'id_poliza'));
                    if (false !== $key) {
                        $chequeado = 'checked';
                    }
                ?>
                    <div class="form-group col-md-4 mb-0">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <div class="input-group-text">
                                    <input type="checkbox" aria-label="Checkbox for following text input" id="check_<?php echo $cant;
                                                                                                                    $cant++ ?>" name="check[]" value="<?php echo $pz['id_poliza'] ?>" <?php echo $chequeado ?>>
                                </div>
                            </div>
                            <div class="form-control form-control-sm" aria-label="Text input with checkbox" style="font-size: 55%;"><?php echo $pz['descripcion'] . ' ' . $pz['porcentaje'] . '%' ?> </div>
                        </div>
                    </div>
                <?php
                }
                ?>
            </div>
            <div class="form-row text-center px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="txtDescNec" class="small">Descripción de la necesidad</label>
                    <textarea name="txtDescNec" id="txtDescNec" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['necesidad']))) ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtActEspecificas" class="small">Actividades específicas</label>
                    <textarea name="txtActEspecificas" id="txtActEspecificas" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['act_especificas']))) ?></textarea>
                </div>
            </div>
            <div class="form-row text-center px-4">
                <div class="form-group col-md-6">
                    <label for="txtProdEntrega" class="small">PRODUCTOS A ENTREGAR</label>
                    <textarea name="txtProdEntrega" id="txtProdEntrega" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['prod_entrega']))) ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtObligContratista" class="small">Obligaciones del Contratista</label>
                    <textarea name="txtObligContratista" id="txtObligContratista" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['obligaciones']))) ?></textarea>
                </div>
            </div>
            <div class="form-row text-center px-4">
                <div class="form-group col-md-6">
                    <label for="txtDescValor" class="small">Descripción de valor</label>
                    <textarea name="txtDescValor" id="txtDescValor" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['describe_valor']))) ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtFormPago" class="small">Forma de Pago</label>
                    <textarea name="txtFormPago" id="txtFormPago" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['forma_pago']))) ?></textarea>
                </div>

            </div>
            <div class="form-row text-center px-4">
                <div class="form-group col-md-6">
                    <label for="txtReqMinHab" class="small">Req. mínimos Habilitantes</label>
                    <textarea name="txtReqMinHab" id="txtReqMinHab" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['requisitos']))) ?></textarea>
                </div>
                <div class="form-group col-md-6">
                    <label for="txtGarantias" class="small">Garantías Contratación</label>
                    <textarea name="txtGarantias" id="txtGarantias" cols="30" rows="2" class="form-control form-control-sm"><?php echo str_replace('<br />', '', nl2br(str_replace('||', "\n", $estudios['garantia']))) ?></textarea>
                </div>
            </div>
        </form>
        <div class="text-center">
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnDuplicaAdq">Duplicar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </div>

    </div>
</div>