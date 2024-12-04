<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : exit('Acceso no disponible');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_pto_cdp`, `id_manu`, `fecha`, `objeto`, `num_solicitud`
            FROM
                `pto_cdp`
            WHERE `id_pto_cdp` = $id_cdp";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CDP</h5>
        </div>
        <form id="formUpCDP">
            <input type="hidden" name="id_cdp" value="<?php echo $id_cdp ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="id_manu" class="small">CONSECUTIVO CDP</label>
                    <input type="number" name="id_manu" id="id_manu" class="form-control form-control-sm" value="<?php echo $datos['id_manu'] ?>" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label for="dateFecha" class="small">FECHA</label>
                    <input type="date" name="dateFecha" id="dateFecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datos['fecha'])) ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numSolicitud" class="small">NÚMERO SOLICITUD</label>
                    <input type="text" id="numSolicitud" name="numSolicitud" class="form-control form-control-sm py-0 sm" aria-label="Default select example" value="<?php echo $datos['num_solicitud'] ?>">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">OBJETO</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"><?php echo $datos['objeto'] ?></textarea>
                </div>
            </div>
            <div class="text-right pb-3 px-4">
                <button class="btn btn-primary btn-sm" id="btnGestionCDP" text="2">Actualizar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>