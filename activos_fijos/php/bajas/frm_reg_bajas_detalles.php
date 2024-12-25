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

$id_acf = isset($_POST['id_acf']) ? $_POST['id_acf'] : -1;
$id = isset($_POST['id']) ? $_POST['id'] : -1;

$sql = "SELECT acf_baja_detalle.*,
            acf_hojavida.placa,far_medicamentos.nom_medicamento AS nom_articulo
        FROM acf_baja_detalle
        INNER JOIN acf_hojavida ON (acf_hojavida.id_activo_fijo=acf_baja_detalle.id_activo_fijo)
        INNER JOIN far_medicamentos ON (far_medicamentos.id_med=acf_hojavida.id_articulo)
        WHERE acf_baja_detalle.id_baja_detalle=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;

    $activofijo = datos_activo_fijo($cmd, $id_acf);
    $obj['id_activo_fijo'] = $activofijo['id_activo_fijo'];
    $obj['placa'] = $activofijo['placa'];
    $obj['nom_articulo'] = $activofijo['nom_articulo'];
    $obj['estado_general'] = $activofijo['estado_general'];    
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR DETALLE EN ORDEN DE BAJA</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Detalle-->
            <form id="frm_reg_bajas_detalles">
                <input type="hidden" id="id_detalle" name="id_detalle" value="<?php echo $id ?>">                
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_placa" class="small">Placa</label>
                        <input type="text" class="form-control form-control-sm" id="txt_placa" class="small" value="<?php echo $obj['placa'] ?>" readonly="readonly">
                        <input type="text" id="id_txt_actfij" name="id_txt_actfij" value="<?php echo $obj['id_activo_fijo'] ?>">
                        <input type="text" id="txt_est_general" name="txt_est_general" value="<?php echo $obj['estado_general'] ?>">
                    </div>  
                    <div class="form-group col-md-9">
                        <label for="txt_nom_art" class="small">Articulo</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" class="small" value="<?php echo $obj['nom_articulo'] ?>" readonly="readonly">
                    </div>  
                    <div class="form-group col-md-12">
                        <label for="txt_observacion" class="small">Observación del baja</label>                   
                        <textarea class="form-control" id="txt_observacion" name="txt_observacion" rows="4"><?php echo $obj['observacion'] ?></textarea>
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
