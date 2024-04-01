<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_ctb_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no permitido');
$id_vigencia = $_SESSION['id_vigencia'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `ctb_doc`
            WHERE (`id_vigencia` = $id_vigencia AND `id_tipo_doc` = $id_ctb_doc)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date("Y-m-d");
// Estabelcer fecha minima con vigencia
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVO DOCUMENTO </h5>
        </div>
        <form id="formGetMvtoCtb">
            <input type="hidden" name="id_ctb_doc" value="<?php echo $id_ctb_doc; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="fecha" class="small">FECHA </label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo $fecha; ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="numDoc" class="small">NUMERO</label>
                    <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" readonly value="<?php echo $id_manu ?>">
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="terceromov" class="small">TERCERO</label>
                    <input type="text" name="terceromov" id="terceromov" class="form-control form-control-sm" value="">
                    <input type="hidden" name="id_tercero" id="id_tercero" class="form-control form-control-sm" value="0">
                </div>

            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="objeto" class="small">OBJETO CRP</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4" required></textarea>
                </div>

            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="gestionarMvtoCtb" text="1">Registrar</button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>