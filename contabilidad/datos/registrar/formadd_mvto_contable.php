<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
include '../../../financiero/consultas.php';


$id_ctb_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acceso no permitido');
$id_documento = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : 0;
$id_vigencia = $_SESSION['id_vigencia'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$fecha_cierre = fechaCierre($_SESSION['vigencia'], 55, $cmd);
try {
    $sql = "SELECT
                MAX(`ctb_doc`.`id_manu`) AS `id_manu`, `ctb_fuente`.`nombre`
            FROM
                `ctb_doc`
                INNER JOIN `ctb_fuente` 
                    ON (`ctb_doc`.`id_tipo_doc` = `ctb_fuente`.`id_doc_fuente`)
            WHERE (`ctb_doc`.`id_tipo_doc` = $id_ctb_doc AND `ctb_doc`.`id_vigencia` = $id_vigencia)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $id_manu = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $fuente = !empty($consecutivo) ? $consecutivo['nombre'] : '---';
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_ctb_doc`, `id_manu`, `id_tercero`, `fecha`, `detalle`
            FROM
                `ctb_doc`
            WHERE (`id_ctb_doc` = $id_documento)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date("Y-m-d");
// Estabelcer fecha minima con vigencia
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
if (empty($datos)) {
    $datos['id_ctb_doc'] = 0;
    $datos['id_manu'] = $id_manu;
    $datos['id_tercero'] = 0;
    $datos['fecha'] = $fecha;
    $datos['detalle'] = '';
    $tercero = '';
} else {
    if ($datos['id_tercero'] > 0) {
        $payload = array(0 => $datos['id_tercero']);
        $ids = implode(',', $payload);
        $terceros = getTerceros($ids, $cmd);
        $tercero = ltrim($terceros[0]['nom_tercero']);
    } else {
        $tercero = '---';
    }
}
$cmd = null;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN DOCUMENTOS: <b><?php echo $fuente; ?></b></h5>
        </div>
        <form id="formGetMvtoCtb">
            <input type="hidden" name="id_ctb_doc" value="<?php echo $id_ctb_doc; ?>">
            <input type="hidden" id="fec_cierre" value="<?php echo $fecha_cierre; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="fecha" class="small">FECHA </label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d', strtotime($datos['fecha'])); ?>" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="numDoc" class="small">NUMERO</label>
                    <input type="number" name="numDoc" id="numDoc" class="form-control form-control-sm" value="<?php echo $datos['id_manu'] ?>">
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="terceromov" class="small">TERCERO</label>
                    <input type="text" name="terceromov" id="terceromov" class="form-control form-control-sm" value="<?php echo $tercero ?>">
                    <input type="hidden" name="id_tercero" id="id_tercero" class="form-control form-control-sm" value="<?php echo $datos['id_tercero'] ?>">
                </div>

            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="objeto" class="small">OBJETO CRP</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4" required><?php echo $datos['detalle'] ?></textarea>
                </div>

            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="gestionarMvtoCtb" text="<?php echo $id_documento ?>"><?php echo $id_documento == 0 ? 'Registrar' : 'Actualizar'; ?></button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>