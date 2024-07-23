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

$id_hv_doc = isset($_POST['id_hv_doc']) ? $_POST['id_hv_doc'] : -1;
$id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;

$sql = "SELECT
            HV.id,
            HVD.id_documento,
            hv.placa,
            HVD.tipo,
            HVD.descripcion,
            HVD.archivo,
            U.login
        FROM acf_hojavida_documentos HVD
        INNER JOIN acf_hojavida HV ON HV.id = HVD.id_activo_fijo
        INNER JOIN seg_usuarios_sistema U ON U.id_usuario = HVD.id_usuario_crea
        WHERE HVD.id_documento=" . $id_hv_doc . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if ($obj === false) {
    $obj = array(); // Inicializa $obj como un array vacío
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
            <h5 style="color: white;">ADJUNTAR DOCUMENTOS</h5>
        </div>
        <div class="px-2">
            <form id="acf_reg_docs_hoja_vida" enctype="multipart/formdata">
                <input type="hidden" id="id_hv" name="id_hv" value="<?php echo $id_hv ?>">
                <input type="hidden" id="id_hv_doc" name="id_hv_doc" value="<?php echo $id_hv_doc ?>">

                <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="estado_general" class="small">Tipo Documento</label>
                    <select class="form-control form-control-sm" id="tipo" name="tipo">
                        <?php tipo_documento_activo('--Tipo--', $obj['tipo']) ?>
                    </select>
                </div>

                <div class="form-group col-md-8">
                    <label for="periodo_garantia" class="small">Descripción</label>
                    <input type="text" class="form-control form-control-sm" id="descripcion" name="descripcion" value="<?php echo $obj['descripcion'] ?>">
                </div>

                <div class="form-group col-md-12">
                    <label for="uploadImageAcf" class="small text-left">Documento</label>
                    <div class="input-group mb-3"> 
                        <button type="button" id="btn_descargar_documento" class="btn btn-outline-primary btn-sm shadow-gb" title="Descargar"> <span class="fas fa-download"></span></button>
                        <input type="label" class="form-control form-control-sm" id="archivo" name="archivo" value="<?php echo $obj['archivo'] ?>" readonly="readonly">
                    </div> 
                    <div class="input-group mb-3"> 
                        <div class="custom-file">
                            <input type="file" class="custom-file-input form-control-sm" id="uploadDocAcf" accept=".pdf">
                            <label class="custom-file-label" for="customFile">Seleccionar documento</label>
                        </div>
                    </div>
                </div>
                <hr>
                </div>

                <div class="form-group mt-3">
                    <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_documentos">Guardar</button>
                    <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Salir</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Add the following code if you want the name of the file appear on select
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
</script>


