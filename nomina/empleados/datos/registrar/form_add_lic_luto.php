<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR LICENCIA POR LUTO</h5>
        </div>
        <form id="formAddLicLuto">
            <input type="number" id="idEmpLicLuto" name="idEmpLicLuto" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasLicLuto">
                        0
                        <input type="number" id="numCantDiasLicLuto" name="numCantDiasLicLuto" value="0" hidden>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabLicLuto" name="numCantDiasHabLicLuto" value="0">
                    <div id="enumCantDiasHabLicLuto" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioLicLuto">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioLicLuto" name="datFecInicioLicLuto" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioLicLuto" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinLicLuto">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinLicLuto" name="datFecFinLicLuto">
                        <div id="edatFecFinLicLuto" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddLicLuto">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>