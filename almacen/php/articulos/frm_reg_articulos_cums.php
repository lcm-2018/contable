<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT far_medicamento_cum.*,
            far_laboratorios.nom_laboratorio,far_presentacion_comercial.nom_presentacion    
        FROM far_medicamento_cum
        INNER JOIN far_laboratorios ON (far_laboratorios.id_lab=far_medicamento_cum.id_lab)
        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom=far_medicamento_cum.id_prescom)
        WHERE id_cum=" . $id . " LIMIT 1";
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
    $obj['estado'] = 1;
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR CUM/EXPEDIENTE DE ARTICULO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de CUM-->
            <form id="frm_reg_articulos_cums">
                <input type="hidden" id="id_cum" name="id_cum" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_cod_cum" class="small">CUM/Expediente</label>
                        <input type="text" class="form-control form-control-sm valcode" id="txt_cod_cum" name="txt_cod_cum" required value="<?php echo $obj['cum'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_cod_ium" class="small">IUM</label>
                        <input type="number" class="form-control form-control-sm number" id="txt_cod_ium" name="txt_cod_ium" value="<?php echo $obj['ium'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txt_lab_cum" class="small">Laboratorio</label>
                        <input type="text" class="form-control form-control-sm" id="txt_lab_cum" required value="<?php echo $obj['nom_laboratorio'] ?>">
                        <input type="hidden" id="id_txt_lab_cum" name="id_txt_lab_cum" value="<?php echo $obj['id_lab'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_reg_inv" class="small">Registro Invima</label>
                        <input type="text" class="form-control form-control-sm valcode" id="txt_reg_inv" name="txt_reg_inv" required value="<?php echo $obj['reg_invima'] ?>">
                    </div>  
                    <div class="form-group col-md-3">
                        <label for="txt_fec_ven_inv" class="small">Vencimiento Invima</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_ven_inv" name="txt_fec_ven_inv" required value="<?php echo $obj['fec_invima'] ?>">
                    </div> 
                    <div class="form-group col-md-4">
                        <label for="sl_estado_inv" class="small">Estado Invima</label>
                        <select class="form-control form-control-sm" id="sl_estado_inv" name="sl_estado_inv">
                            <?php estados_invima('', $obj['estado_invima']) ?>
                        </select>
                    </div>               
                    <div class="form-group col-md-6">
                        <label for="txt_precom_cum" class="small">Presentación Comercial</label>
                        <input type="text" class="form-control form-control-sm" id="txt_precom_cum" required value="<?php echo $obj['nom_presentacion'] ?>">
                        <input type="hidden" id="id_txt_precom_cum" name="id_txt_precom_cum" value="<?php echo $obj['id_prescom'] ?>">
                    </div>                       
                    <div class="form-group col-md-3">
                        <label for="sl_estado_cum" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado_cum" name="sl_estado_cum">
                            <?php estados_registros('', $obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_cum">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
