<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include('../../../../conexion.php');
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida.');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_intv`,`valor`
            FROM `nom_intereses_vivienda`
            WHERE `id_intv` = $id";
    $rs = $cmd->query($sql);
    $vivienda = $rs->fetch(PDO::FETCH_ASSOC);
    if (empty($vivienda)) {
        $vivienda = [
            'id_intv' => 0,
            'valor' => ''
        ];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">GESTIONAR INTERÉS DE VIVIENDA</h5>
        </div>
        <form id="formIntVivienda">
            <input type="hidden" id="idIntViv" name="idIntViv" value="<?= $vivienda['id_intv'] ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label class="small" for="valIntViv">valor</label>
                    <input type="number" class="form-control form-control-sm text-right" id="valIntViv" name="valIntViv" value="<?= $vivienda['valor'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" onclick="btnGuardaIntVivienda()">Guardar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>