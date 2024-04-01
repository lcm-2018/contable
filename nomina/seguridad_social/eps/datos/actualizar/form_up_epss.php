<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$ideps = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_eps, nombre_eps, nit, telefono, correo FROM nom_epss WHERE id_eps = '$ideps'";
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
            <h5 style="color: white;">ACTUALIZAR EPS</h5>
        </div>
        <form id="formUpEps">
            <input type="number" name="numIdEps" value="<?php echo $ideps ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="txtNitUpEps">NIT</label>
                    <input type="text" class="form-control form-control-sm" id="txtNitUpEps" name="txtNitUpEps" value="<?php echo $obj['nit'] ?>" placeholder="Sin dígito de verificación">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="txtNomUpEps">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="txtNomUpEps" name="txtNomUpEps" value="<?php echo $obj['nombre_eps'] ?>" placeholder="Nombre EPS">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="txtTelUpEps">Teléfono</label>
                    <input type="text" class="form-control form-control-sm" id="txtTelUpEps" name="txtTelUpEps" value="<?php echo $obj['telefono'] ?>" placeholder="celular o fijo">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="mailUpeps">Correo eléctronico</label>
                    <input type="email" class="form-control form-control-sm" id="mailUpeps" name="mailUpeps" value="<?php echo $obj['correo'] ?>" placeholder="eps@correoeps.com">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarEPSs"> Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>