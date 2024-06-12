<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$campos = isset($_POST['campos']) ? explode('|', $_POST['campos']) : exit('Acción no permitida');
$id_prod = $campos[0];
$id_api = $campos[1];
$bnsv = $campos[2];
$cantidad = $campos[3];
$val_uni = $campos[4];
$fecha_min = $campos[5];
$estado = trim($campos[6]);
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
?>
<input id="dateFecMin" type="hidden" value="<?php echo $fecha_min ?>">
<input id="numCantMax" type="hidden" value="<?php echo $cantidad ?>">
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">RECEPCIONAR ENTRADA</h5>
        </div>
        <div class="px-2">
            <?php
            if (strcasecmp($estado, 'PENDIENTE') == 0) {
            ?>
                <form id="formRegActivosFijos">
                    <input id="idApi" name="idApi" hidden value="<?php echo $id_api ?>">
                    <input id="idProd" name="idProd" hidden value="<?php echo $id_prod ?>">
                    <input id="numCantMax" name="numCantMax" type="hidden" value="<?php echo $cantidad ?>">
                    <input id="idtentrada" name="idtentrada" type="hidden" value="1">
                    <input id="valor_unit" name="valor_unit" type="hidden" value="<?php echo $val_uni ?>">
                    <div class=" form-row">
                        <div class="form-group col-md-12">
                            <label for="nom_prod" class="small">Descrición</label>
                            <input type="text" id="nom_prod" class="form-control form-control-sm" value="<?php echo $bnsv ?>" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="cantidad" class="small">Cantidad</label>
                            <input type="number" id="cantidad" name="cantidad" class="form-control form-control-sm" min="0" max="<?php echo $cantidad ?>" value="<?php echo $cantidad ?>">
                            <input type="hidden" id="cantMax" value="<?php echo $cantidad ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label for="mantenimiento" class="small">Mantenimiento</label>
                            <select type="text" id="mantenimiento" name="mantenimiento" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">SI</option>
                                <option value="2">NO</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label for="slcDepresiacion" class="small">Depreciable</label>
                            <select type="date" id="slcDepresiacion" name="slcDepresiacion" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">SI</option>
                                <option value="2">NO</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label for="txtMarca" class="small">Marca</label>
                            <input type="text" id="txtMarca" name="txtMarca" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-3">
                            <label for="txtModelo" class="small">Modelo</label>
                            <input id="txtModelo" name="txtModelo" class="form-control form-control-sm">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="slcTipoActivo" class="small">Tipo de Activo</label>
                            <select type="text" id="slcTipoActivo" name="slcTipoActivo" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <?php
                                foreach ($tipo_activo as $tipo) {
                                ?>
                                    <option value="<?php echo $tipo['id_tipo_act'] ?>"><?php echo $tipo['descripcion'] ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label for="txtNoSerie" class="small"># Serie</label>
                            <div class="input-group mb-1">
                                <input type="text" id="txtNoSerie" name="txtNoSerie" class="form-control form-control-sm">
                                <input type="hidden" id="txtSeriales" name="txtSeriales" value="0">
                                <div class="input-group-append">
                                    <button id="btnMasSerie" class="btn btn-sm btn-success" title="Agregar un No. de Serie"><span class="fas fa-arrow-alt-circle-right fa-lg"></span></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row px-2">
                        <div id="divSeriales" class="rounded border border-light w-100 text-left form-row mb-2" style="background-color: #16a08533;">
                        </div>
                    </div>
                    <div class="form-row text-center">
                        <div class="form-group col-md-12">
                            <label for="txtObservaActFijo" class="small">Observaciones</label>
                            <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"></textarea>
                        </div>
                    </div>
                </form>
            <?php
            } else {
                echo '<div class="alert alert-danger" role="alert">ELEMENTO YA RECEPCIONADO</div><br>';
            }
            ?>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm btnRecActFijo">Recepcionar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>