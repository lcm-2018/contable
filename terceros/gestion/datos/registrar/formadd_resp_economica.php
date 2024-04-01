<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$idT = isset($_POST['idt']) ? $_POST['idt'] : exit('Acción no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR RESPONSABILIDAD ECONÓMICA DE TERCERO</h5>
        </div>
        <form id="formAddRespEcon">
            <input type="number" id="idTercero" name="idTercero" value="<?php echo $idT ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="buscarRespEcono" class="small">RESPONSABILIDAD ECONÓMICA</label>
                    <input type="text" class="form-control form-control-sm" id="buscarRespEcono">
                    <input type="hidden" id="slcRespEcon" name="slcRespEcon" value="0">
                </div>
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddRespEcon">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
        </form>
    </div>
</div>