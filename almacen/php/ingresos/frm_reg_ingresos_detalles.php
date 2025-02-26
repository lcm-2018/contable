<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_bodega = isset($_POST['id_bodega']) && $_POST['id_bodega'] ? $_POST['id_bodega'] : -1;
$id_articulo = isset($_POST['id_articulo']) ? $_POST['id_articulo'] : -1;
$id_lote = isset($_POST['id_lote']) ? $_POST['id_lote'] : -1;
$cantidad = isset($_POST['cantidad']) ? $_POST['cantidad'] : 0;

$id = isset($_POST['id']) ? $_POST['id'] : -1;

$sql = "SELECT far_orden_ingreso_detalle.*,
            far_medicamentos.id_med,far_medicamentos.nom_medicamento AS nom_articulo,
            far_presentacion_comercial.nom_presentacion,IFNULL(far_presentacion_comercial.cantidad,1) AS cantidad_umpl
        FROM far_orden_ingreso_detalle
        INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_orden_ingreso_detalle.id_lote)
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_orden_ingreso_detalle.id_presentacion)
        WHERE id_ing_detalle=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    $obj['iva'] = 0;
    $obj['id_med'] = $id_articulo;
    $obj['id_lote'] = $id_lote;
    $obj['cantidad'] = (int)$cantidad;
} 

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DETALLE EN ORDEN DE INGRESO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_ingresos_detalles">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-8">
                        <label for="txt_nom_art" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                        <input type="text" id="id_txt_nom_art" name="id_txt_nom_art" value="<?php echo $obj['id_med'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_lote_art" class="small">Lote</label>
                        <div class=" form-row">
                            <div class="form-group col-md-9">
                                <select class="form-control form-control-sm" id="sl_lote_art" name="sl_lote_art">
                                    <?php lotes_articulo($cmd, $id_bodega, $obj['id_med'], $obj['id_lote']) ?>
                                </select>                    
                                </div>                                    
                            <div class="form-group col-md-3">    
                                <button type="button" class="btn btn-primary btn-sm" id="btn_nuevo_lote">Nuevo</button>                        
                            </div>    
                        </div>
                    </div>    
                    <div class="form-group col-md-10">
                        <label for="txt_pre_lot" class="small">Unidad de Medida de Presentación del Lote</label>
                        <input type="text" class="form-control form-control-sm" id="txt_pre_lot" value="<?php echo $obj['nom_presentacion'] ?>">
                        <input type="hidden" id="id_txt_pre_lot" name="id_txt_pre_lot" value="<?php echo $obj['id_presentacion'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_can_lot" class="small">Cant. X UMPL</label>
                        <input type="text" class="form-control form-control-sm" id="txt_can_lot" name="txt_can_lot" value="<?php echo $obj['cantidad_umpl'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_can_ing" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm numberint" id="txt_can_ing" name="txt_can_ing" required value="<?php echo $obj['cantidad'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_val_uni" class="small">Vr. Unitario</label>
                        <input type="text" class="form-control form-control-sm numberfloat" id="txt_val_uni" name="txt_val_uni" required value="<?php echo formato_decimal($obj['valor_sin_iva']) ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_por_iva" class="small">% IVA</label>
                        <select class="form-control form-control-sm" id="sl_por_iva" name="sl_por_iva">
                            <?php iva($obj['iva']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_val_cos" class="small">Vr. Costo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_val_cos" name="txt_val_cos" value="<?php echo $obj['valor'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_observacion" class="small">Observación</label>
                        <input type="text" class="form-control form-control-sm" id="txt_observacion" name="txt_observacion" value="<?php echo $obj['observacion'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_detalle">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>