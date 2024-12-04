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

$id = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;
$sql = "SELECT imagen
        FROM acf_hojavida
        WHERE id_activo_fijo=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
?>
  
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ADJUNTAR IMAGEN DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="frm_reg_hojavida" enctype="multipart/formdata">
                <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label class="small text-left">Archivo Imagen</label>
                        <div class="input-group mb-3">                             
                            <input type="label" class="form-control form-control-sm" id="imagen" name="imagen" value="<?php echo $obj['imagen'] ?>" readonly="readonly">
                            <button type="button" id="btn_ver_imagen" class="btn btn-outline-primary btn-sm shadow-gb" title="Ver"> <span class="fas fa-eye"></span></button>
                            <button type="button" id="btn_borrar_imagen" class="btn btn-outline-primary btn-sm shadow-gb" title="Borrar"> <span class="fas fa-trash-alt"></span></button>
                        </div> 
                    </div>
                    <div class="form-group col-md-12">  
                        <div class="input-group mb-3">                             
                            <div class="custom-file">
                                <input type="file" class="custom-file-input form-control-sm" id="uploadImageAcf" accept=".jpg,.jpeg,.png">
                                <label class="custom-file-label" for="customFile">Seleccionar archivo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">        
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_imagen">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script>
    // Add the following code if you want the name of the file appear on select
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>


