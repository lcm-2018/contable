<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_md = isset($_POST['id_md']) ? $_POST['id_md'] : -1;

$sql = "SELECT MD.*,HV.placa,HV.num_serial,FM.nom_medicamento AS nom_articulo,
            CASE MD.estado_general WHEN 1 THEN 'BUENO' WHEN 2 THEN 'REGULAR' WHEN 3 THEN 'MALO' WHEN 4 THEN 'SIN SERVICIO' END AS estado_general
        FROM acf_mantenimiento_detalle AS MD
        INNER JOIN acf_hojavida AS HV ON (HV.id_activo_fijo=MD.id_activo_fijo)
        INNER JOIN far_medicamentos FM ON (FM.id_med=HV.id_articulo)
        WHERE MD.id_mant_detalle=" . $id_md . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

$editar = in_array($obj['estado'],[1,2]) && $id_md != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">OBSERVACIÓN DE FINALIZACIÓN DE MANTENIMIENTO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_mantenimiento_detalle">
                <input type="hidden" id="id_mant_detalle" name="id_mant_detalle" value="<?php echo $id_md ?>">
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_placa" class="small">Placa</label>
                        <input type="text" class="form-control form-control-sm" id="txt_placa" class="small" value="<?php echo $obj['placa'] ?>" readonly="readonly">
                    </div>  
                    <div class="form-group col-md-9">
                        <label for="txt_nom_art" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                    </div>  
                    <div class="form-group col-md-3">
                        <label for="txt_nom_art" class="small">No. Serial</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['num_serial'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_est_gen" class="small">Estado General</label>
                        <input type="text" class="form-control form-control-sm" id="txt_est_gen" class="small" value="<?php echo $obj['estado_general'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_observacio_mant" class="small">Observación del Mantenimiento</label>                   
                        <textarea class="form-control" id="txt_observacio_mant" name="txt_observacio_mant" rows="4"><?php echo $obj['observacion_mant'] ?></textarea>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_estado_general" class="small" required>Estado Fin Mantenimiento</label>
                        <select class="form-control form-control-sm" id="sl_estado_general" name="sl_estado_general">
                            <?php estado_general_activo('', $obj['estado_fin_mant']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_observacio_fin_mant" class="small">Observación de la finalización del Mantenimiento</label>                   
                        <textarea class="form-control" id="txt_observacio_fin_mant" name="txt_observacio_fin_mant" rows="4"><?php echo $obj['observacion_fin_mant'] ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_detalle" <?php echo $editar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_finalizar_detalle" <?php echo $editar ?>>Finalizar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir">Imprimir</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
