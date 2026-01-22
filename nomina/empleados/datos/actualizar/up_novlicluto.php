<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}

include '../../../../conexion.php';
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_licluto`, `fec_inicio`, `fec_fin`, `dias_inactivo`, `dias_habiles`
            FROM
                `nom_licencia_luto`
            WHERE `id_licluto` = $id";
    $rs = $cmd->query($sql);
    $licencia = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR LICENCIA POR LUTO</h5>
        </div>
        <form id="formUpLicLuto">
            <input type="number" name="numidLicLuto" value="<?php echo $licencia['id_licluto'] ?>" hidden>
            <div class="form-row px-4 pt-2">
                <?php
                $fecinlic = $licencia['fec_inicio'];
                $fecfinlic = $licencia['fec_fin'];
                $diainac = $licencia['dias_inactivo'];
                $diahab = $licencia['dias_habiles'];
                ?>
                <div class="form-group col-md-6">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasLicLuto">
                        <?php echo $diainac ?>
                        <input type="number" id="numCantDiasLicLuto" name="numCantDiasLicLuto" value="<?php echo $diainac ?>" hidden>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabLicLuto" name="numCantDiasHabLicLuto" value="<?php echo $diahab ?>">
                    <div id="enumCantDiasHabLicLuto" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecInicioLicLuto">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioLicLuto" name="datFecInicioLicLuto" value="<?php echo $fecinlic ?>">
                        <div id="edatFecInicioLicLuto" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datFecFinLicLuto">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinLicLuto" name="datFecFinLicLuto" value="<?php echo $fecfinlic ?>">
                        <div id="edatFecFinLicLuto" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnUpLicLuto">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>