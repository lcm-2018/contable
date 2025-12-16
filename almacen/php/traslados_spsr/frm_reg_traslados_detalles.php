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

$id_lote = isset($_POST['id_lote']) ? $_POST['id_lote'] : -1;
$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT far_traslado_r_detalle.*,
            far_medicamento_lote.lote,far_medicamento_lote.existencia,
            CONCAT(far_medicamentos.nom_medicamento,IF(far_medicamento_lote.id_marca=0,'',CONCAT(' - ',acf_marca.descripcion))) AS nom_articulo
        FROM far_traslado_r_detalle
        INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_traslado_r_detalle.id_lote_origen)
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
        INNER JOIN acf_marca ON (acf_marca.id=far_medicamento_lote.id_marca)
        WHERE id_tra_detalle=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;

    $lote = datos_lote($cmd, $id_lote);
    $obj['id_lote_origen'] = $lote['id_lote'];
    $obj['lote'] = $lote['lote'];
    $obj['nom_articulo'] = $lote['nom_articulo'];
    $obj['valor'] = $lote['val_promedio'];
    $obj['existencia'] = $lote['existencia'];
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DETALLE EN TRASLADO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_traslados_detalles">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-9">
                        <label for="txt_nom_art" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_nom_lot" class="small">Lote</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_lot" class="small" value="<?php echo $obj['lote'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_nom_lot" name="id_txt_nom_lot" value="<?php echo $obj['id_lote_origen'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_can_tra" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm numberint" id="txt_can_tra" name="txt_can_tra" value="<?php echo $obj['cantidad'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_val_pro" class="small">Vr. Promedio</label>
                        <input type="text" class="form-control form-control-sm" id="txt_val_pro" name="txt_val_pro" value="<?php echo $obj['valor'] ?>" readonly="readonly">
                    </div>    
                    <div class="form-group col-md-3">
                        <label for="txt_exi_lote" class="small">Existencia Actual</label>
                        <input type="text" class="form-control form-control-sm" id="txt_exi_lote" name="txt_exi_lote" value="<?php echo $obj['existencia'] ?>" readonly="readonly">
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