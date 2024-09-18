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
$sql = "SELECT * FROM far_presentacion_comercial WHERE id_prescom=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if(empty($obj)){
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++):
        $col = $rs->getColumnMeta($i);
        $name=$col['name'];
        $obj[$name]=NULL;
    endfor;    
    //Inicializa variable por defecto
    $obj['cantidad'] = 1;
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR PRESENTACION COMERCIALE</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_prescomerciales">
                <input type="hidden" id="id_prescomercial" name="id_prescomercial" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-10">
                        <label for="txt_nom_prescomercial" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_prescomercial" name="txt_nom_prescomercial" required value="<?php echo $obj['nom_presentacion'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_cantidad" class="small">Cantidad</label>
                        <input type="number" class="form-control form-control-sm numberint" id="txt_cantidad" name="txt_cantidad" value="<?php echo $obj['cantidad'] ?>">
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