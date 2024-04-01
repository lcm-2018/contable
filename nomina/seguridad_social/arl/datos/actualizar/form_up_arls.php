<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$idarl = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_arl, nit_arl, nombre_arl, telefono, correo FROM nom_arl WHERE id_arl = '$idarl'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR ARL</h5>
        </div>
        <form id="formUpArl">
            <input type="number" name="numIdArl" value="<?php echo $idarl ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="txtNitUpArl">NIT</label>
                    <input type="text" class="form-control form-control-sm" id="txtNitUpArl" name="txtNitUpArl" value="<?php echo $obj['nit_arl'] ?>" placeholder="Sin dígito de verificación">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="txtNomUpArl">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="txtNomUpArl" name="txtNomUpArl" value="<?php echo $obj['nombre_arl'] ?>" placeholder="Nombre EPS">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="txtTelUpArl">Teléfono</label>
                    <input type="text" class="form-control form-control-sm" id="txtTelUpArl" name="txtTelUpArl" value="<?php echo $obj['telefono'] ?>" placeholder="celular o fijo">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="mailUparl">Correo eléctronico</label>
                    <input type="email" class="form-control form-control-sm" id="mailUparl" name="mailUparl" value="<?php echo $obj['correo'] ?>" placeholder="arl@correoarl.com">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm" id="btnUpArl"> Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>