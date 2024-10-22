<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no permitido');
$id = $id != '0' ? base64_decode($id) : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_retencion_rango`.`id_rango`
                , `ctb_retenciones`.`id_retencion`
                , CONCAT_WS(' -> ',`ctb_retencion_tipo`.`tipo`
                , `ctb_retenciones`.`nombre_retencion`) AS `retencion`
                , `ctb_retencion_rango`.`valor_base`
                , `ctb_retencion_rango`.`valor_tope`
                , `ctb_retencion_rango`.`tarifa`
                , `ctb_retencion_rango`.`estado`
            FROM
                `ctb_retencion_rango`
                INNER JOIN `ctb_retenciones` 
                    ON (`ctb_retencion_rango`.`id_retencion` = `ctb_retenciones`.`id_retencion`)
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
            WHERE (`ctb_retencion_rango`.`id_rango` = $id)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_retencion_tipo`,`tipo`
            FROM `ctb_retencion_tipo`
            WHERE `estado` = 1 ORDER BY `tipo` ASC";
    $rs = $cmd->query($sql);
    $tipos = $rs->fetchAll(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($datos)) {
    $datos = [
        'id_retencion' => 0,
        'retencion' => '',
        'valor_base' => 0,
        'valor_tope' => 0,
        'tarifa' => 0,
    ];
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN TIPO DE RETENCIONES</b></h5>
        </div>
        <form id="formGestRango">
            <input type="hidden" name="id_rango" value="<?php echo $id; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="buscaRetencion" class="small">Retención</label>
                    <input type="text" class="form-control form-control-sm" id="buscaRetencion" value="<?= $datos['retencion']; ?>">
                    <input type="hidden" name="id_retencion" id="id_retencion" value="<?= $datos['id_retencion']; ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-4">
                    <label for="valor_base" class="small">Valor Base</label>
                    <input type="number" class="form-control form-control-sm text-right" id="valor_base" name="valor_base" value="<?= $datos['valor_base']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="valor_tope" class="small">Valor Tope</label>
                    <input type="number" class="form-control form-control-sm text-right" id="valor_tope" name="valor_tope" value="<?= $datos['valor_tope']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="tarifa" class="small">Tarifa</label>
                    <input type="number" class="form-control form-control-sm text-right" id="tarifa" name="tarifa" value="<?= $datos['tarifa']; ?>">
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="btnGuardaRango">Guardar</button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>