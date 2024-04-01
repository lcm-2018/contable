<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$tipo = isset($_POST['tip_eaf_det']) ? $_POST['tip_eaf_det'] : exit('Acción no permitida');
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
    $sql = "SELECT `descripcion` FROM  `seg_tipo_entrada` WHERE `id_entrada` = $tipo";
    $rs = $cmd->query($sql);
    $tentradas = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$tipol = $tentradas['descripcion'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DETALLE DE ACTIVOS FIJOS POR <?php echo $tipol ?></h5>
        </div>
        <div class="px-2">
            <form id="formRegDetActFijoDO">
                <input name="tipoEntrada" hidden value="<?php echo $tipo ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="nom_prod" class="small">Buscar Activo Fijo</label>
                        <input type="text" id="busc_acfijo" class="form-control form-control-sm">
                        <input type="hidden" id="id_acfijo" name="id_acfijo" value="0">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="cantidad" class="small">Cantidad</label>
                        <input type="number" id="cantidad" name="cantidad" class="form-control form-control-sm" min="0">
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
                        <label for="numValUnita" class="small">Valor Unitario</label>
                        <input type="number" id="numValUnita" name="numValUnita" class="form-control form-control-sm" min="0">
                    </div>
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
                    <div id="divSeriales" class="rounded border border-light w-100 text-left form-row mb-2 pt-1" style="background-color: #16a08533;">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaActFijo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button id="btnRegDetActFijoDO" type="button" class="btn btn-primary btn-sm">Registrar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>