<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR LICENCIA NO REMUNERADA</h5>
        </div>
        <form id="formAddLicenciaNR">
            <input type="number" id="idEmpLicNR" name="idEmpLicNR" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasLicNR">
                        0
                        <input type="number" id="numCantDiasLicNR" name="numCantDiasLicNR" value="0" hidden>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabLicNR" name="numCantDiasHabLicNR" value="0">
                    <div id="enumCantDiasHabLicNR" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioLicNR">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioLicNR" name="datFecInicioLicNR" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioLicNR" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinLicNR">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinLicNR" name="datFecFinLicNR">
                        <div id="edatFecFinLicNR" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddLicNR">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>