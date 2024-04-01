<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_ut = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_ubica_traslado_centro_costo`.`id_traslado`
                , `tb_centro_costo_x_sede`.`id_sede`
                , `seg_ubica_traslado_centro_costo`.`id_centro_costo`
                , `seg_ubica_traslado_centro_costo`.`fecha`
                , `seg_ubica_traslado_centro_costo`.`estado`
                , `seg_ubica_traslado_centro_costo`.`id_tercero_api`
                , `seg_ubica_traslado_centro_costo`.`observaciones`
            FROM
                `seg_ubica_traslado_centro_costo`
                INNER JOIN `tb_centro_costo_x_sede` 
                    ON (`seg_ubica_traslado_centro_costo`.`id_centro_costo` = `tb_centro_costo_x_sede`.`id_x_sede`)
            WHERE `id_traslado` = '$id_ut'";
    $rs = $cmd->query($sql);
    $ubicat = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_sede`, `nombre` FROM `tb_sedes`";
    $rs = $cmd->query($sql);
    $sedes = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$sede_act = $ubicat['id_sede'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `tb_centro_costo_x_sede`.`id_x_sede`
                , `tb_centros_costo`.`descripcion`
            FROM
                `tb_centro_costo_x_sede`
                INNER JOIN `tb_centros_costo` 
                    ON (`tb_centro_costo_x_sede`.`id_centro_c` = `tb_centros_costo`.`id_centro`)
            WHERE `tb_centro_costo_x_sede`.`id_sede` = '$sede_act'";
    $rs = $cmd->query($sql);
    $centros = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_estado`, `descripcion` FROM `nom_estado_acfijo`";
    $rs = $cmd->query($sql);
    $estado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
//API URL
$url = $api . 'terceros/datos/res/datos/id/' . $ubicat['id_tercero_api'];
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$terceros = json_decode($result, true);
if ($terceros != 0) {
    $nom_tercero = mb_strtoupper($terceros[0]['apellido1'] . ' ' . $terceros[0]['apellido2'] . ' ' . $terceros[0]['nombre1'] . ' ' . $terceros[0]['nombre2'] . $terceros[0]['razon_social'] . ' || ' . $terceros[0]['cc_nit']);
    $id_ter_api = $terceros[0]['id_tercero'];
} else {
    $nom_tercero = '';
    $id_ter_api = '0';
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR UBICACIÓN O TRASLADO DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formUpUbicaTRasladAcFijo">
                <input name="id_traslado" hidden value="<?php echo $id_ut ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="slcSedeUbTr" class="small">SEDE</label>
                        <select name="slcSedeUbTr" id="slcSedeUbTr" class="form-control form-control-sm">
                            <?php foreach ($sedes as $s) {
                                if ($s['id_sede'] == $ubicat['id_sede']) { ?>
                                    <option value="<?php echo $s['id_sede'] ?>" selected><?php echo $s['nombre'] ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $s['id_sede'] ?>"><?php echo $s['nombre'] ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcCentroCosto" class="small">CENTRO DE COSTO</label>
                        <select name="slcCentroCosto" id="slcCentroCosto" class="form-control form-control-sm">
                            <?php
                            foreach ($centros as $c) {
                                if ($c['id_x_sede'] == $ubicat['id_centro_costo']) { ?>
                                    <option value="<?php echo $c['id_x_sede'] ?>" selected><?php echo $c['descripcion'] ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $c['id_x_sede'] ?>"><?php echo $c['descripcion'] ?></option>
                            <?php }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="fecUbicTrasl" class="small">Fecha</label>
                        <input type="date" id="fecUbicTrasl" name="fecUbicTrasl" class="form-control form-control-sm" value="<?php echo $ubicat['fecha'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcEstadoAcFijo" class="small">estado</label>
                        <select name="slcEstadoAcFijo" id="slcEstadoAcFijo" class="form-control form-control-sm">
                            <?php foreach ($estado as $e) {
                                if ($e['id_estado'] == $ubicat['id_estado']) { ?>
                                    <option value="<?php echo $e['id_estado'] ?>" selected><?php echo $e['descripcion'] ?></option>
                                <?php } else { ?>
                                    <option value="<?php echo $e['id_estado'] ?>"><?php echo $e['descripcion'] ?></option>
                            <?php }
                            } ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="txtBuscaTerceroResp" class="small">Tercero Responsable</label>
                        <input type="text" id="txtBuscaTerceroResp" class="form-control form-control-sm" value="<?php echo $nom_tercero ?>">
                        <input type="hidden" id="numTercerResp" name="numTercerResp" value="<?php echo $id_ter_api ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaUbicaTraslado" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaUbicaTraslado" name="txtObservaUbicaTraslado" rows="3"><?php echo $ubicat['observaciones'] ?></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnUpUbicaTrasladoActFijo" type="button" class="btn btn-primary btn-sm">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>