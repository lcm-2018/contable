<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_ctb_ref = isset($_POST['id_ctb_ref']) ? $_POST['id_ctb_ref'] : exit('Acceso no permitido');
$id_doc_ref = $_POST['id_doc_ref'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `ctb_referencia`.`id_ctb_referencia`
                , `ctb_referencia`.`id_cuenta`
                ,`ctb_referencia`.`nombre`
                , `ctb_referencia`.`accion`
                , `ctb_referencia`.`estado`
                , CONCAT(`ctb_pgcp`.`nombre`, ' -> ',`ctb_pgcp`.`cuenta`) AS `nom_cuenta`
                , 'D' AS `tipo`
            FROM `ctb_referencia`
                INNER JOIN `ctb_pgcp` 
                    ON (`ctb_referencia`.`id_cuenta` = `ctb_pgcp`.`id_pgcp`)
            WHERE `ctb_referencia`.`id_ctb_referencia` = $id_ctb_ref";
    $rs = $cmd->query($sql);
    $referencias = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($referencias)) {
        $referencias = [
            'id_ctb_referencia' => 0,
            'id_cuenta' => 0,
            'nombre' => '',
            'accion' => 2,
            'estado' => 1,
            'nom_cuenta' => '',
            'tipo' => 'M'
        ];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REFERENCIA</b></h5>
        </div>
        <form id="formRefDr">
            <input type="hidden" name="id_doc_ref" id="id_doc_ref" value="<?php echo $id_doc_ref; ?>">
            <input type="hidden" name="id_ctb_ref" id="id_ctb_ref" value="<?php echo $id_ctb_ref; ?>">
            <div class="form-row px-4 pt-3">
                <div class="form-group col-md-8">
                    <label for="nombre" class="small">Nombre</label>
                    <input type="text" class="form-control form-control-sm" id="nombre" name="nombre" value="<?php echo $referencias['nombre']; ?>">
                </div>
                <div class="form-group col-md-4">
                    <label for="accion" class="small">acción</label>
                    <select class="form-control form-control-sm" id="accion" name="accion">
                        <option value="2">--Seleccione--</option>
                        <option value="1" <?php echo $referencias['accion'] == 1 ? 'selected' : ''; ?>>INGRESO</option>
                        <option value="0" <?php echo $referencias['accion'] == 0 ? 'selected' : ''; ?>>GASTO</option>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pb-3">
                <div class="form-group col-md-12">
                    <label for="BusCta" class="small">Cuenta</label>
                    <input type="text" name="codigoCta" id="codigoCta" class="form-control form-control-sm" value="<?php echo $referencias['nom_cuenta']; ?>">
                    <input type="hidden" name="id_codigoCta" id="id_codigoCta" value="<?php echo $referencias['id_cuenta']; ?>">
                    <input type="hidden" name="tipoDato" id="tipoDato" value="<?php echo $referencias['tipo']; ?>">
                </div>
            </div>
        </form>
        <div class="text-right pb-3 px-4 w-100">
            <button type="button" class="btn btn-primary btn-sm" onclick="GuardarReferenciaDr()">Guardar</button>
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
        </div>
    </div>
</div>