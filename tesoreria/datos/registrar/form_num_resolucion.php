<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = $_POST['id'];
$id_vigencia = $_SESSION['id_vigencia'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_resol`,`consecutivo`
            FROM `ips_mun`.`tes_resolucion_pago`
            WHERE `id_ctb_doc` = $id AND `id_vigencia` = $id_vigencia";
    $rs = $cmd->query($sql);
    $resolucion = $rs->fetch(PDO::FETCH_ASSOC);
    $id_resol = $resolucion['id_resol'];
    $consecutivo = $resolucion['consecutivo'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">RESOLUCIÓN DE PAGOS</h5>
        </div>
        <form id="formConsecResol" class="px-3">
            <input type="hidden" name="id_ctb_doc" id="id_ctb_doc" value="<?php echo $id; ?>">
            <input type="hidden" name="id_resol" id="id_resol" value="<?php echo $id_resol; ?>">
            <div class="form-row px-12 pt-2">
                <div class="form-group col-md-12">
                    <label for="numResolucion" class="small">Consecutivo</label>
                    <input type="number" name="numResolucion" id="numResolucion" class="form-control form-control-sm" value="<?= $consecutivo; ?>" required>
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" onclick="guardarConsecutivoResolucion(this)">Guardar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>