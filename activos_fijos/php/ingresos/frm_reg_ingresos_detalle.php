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

$id_art = isset($_POST['idart']) ? $_POST['idart'] : -1;
$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT acf_orden_ingreso_detalle.*, 
            far_medicamentos.nom_medicamento AS nom_articulo
        FROM acf_orden_ingreso_detalle
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med=acf_orden_ingreso_detalle.id_articulo)
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
    $articulo = datos_articulo($cmd, $id_art);
    $obj['iva'] = 0;
    $obj['id_articulo'] = $articulo['id_med'];
    $obj['nom_articulo'] = $articulo['nom_articulo'];
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DETALLE EN ORDEN DE INGRESO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_ingresos_detalle">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_nom_art" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_nom_art" name="id_txt_nom_art" value="<?php echo $obj['id_articulo'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_can_ing" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm numberint" id="txt_can_ing" name="txt_can_ing" required value="<?php echo $obj['cantidad'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_val_uni" class="small">Vr. Unitario</label>
                        <input type="text" class="form-control form-control-sm numberfloat" id="txt_val_uni" name="txt_val_uni" required value="<?php echo $obj['valor_sin_iva'] ?>">
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