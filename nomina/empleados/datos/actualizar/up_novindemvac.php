<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT 
                `id_indemniza`, `fec_inica`, `fec_fin`, `cant_dias`
            FROM
                `nom_indemniza_vac`
            WHERE `id_indemniza` = '$id'";
    $rs = $cmd->query($sql);
    $indemnizacion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR LICENCIA NO REMUNERADA</h5>
        </div>
        <form id="formUpIndemVacac">
            <input type="number" name="numidLicenciaNR" value="<?php echo $indemnizacion['id_indemniza'] ?>" hidden>
            <div class="form-row px-4">
                <?php
                $fecinlic = $indemnizacion['fec_inica'];
                $fecfinlic = $indemnizacion['fec_fin'];
                $licact = $indemnizacion['id_indemniza'];
                $diainac = $indemnizacion['cant_dias'];
                ?>
                <div class="form-group col-md-12">
                    <label class="small">Días</label>
                    <input type="number" id="numUpCantDiasLicNR" class="form-control form-control-sm" name="numUpCantDiasLicNR" value="<?php echo $diainac ?>">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecInicioLicNR">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioLicNR" name="datUpFecInicioLicNR" value="<?php echo $fecinlic ?>">
                        <div id="edatUpFecInicioLicNR" class="invalid-tooltip">
                            Inicio debe ser menor
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecFinLicNR">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinLicNR" name="datUpFecFinLicNR" value="<?php echo $fecfinlic ?>">
                        <div id="edatUpFecFinLicNR" class="invalid-tooltip">
                            Fin debe ser mayor
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarIndemVac">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>