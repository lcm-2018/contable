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

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$id_articulo = isset($_POST['id_articulo']) ? $_POST['id_articulo'] : 0;
$sql = "SELECT far_medicamento_lote.*,
            far_bodegas.nombre AS nom_bodega,
            far_presentacion_comercial.nom_presentacion,far_presentacion_comercial.cantidad AS cantidad_umpl
        FROM far_medicamento_lote
        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_lote.id_presentacion)
        INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_medicamento_lote.id_bodega)
        WHERE id_lote=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto
    $obj['id_marca'] = 0;
    $obj['estado'] = 1;
    $bodega = bodega_principal($cmd);
    $obj['id_bodega'] = $bodega['id_bodega'];
    $obj['nom_bodega'] = $bodega['nom_bodega'];
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR LOTE DE ARTICULO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de lote-->
            <form id="frm_reg_articulos_lotes">
                <input type="hidden" id="id_lote" name="id_lote" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-5">
                        <label for="txt_nom_bod" class="small">Bodega</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_bod" class="small" value="<?php echo $obj['nom_bodega'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_nom_bod" name="id_txt_nom_bod" value="<?php echo $obj['id_bodega'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_num_lot" class="small">lote</label>
                        <input type="text" class="form-control form-control-sm valcode" id="txt_num_lot" name="txt_num_lot" required value="<?php echo $obj['lote'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_ven" class="small">Fecha de Vencimiento</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_ven" name="txt_fec_ven" required value="<?php echo $obj['fec_vencimiento'] ?>">
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="txt_reg_inv" class="small">Registro Invima</label>
                        <input type="text" class="form-control form-control-sm valcode" id="txt_reg_inv" name="txt_reg_inv" required value="<?php echo $obj['reg_invima'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_ser_ref" class="small">Serie/Ref. (Disp. Médico)</label>
                        <input type="text" class="form-control form-control-sm valcode" id="txt_ser_ref" name="txt_ser_ref" required value="<?php echo $obj['serie'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="sl_marca_lot" class="small">Marca</label>
                        <select class="form-control form-control-sm" id="sl_marca_lot" name="sl_marca_lot">
                            <?php marcas($cmd,'', $obj['id_marca']) ?>
                        </select>
                    </div> 
                    <div class="form-group col-md-9">
                        <label for="txt_pre_lote" class="small">Unidad de Medida de Presentación del Lote</label>
                        <input type="text" class="form-control form-control-sm" id="txt_pre_lote" value="<?php echo $obj['nom_presentacion'] ?>">
                        <input type="hidden" id="id_txt_pre_lote" name="id_txt_pre_lote" value="<?php echo $obj['id_presentacion'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_can_lote" class="small">Cant.X U.Medida Pres. Lote</label>
                        <input type="text" class="form-control form-control-sm" id="txt_can_lote" value="<?php echo $obj['cantidad_umpl'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-9">
                        <label for="sl_cum_lot" class="small">CUM</label>
                        <select class="form-control form-control-sm" id="sl_cum_lot" name="sl_cum_lot">
                            <?php cums_articulo($cmd, $id_articulo, $obj['id_cum']) ?>
                        </select>
                    </div>                    
                    <div class="form-group col-md-3">
                        <label for="sl_estado_lot" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado_lot" name="sl_estado_lot">
                            <?php estados_registros('', $obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_lote">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
