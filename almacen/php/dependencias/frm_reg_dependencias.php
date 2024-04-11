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
$sql = "SELECT id_dependencia,nom_dependencia FROM tb_dependencias WHERE id_dependencia=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DEPENDENCIA</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_dependencias">
                <input type="hidden" id="id_dependencia" name="id_dependencia" value="<?php echo $id ?>">
                <div class=" form-row">                   
                    <div class="form-group col-md-12">
                        <label for="txt_nom_dependencia" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_dependencia" name="txt_nom_dependencia" required value="<?php echo isset($obj['nom_dependencia'])?$obj['nom_dependencia']:""?>">
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