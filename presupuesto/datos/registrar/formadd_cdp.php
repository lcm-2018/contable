<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$id_pto = isset($_POST['id_pto']) ? $_POST['id_pto'] : exit('Acceso no disponible');

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 54, $cmd);
try {
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_cdp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

try {
    $sql = "SELECT * FROM `pto_tipo` ORDER BY `nombre` ASC";
    $rs = $cmd->query($sql);
    $modalidad = $rs->fetchAll();
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
        <form id="formAddCDP">
            <input type="hidden" name="id_pto" value="<?php echo $id_pto ?>">
            <input type="hidden" id="fec_cierre" value="<?php echo $fecha_cierre ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="id_manu" class="small">CONSECUTIVO CDP</label>
                    <input type="number" name="id_manu" id="id_manu" class="form-control form-control-sm" value="<?php echo $id_manu ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="dateFecha" class="small">FECHA</label>
                    <input type="date" name="dateFecha" id="dateFecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="numSolicitud" class="small">NÚMERO SOLICITUD</label>
                    <input type="text" id="numSolicitud" name="numSolicitud" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">OBJETO</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"></textarea>
                </div>
            </div>
            <div class="text-right pb-3 px-4">
                <button class="btn btn-success btn-sm" id="btnGestionCDP" text="1">Agregar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>