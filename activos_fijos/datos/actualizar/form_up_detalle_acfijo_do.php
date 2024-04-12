<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_detalle = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `seg_entra_detalle_activos_fijos`.`id_acfijo`
                , `seg_entra_detalle_activos_fijos`.`id_prod`
                , `ctt_bien_servicio`.`bien_servicio`
                , `seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do`
                , `seg_entra_detalle_activos_fijos`.`mantenimiento`
                , `seg_entra_detalle_activos_fijos`.`depreciable`
                , `seg_entra_detalle_activos_fijos`.`modelo`
                , `seg_entra_detalle_activos_fijos`.`marca`
                , `seg_entra_detalle_activos_fijos`.`val_unit`
                , `seg_entra_detalle_activos_fijos`.`cantidad`
                , `seg_entra_detalle_activos_fijos`.`id_tipo_activo`
                , `seg_entra_detalle_activos_fijos`.`descripcion`
                , `acf_entrada`.`id_tipo_entrada`
            FROM
                `seg_entra_detalle_activos_fijos`
                INNER JOIN `ctt_bien_servicio` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_prod` = `ctt_bien_servicio`.`id_b_s`)
                INNER JOIN `acf_entrada` 
                    ON (`seg_entra_detalle_activos_fijos`.`id_entra_acfijo_do` = `acf_entrada`.`id_entra_af`)
            WHERE `seg_entra_detalle_activos_fijos`.`id_acfijo` = '$id_detalle'";
    $rs = $cmd->query($sql);
    $detalle_af = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipo = $detalle_af['id_tipo_entrada'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_tipo_act`, `descripcion` FROM `seg_tipo_activo`";
    $rs = $cmd->query($sql);
    $tipo_activo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_serial`, `id_activo_fijo`, `placa`, `num_serial` FROM `seg_num_serial` WHERE `id_activo_fijo`= '$id_detalle'";
    $rs = $cmd->query($sql);
    $seriesc = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
if ($tipo == 3) {
    $tipol = 'DONACIÓN';
} else {
    $tipol = 'OTRA';
}
if ($tipo == 3) {
    $tipoente = 'DONADOR';
} else {
    $tipoente = 'OTRO';
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR DETALLE DE ACTIVOS FIJOS POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formUpDetActFijoDO">
                <input name="tipoEntrada" hidden value="<?php echo $tipo ?>">
                <input name="id_det_actfijo" hidden value="<?php echo $id_detalle ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="nom_prod" class="small">Buscar Activo Fijo</label>
                        <input type="text" id="busc_acfijo" class="form-control form-control-sm" value="<?php echo $detalle_af['bien_servicio'] ?>">
                        <input type="hidden" id="id_acfijo" name="id_acfijo" value="<?php echo $detalle_af['id_prod'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="cantidad" class="small">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control form-control-sm" min="0" value="<?php echo $detalle_af['cantidad'] ?>" readonly>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="mantenimiento" class="small">Mantenimiento</label>
                        <select type="text" id="mantenimiento" name="mantenimiento" class="form-control form-control-sm">
                            <?php if ($detalle_af['mantenimiento'] == 1) { ?>
                                <option value="1" selected>SI</option>
                                <option value="2">NO</option>
                            <?php } else { ?>
                                <option value="1">SI</option>
                                <option value="2" selected>NO</option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="slcDepresiacion" class="small">Depreciable</label>
                        <select type="date" id="slcDepresiacion" name="slcDepresiacion" class="form-control form-control-sm">
                            <?php if ($detalle_af['depreciable'] == 1) { ?>
                                <option value="1" selected>SI</option>
                                <option value="2">NO</option>
                            <?php } else { ?>
                                <option value="1">SI</option>
                                <option value="2" selected>NO</option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="txtMarca" class="small">Marca</label>
                        <input type="text" id="txtMarca" name="txtMarca" class="form-control form-control-sm" value="<?php echo $detalle_af['marca'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txtModelo" class="small">Modelo</label>
                        <input id="txtModelo" name="txtModelo" class="form-control form-control-sm" value="<?php echo $detalle_af['modelo'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="slcTipoActivo" class="small">Tipo de Activo</label>
                        <select type="text" id="slcTipoActivo" name="slcTipoActivo" class="form-control form-control-sm">
                            <?php
                            foreach ($tipo_activo as $tipo) {
                                if ($detalle_af['id_tipo_activo'] == $tipo['id_tipo_act']) {
                                    echo "<option value='" . $tipo['id_tipo_act'] . "' selected>" . $tipo['descripcion'] . "</option>";
                                } else {
                                    echo "<option value='" . $tipo['id_tipo_act'] . "'>" . $tipo['descripcion'] . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="numValUnita" class="small">Valor Unitario</label>
                        <input type="number" id="numValUnita" name="numValUnita" class="form-control form-control-sm" min="0" value="<?php echo $detalle_af['val_unit'] ?>">
                    </div>
                </div>
                <div class="form-row px-2">
                    <div class="text-center w-100"><label for="divSeriales" class="small"># Serial(es)</label></div>
                    <div id="divSeriales" class="rounded border border-light w-100 text-left form-row mb-2 pt-1" style="background-color: #16a08533;">
                        <?php
                        $cadena = '0';
                        foreach ($seriesc as $sc) {
                            $cadena  .= '|' . $sc['num_serial'];
                        ?>
                            <div class="input-group mb-1 col-3">
                                <input type="text" id="serieUp_<?php echo $sc['id_serial'] ?>" name="serieUp[<?php echo $sc['id_serial'] ?>]" class="form-control form-control-sm altura" value="<?php echo $sc['num_serial'] ?>">
                            </div>
                        <?php
                        } ?>
                    </div>
                    <input type="hidden" id="txtSeriales" name="txtSeriales" value="<?php echo $cadena ?>">
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaActFijo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"><?php echo $detalle_af['descripcion'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="btnUpDetActFijoDO" type="button" class="btn btn-primary btn-sm">Actualizar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>