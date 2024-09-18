<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../terceros.php';
$id_c = isset($_POST['id_c']) ? $_POST['id_c'] : 0;
$id_ter = $_POST['tercero'];
$id_adquisicion = $_POST['id_adquisicion'];
$cmd = null;
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DESIGNAR SUPERVISOR DE CONTRATO</h5>
        </div>
        <form id="formDesingSupervisor">
            <input type="hidden" name="id_con_final" value="<?php echo $id_c ?>">
            <input type="hidden" name="id_ter_sup" value="<?php echo $id_ter ?>">
            <input type="hidden" name="id_adquisicion" value="<?php echo $id_adquisicion ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="datFecDesigSup" class="small">FECHA DESIGNACÓN</label>
                    <input type="date" name="datFecDesigSup" id="datFecDesigSup" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-6">
                    <label for="numMemorando" class="small">Número Memorando</label>
                    <input type="number" name="numMemorando" id="numMemorando" class="form-control form-control-sm">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="txtaObservaciones" class="small">OBSERVACIONES</label>
                    <textarea name="txtaObservaciones" id="txtaObservaciones" class="form-control form-control-sm" rows="3"></textarea>
                </div>
            </div>
            <div class="text-center pb-3">
                <button class="btn btn-primary btn-sm" id="btnDesigSupervisor">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
        </form>
    </div>
</div>