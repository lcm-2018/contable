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
$sql = "SELECT * FROM acf_hojavida_documentos WHERE id_documento=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

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
            <h7 style="color: white;">ADJUNTAR DOCUMENTO</h7>
        </div>
        <div class="px-2">
            <form id="frm_reg_documento">
                <input type="hidden" id="id_documento" name="id_documento" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="tipo" class="small">Tipo Documento</label>
                        <select class="form-control form-control-sm" id="tipo" name="tipo">
                            <?php tipo_documento_activo('', $obj['tipo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label for="descripcion" class="small">Descripción</label>
                        <input type="text" class="form-control form-control-sm" id="descripcion" name="descripcion" value="<?php echo $obj['descripcion'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label class="small text-left">Archivo Documento</label>
                        <div class="input-group mb-3">                             
                            <input type="label" class="form-control form-control-sm" id="archivo" name="archivo" value="<?php echo $obj['archivo'] ?>" readonly="readonly">
                            <button type="button" id="btn_ver_documento" class="btn btn-outline-primary btn-sm shadow-gb" title="Ver"> <span class="fas fa-eye"></span></button>
                        </div> 
                    </div>   
                    <div class="form-group col-md-12"> 
                        <div class="input-group mb-3"> 
                            <div class="custom-file">
                                <input type="file" class="custom-file-input form-control-sm" id="uploadDocAcf" accept=".pdf">
                                <label class="custom-file-label" for="customFile">Seleccionar archivo</label>
                            </div>
                        </div>
                    </div>    
                </div>
            </form>  
        </div>
    </div>  
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_documento">Guardar</button>
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


