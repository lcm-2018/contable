<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_mmto = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_mmto`, `num_orden`, `fec_inicia`, `fec_termina`, `tipo`, `concpeto`, `val_deterioro`, `observaciones`
            FROM
                `seg_mantenimiento_acfijo`
            WHERE  `id_mmto` = '$id_mmto'";
    $rs = $cmd->query($sql);
    $mmto = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR O MODIFICAR MANTENIMINETO DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formUpMantenimientoAcFijo">
                <input name="id_mmto" hidden value="<?php echo $id_mmto ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="txtNoOrdenMmto" class="small"># orden</label>
                        <input type="text" name="txtNoOrdenMmto" id="txtNoOrdenMmto" class="form-control form-control-sm" value="<?php echo $mmto['num_orden'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecIniciaMmto" class="small">Fecha de Ingreso</label>
                        <input type="date" name="fecIniciaMmto" id="fecIniciaMmto" class="form-control form-control-sm" value="<?php echo $mmto['fec_inicia'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecFinMmto" class="small">Fecha de retorno</label>
                        <input type="date" name="fecFinMmto" id="fecFinMmto" class="form-control form-control-sm" value="<?php echo $mmto['fec_termina'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="slcTipoMmto" class="small">Tipo de mantenimiento</label>
                        <select id="slcTipoMmto" name="slcTipoMmto" class="form-control form-control-sm">
                            <?php
                            if ($mmto['tipo'] == '1') {
                                echo '<option value="1" selected>PREVENTIVO</option>';
                                echo '<option value="2">CORRECTIVO</option>';
                            } else {
                                echo '<option value="1">PREVENTIVO</option>';
                                echo '<option value="2" selected>CORRECTIVO</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txtConcptoMmto" class="small">concepto</label>
                        <input type="text" id="txtConcptoMmto" name="txtConcptoMmto" class="form-control form-control-sm" value="<?php echo $mmto['concpeto'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numValDeterioro" class="small">Valor deterioro</label>
                        <input type="number" id="numValDeterioro" name="numValDeterioro" class="form-control form-control-sm" value="<?php echo $mmto['val_deterioro'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaMmto" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaMmto" name="txtObservaMmto" rows="3"><?php echo $mmto['observaciones'] ?></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnUpMmtoActFijo" type="button" class="btn btn-primary btn-sm">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>