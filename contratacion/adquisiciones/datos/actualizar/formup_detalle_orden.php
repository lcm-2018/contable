<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_do = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida ');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctt_orden_compra_detalle`.`id_detalle`
                , `ctt_orden_compra_detalle`.`cantidad`
                , `ctt_orden_compra_detalle`.`val_unid`
                , `ctt_bien_servicio`.`bien_servicio`
            FROM
                `ctt_orden_compra_detalle`
                INNER JOIN `ctt_bien_servicio` 
                    ON (`ctt_orden_compra_detalle`.`id_servicio` = `ctt_bien_servicio`.`id_b_s`)
            WHERE (`ctt_orden_compra_detalle`.`id_detalle` = $id_do)";
    $rs = $cmd->query($sql);
    $detalle = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR DETALLE DE ORDEN</h5>
        </div>
        <form id="formUpDetalleOrden">
            <input type="hidden" name="id_detalle" value="<?php echo $id_do ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="numCantidad" class="small">CANTIDAD</label>
                    <input type="number" name="numCantidad" id="numCantidad" class="form-control form-control-sm" value="<?php echo $detalle['cantidad'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="numValUnid" class="small">val. unidad</label>
                    <input type="number" name="numValUnid" id="numValUnid" class="form-control form-control-sm text-right" value="<?php echo $detalle['val_unid'] ?>">
                </div>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnUpDetalleOrdnen">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>