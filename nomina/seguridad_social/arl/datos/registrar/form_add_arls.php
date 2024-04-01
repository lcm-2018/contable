<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR ARL</h5>
        </div>
        <form id="formAddArl">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="txtNitArl">NIT</label>
                    <input type="text" class="form-control form-control-sm" id="txtNitArl" name="txtNitArl" placeholder="Sin dígito de verificación">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="txtNomArl">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="txtNomArl" name="txtNomArl" placeholder="Nombre ARL">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="txtTelArl">Teléfono</label>
                    <input type="text" class="form-control form-control-sm" id="txtTelArl" name="txtTelArl" placeholder="celular o fijo">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="maileps">Correo eléctronico</label>
                    <input type="email" class="form-control form-control-sm" id="mailArl" name="mailArl" placeholder="arl@correoarl.com">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddArls">Registrar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>