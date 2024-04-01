<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CARGAR PRESUPUESTO EN EXCEL</h5>
        </div>
        <form id="formAddDocsTercero" enctype="multipart/form-data">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="fileDocHoEx" class="small">DOCUMENTO</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" class="form-control-file border" name="file" id="file">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" id="btnAddPtoExcel">Subir</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>