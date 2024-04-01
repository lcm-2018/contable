<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id_sp = isset($_POST['id']) ? $_POST['id'] : exit('Accion no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ENVIAR ACTA DESIGNACIÓN SUPERVISIÓN</h5>
        </div>
        <form id="formEnviarDesigSuperv">
            <input type="hidden" id="id_supervision" value="<?php echo $id_sp ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="fileSup" class="small">SELECIONAR UN ARCHIVO</label>
                    <input type="file" class="form-control-file border" name="fileSup" id="fileSup">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnSubirDesigSuperv">Enviar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>