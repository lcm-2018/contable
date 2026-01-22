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
                `ctb_retenciones`.`id_retencion`
                , `ctb_retencion_tipo`.`id_retencion_tipo`
                , `ctb_retenciones`.`nombre_retencion`
                , CONCAT_WS(' -> ',`ctb_pgcp`.`cuenta`
                , `ctb_pgcp`.`nombre`) AS `cuenta`
                , `ctb_pgcp`.`id_pgcp`
                , `ctb_pgcp`.`tipo_dato`
                , `ctb_retenciones`.`estado`
            FROM
                `ctb_retenciones`
                INNER JOIN `ctb_retencion_tipo` 
                    ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
                LEFT JOIN `ctb_pgcp` 
                    ON (`ctb_retenciones`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE (`ctb_retenciones`.`id_retencion` = $id)";
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
        'id_retencion_tipo' => 0,
        'nombre_retencion' => '',
        'cuenta' => '',
        'id_pgcp' => 0,
        'tipo_dato' => 'M',
        'estado' => 1
    ];
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIÓN TIPO DE RETENCIONES</b></h5>
        </div>
        <form id="formGestRetencion">
            <input type="hidden" name="id_retencion" value="<?php echo $id; ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtTipoRte" class="small">Tipo Retención </label>
                    <select name="txtTipoRte" id="txtTipoRte" class="form-control form-control-sm">
                        <option value="0" <?= $datos['id_retencion_tipo'] == '0' ? 'selected' : ''; ?>>--Seleccione--</option>
                        <?php
                        if (!empty($tipos)) {
                            foreach ($tipos as $tp) {
                                $slc = $datos['id_retencion_tipo'] == $tp['id_retencion_tipo'] ? 'selected' : '';
                                echo '<option value="' . $tp['id_retencion_tipo'] . '" ' . $slc . '>' . $tp['tipo'] . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>

            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="txtNombreRte" class="small">Retención</label>
                    <input type="text" class="form-control form-control-sm" id="txtNombreRte" name="txtNombreRte" value="<?= $datos['nombre_retencion']; ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="codigoCta" class="small">Cuenta</label>
                    <input type="text" class="form-control form-control-sm" id="codigoCta" value="<?= $datos['cuenta']; ?>">
                    <input type="hidden" name="id_codigoCta" id="id_codigoCta" value="<?= $datos['id_pgcp']; ?>">
                    <input type="hidden" name="tipoDato" id="tipoDato" value="<?= $datos['tipo_dato']; ?>">
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button class="btn btn-primary btn-sm" style="width: 5rem;" id="btnGuardaRetencion">Guardar</button>
            <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
        </div>
    </div>
</div>