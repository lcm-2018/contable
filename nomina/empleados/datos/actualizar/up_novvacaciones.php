<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$idvac  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT
                `id_vac`, `id_empleado`, `anticipo`, `fec_inicial`, `fec_inicio`, `fec_fin`, `dias_inactivo`, `dias_habiles`, `corte`, `dias_liquidar`
            FROM
                `nom_vacaciones`
            WHERE `id_vac` = $idvac";
    $rs = $cmd->query($sql);
    $vacacion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR VACACIONES</h5>
        </div>
        <form id="formUpVacaciones">
            <input type="number" id="numidVacacion" name="numidVacacion" value="<?php echo $idvac ?>" hidden>
            <!--<div class="form-row p-4">
                <div class="alert alert-warning" role="alert">
                    <?php //echo 'Días para calcular vaciones con corte a ' . $fec_corte . ': <br><b>' . $tot_dias['total_dias'] . '<b>' 
                    ?>
                </div>
            </div>-->
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-4">
                    <label for="fecCorteVac" class="small">Fecha Corte</label>
                    <input type="date" class="form-control form-control-sm" id="fecCorteVac" name="fecCorteVac" value="<?php echo $vacacion['corte'] ?>">
                </div>
                <div class="form-group col-md-8">
                    <label class="small">Total Dias Calcular</label>
                    <input type="number" class="form-control form-control-sm" id="numDiasToCalc" name="numDiasToCalc" value="<?php echo $vacacion['dias_liquidar'] ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="slcVacAnticip">Anticipadas</label>
                    <div class="form-group">
                        <select id="slcVacAnticip" name="slcVacAnticip" class="form-control form-control-sm py-0" aria-label="Default select example">
                            <option value="1" <?php echo $vacacion['anticipo'] == 1 ? 'selected' : '' ?>>Si</option>
                            <option value="2" <?php echo $vacacion['anticipo'] == 2 ? 'selected' : '' ?>>No</option>
                        </select>
                        <div id="eslcVacAnticip" class="invalid-tooltip">
                            <?php echo 'Selecionar una opción' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasVac">
                        <?php echo $vacacion['dias_inactivo'] ?>
                        <input type="number" id="numCantDiasVac" name="numCantDiasVac" value="<?php echo $vacacion['dias_inactivo'] ?>" hidden>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabVac" name="numCantDiasHabVac" value="<?php echo $vacacion['dias_habiles'] ?>">
                    <div id="enumCantDiasHabVac" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioVac">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioVac" name="datFecInicioVac" value="<?php echo $vacacion['fec_inicial'] ?>">
                        <div id="edatFecInicioVac" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinVac">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinVac" name="datFecFinVac" value="<?php echo $vacacion['fec_fin'] ?>">
                        <div id="edatFecFinVac" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm actualizarVac">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>