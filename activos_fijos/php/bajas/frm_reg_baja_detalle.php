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

$id_art = isset($_POST['idart']) ? $_POST['idart'] : -1;
$id = isset($_POST['id_baja_detalle']) ? $_POST['id_baja_detalle'] : -1;
$id_baja = isset($_POST['id_baja']) ? $_POST['id_baja'] : -1;
$sql = "SELECT 
            BD.id_baja_detalle,
            BD.id_baja,
            m.nom_medicamento articulo,
            HV.placa,
            HV.id_activo_fijo id_activofijo,
            CONCAT(HV.placa,' (',M.nom_medicamento,')') as nombre_activofijo,
            BD.observacion_baja
        FROM acf_baja_detalle BD
            INNER JOIN acf_hojavida HV ON HV.id_activo_fijo = BD.id_activo_fijo
            INNER JOIN far_medicamentos M ON M.id_med = HV.id_articulo
        WHERE BD.id_baja_detalle=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if ($obj === false) {
    $obj = array(); 
}

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DETALLE EN ORDEN DE BAJA</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_baja_detalle">
                <input type="hidden" id="id_baja_detalle" name="id_baja_detalle" value="<?php echo $id ?>">
                <input type="hidden" id="id_baja" name="id_baja" value="<?php echo $id_baja ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                    <label for="txt_activo_fijo" class="small">Activo Fijo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_activo_fijo" required value="<?php echo $obj['nombre_activofijo'] ?>">
                        <input type="hidden" id="id_txt_activo_fijo" name="id_txt_activo_fijo" value="<?php echo $obj['id_activofijo'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="observacion_baja" class="small">Observación Mantenimiento</label>
                        <input type="text" class="form-control form-control-sm" id="observacion_baja" name="observacion_baja" value="<?php echo $obj['observacion_baja'] ?>">
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
