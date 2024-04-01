<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
include '../../../../conexion.php';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_dcto`, `fecha`, `concepto`, `valor`
            FROM
                `nom_otros_descuentos`
            WHERE (`id_dcto` = $id);";
    $rs = $cmd->query($sql);
    $descuento = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR DESCUENTO(OTRO)</h5>
        </div>
        <form id="formUpOtroDcto">
            <input type="hidden" id="id_dcto" name="id_dcto" value="<?php echo $descuento['id_dcto'] ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecDcto">Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="datFecDcto" name="datFecDcto" value="<?php echo $descuento['fecha'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="numValDcto">Valor</label>
                    <input type="number" class="form-control form-control-sm" id="numValDcto" name="numValDcto" value="<?php echo $descuento['valor'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="datFecInicioIncap">Concepto por el que se hace el descuento</label>
                    <textarea class="form-control form-control-sm" id="txtConDcto" name="txtConDcto" rows="3"><?php echo $descuento['concepto'] ?></textarea>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnUpOtroDcto">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>