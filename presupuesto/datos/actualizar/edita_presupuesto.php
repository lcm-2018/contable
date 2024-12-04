<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$error = "Debe diligenciar este campo";
$id = $_POST['idtbs'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_tipo` AS `id_pto_tipo`
                , `nombre`
                , `descripcion`
                , `id_pto`
            FROM
                `pto_presupuestos`
            WHERE (`id_pto` = $id)";
    $rs = $cmd->query($sql);
    $presupuesto = $rs->fetch();
    // Consulto tipo de presupuesto
    $sql = "SELECT
                `id_tipo` AS `id_pto_tipo`
                , `nombre`
            FROM
                `pto_tipo`
            ORDER BY `nombre` ASC";
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
            <h5 style="color: white;">EDITAR PRESUPUESTO</h5>
        </div>
        <form id="formUpdatePresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-8">
                    <label for="nomPto" class="small">NOMBRE PRESUPUESTO</label>
                    <input type="text" name="nomPto" id="nomPto" class="form-control form-control-sm" value="<?php echo $presupuesto['nombre'] ?>">
                </div>
                <input type="hidden" id="id" name="id" value="<?php echo $id ?>">
                <div class="form-group col-md-4">
                    <label for="tipoPto" class="small">TIPO DE PRESUPUESTO</label>
                    <select id="tipoPto" name="tipoPto" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($modalidad as $mo) {
                            if ($mo['id_pto_tipo'] == $presupuesto['id_pto_tipo']) {
                                echo '<option value="' . $mo['id_pto_tipo'] . '" selected>' . mb_strtoupper($mo['nombre']) . '</option>';
                            } else {
                                echo '<option value="' . $mo['id_pto_tipo'] . '">' . mb_strtoupper($mo['nombre']) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="txtObjeto" class="small">DESCRIPCIÓN</label>
                    <textarea id="txtObjeto" type="text" name="txtObjeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3"><?php echo $presupuesto['descripcion'] ?></textarea>
                </div>
            </div>
            <div class="text-right px-4 pb-3">
                <button class="btn btn-success btn-sm" id="btnUpdatePresupuesto">Actualizar</button>
                <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>