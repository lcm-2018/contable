<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../../index.php");</script>';
    exit();
}
include '../../../../../conexion.php';
$idafp = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT id_afp, nit_afp, dig_verf, nombre_afp, telefono, correo FROM nom_afp WHERE id_afp = '$idafp'";
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
            <h5 style="color: white;">ACTUALIZAR AFP</h5>
        </div>
        <form id="formUpAfp">
            <input type="number" name="numIdAfp" value="<?php echo $idafp ?>" hidden="true">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="txtNitUpAfp">NIT</label>
                    <input type="text" class="form-control form-control-sm" id="txtNitUpAfp" name="txtNitUpAfp" value="<?php echo $obj['nit_afp'] ?>" placeholder="Sin dígito de verificación">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="txtNomUpAfp">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="txtNomUpAfp" name="txtNomUpAfp" value="<?php echo $obj['nombre_afp'] ?>" placeholder="Nombre EPS">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="txtTelUpAfp">Teléfono</label>
                    <input type="text" class="form-control form-control-sm" id="txtTelUpAfp" name="txtTelUpAfp" value="<?php echo $obj['telefono'] ?>" placeholder="celular o fijo">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="mailUpafp">Correo eléctronico</label>
                    <input type="email" class="form-control form-control-sm" id="mailUpafp" name="mailUpafp" value="<?php echo $obj['correo'] ?>" placeholder="afp@correoafp.com">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="mb-3">
                    <button class="btn btn-primary btn-sm" id="btnUpAfp"> Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>