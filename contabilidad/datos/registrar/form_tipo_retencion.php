<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_tipo = isset($_POST['id_tipo']) ? $_POST['id_tipo'] : exit('Acceso no permitido');
$id_tipo = $id_tipo != '0' ? base64_decode($id_tipo) : 0;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_retencion_tipo`.`id_retencion_tipo`
                , `ctb_retencion_tipo`.`tipo`
                , `ctb_retencion_tipo`.`id_tercero`
                , `tb_terceros`.`nom_tercero`
                , `tb_terceros`.`nit_tercero`
            FROM
                `ctb_retencion_tipo`
                LEFT JOIN `tb_terceros` 
                    ON (`ctb_retencion_tipo`.`id_tercero` = `tb_terceros`.`id_tercero_api`)
            WHERE `ctb_retencion_tipo`.`id_retencion_tipo` = $id_tipo";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch(PDO::FETCH_ASSOC);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (empty($datos)) {
    $datos = [
        'id_retencion_tipo' => 0,
        'tipo' => '',
        'id_tercero' => 0,
        'nom_tercero' => '',
    ];
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN TIPO DE RETENCIONES</b></h5>
        </div>
        <form id="formGestTpRet">
            <input type="hidden" name="tipo_retencion" value="<?php echo $id_tipo; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtTipoRte" class="small">Tipo Retención </label>
                    <input type="text" name="txtTipoRte" id="txtTipoRte" class="form-control form-control-sm" value="<?= $datos['tipo']; ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="SeaTercer">Responsable</label>
                    <input type="text" class="form-control form-control-sm" id="SeaTercer" value="<?= $datos['nom_tercero'] != '' ? $datos['nom_tercero'] . ' -> ' . $datos['nit_tercero'] : '' ?>">
                    <input type="hidden" id="id_tercero" name="id_tercero" value="<?= $datos['id_tercero'] ?>">
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="btnGuardaTpRte">Guardar</button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>