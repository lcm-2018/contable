<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT far_orden_egreso_tipo_cta.*,
            CONCAT_WS(' - ',ctb_pgcp.cuenta,ctb_pgcp.nombre) AS cuenta
        FROM far_orden_egreso_tipo_cta
        INNER JOIN ctb_pgcp ON (ctb_pgcp.id_pgcp=far_orden_egreso_tipo_cta.id_cuenta)
        WHERE far_orden_egreso_tipo_cta.id_tipo_egreso_cta=" . $id . " LIMIT 1";
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
            <h7 style="color: white;">REGISRTAR CUENTA CONTABLE DE TIPO DE ORDENE DE EGRESO - CONSUMO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Cuenta-->
            <form id="frm_reg_tipos_orden_egreso_cta">
                <input type="hidden" id="id_tipo_egreso_cta" name="id_tipo_egreso_cta" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_cta_con" class="small">Cuenta Contable</label>
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_con" data-campoid="id_txt_cta_con" value="<?php echo $obj['cuenta'] ?>">
                        <input type="hidden" id="id_txt_cta_con" name="id_txt_cta_con" value="<?php echo $obj['id_cuenta'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_vig" class="small">Fecha Inicio de Vigencia</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_vig" name="txt_fec_vig" value="<?php echo $obj['fecha_vigencia'] ?>">
                    </div> 
                    <div class="form-group col-md-2">
                        <label for="sl_estado_cta" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado_cta" name="sl_estado_cta">
                            <?php estados_registros('',$obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_cta">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
