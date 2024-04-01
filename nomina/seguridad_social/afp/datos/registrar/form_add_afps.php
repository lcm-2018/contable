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
            <h5 style="color: white;">REGISTRAR AFP</h5>
        </div>
        <form id="formAddAfp">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="txtNitAfp">NIT</label>
                    <input type="text" class="form-control form-control-sm" id="txtNitAfp" name="txtNitAfp" placeholder="Sin dígito de verificación">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="txtNomAfp">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="txtNomAfp" name="txtNomAfp" placeholder="Nombre AFP">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="txtTelAfp">Teléfono</label>
                    <input type="text" class="form-control form-control-sm" id="txtTelAfp" name="txtTelAfp" placeholder="celular o fijo">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="maileps">Correo eléctronico</label>
                    <input type="email" class="form-control form-control-sm" id="mailAfp" name="mailAfp" placeholder="afp@correoafp.com">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddAfp">Registrar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>