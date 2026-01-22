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
            <h5 style="color: white;">REGISTRAR VACACIONES</h5>
        </div>
        <form id="formAddVacaciones">
            <input type="number" id="idEmpVacacion" name="idEmpVacacion" value="<?php echo $idemp ?>" hidden>
            <!--<div class="form-row p-4">
                <div class="alert alert-warning" role="alert">
                    <?php //echo 'Días para calcular vaciones con corte a ' . $fec_corte . ': <br><b>' . $tot_dias['total_dias'] . '<b>' 
                    ?>
                </div>
            </div>-->
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="fecCorteVac" class="small">Fecha Corte</label>
                    <input type="date" class="form-control form-control-sm" id="fecCorteVac" name="fecCorteVac">
                </div>
                <div class="form-group col-md-8">
                    <label class="small">Total Dias Calcular</label>
                    <input type="number" class="form-control form-control-sm" id="numDiasToCalc" name="numDiasToCalc">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="slcVacAnticip">Anticipadas</label>
                    <div class="form-group">
                        <select id="slcVacAnticip" name="slcVacAnticip" class="form-control form-control-sm py-0" aria-label="Default select example">
                            <option selected value="0">--Selecionar--</option>
                            <option value="1">Si</option>
                            <option value="2">No</option>
                        </select>
                        <div id="eslcVacAnticip" class="invalid-tooltip">
                            <?php echo 'Selecionar una opción' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasVac">
                        0
                        <input type="number" id="numCantDiasVac" name="numCantDiasVac" value="0" hidden>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabVac" name="numCantDiasHabVac" value="0">
                    <div id="enumCantDiasHabVac" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioVac">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioVac" name="datFecInicioVac" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioVac" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinVac">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinVac" name="datFecFinVac">
                        <div id="edatFecFinVac" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddVacacion">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>