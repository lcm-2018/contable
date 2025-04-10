<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';

$data = isset($_POST['data']) ? $_POST['data'] : exit('Acceso no disponible');
$data = explode('|', base64_decode($data));
$id = $data[0];
$tipo = $data[1];
$table = $tipo == 'cdp' ? 'pto_cdp' : 'pto_crp';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_manu` FROM $table WHERE `id_pto_$tipo` = $id";
    $rs = $cmd->query($sql);
    $numero = $rs->fetch();
    $consecutivo = $numero['id_manu'];
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formAnulaDoc">
        <input type="hidden" id="id_pto_doc" name="id_pto_doc" value="<?php echo $id; ?>">
        <input type="hidden" id="tipo" name="tipo" value="<?php echo $tipo; ?>">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h5 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;ANULACIÓN DE DOCUMENTO PRESUPUESTAL</h5>
            </div>
            <div class="pt-3 px-3">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="numDoc" class="small">NUMERO</label>
                        <input type="text" id="numDoc" name="numDoc" class="form-control form-control-sm" value="<?php echo $consecutivo; ?>" readonly>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecha" class="small">FECHA</label>
                        <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo date('Y-m-d') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label for="numDoc" class="small">CONCEPTO</label>
                        <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="changeEstadoAnulacion()">Anular</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>