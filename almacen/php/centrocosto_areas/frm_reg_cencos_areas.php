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
$sql = "SELECT far_centrocosto_area.*,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_responsable
        FROM far_centrocosto_area 
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_centrocosto_area.id_responsable) 
        WHERE far_centrocosto_area.id_area=" . $id . " LIMIT 1";
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
    $obj['id_centrocosto'] = 0;
    $obj['id_tipo_area'] = 0;
    $obj['id_responsable'] = 0;
    $obj['id_sede'] = 0;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR AREA DE CENTRO DE COSTO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_cencos_areas">
                <input type="hidden" id="id_area" name="id_area" value="<?php echo $id ?>">
                <div class=" form-row">                   
                    <div class="form-group col-md-5">
                        <label for="txt_nom_area" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_area" name="txt_nom_area" value="<?php echo $obj['nom_area'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_centrocosto" class="small">Centro Costo</label>
                        <select class="form-control form-control-sm" id="sl_centrocosto" name="sl_centrocosto">
                            <?php centros_costo($cmd, '', $obj['id_centrocosto']) ?>
                        </select>
                    </div>  
                    <div class="form-group col-md-3">
                        <label for="sl_tipo_area" class="small">Tipo Area</label>
                        <select class="form-control form-control-sm" id="sl_tipo_area" name="sl_tipo_area">
                            <?php tipo_area($cmd, '', $obj['id_tipo_area']) ?>
                        </select>
                    </div>                  
                    <div class="form-group col-md-4">
                        <label for="txt_responsable" class="small">Responsable</label>
                        <input type="text" class="form-control form-control-sm" id="txt_responsable" value="<?php echo $obj['usr_responsable'] ?>">
                        <input type="hidden" id="id_txt_responsable" name="id_txt_responsable" value="<?php echo $obj['id_responsable'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_sede" class="small">Sede</label>
                        <select class="form-control form-control-sm" id="sl_sede" name="sl_sede">
                            <?php sedes($cmd, '', $obj['id_sede']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_bodega" class="small">Bodega</label>
                        <select class="form-control form-control-sm" id="sl_bodega" name="sl_bodega">
                            <?php bodegas_sede($cmd, '', $obj['id_sede'], $obj['id_bodega']) ?>   
                        </select>
                    </div>
                </div>    
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>