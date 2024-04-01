<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT *
            FROM
                nom_licenciasmp
            WHERE id_licmp = '$id'";
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
            <h5 style="color: white;">ACTUALIZAR LICENCIA</h5>
        </div>
        <form id="formUpLicencia">
            <input type="number" name="numidLicencia" value="<?php echo $licencia['id_licmp'] ?>" hidden>
            <div class="form-row px-4">
                <?php
                $fecinlic = $licencia['fec_inicio'];
                $fecfinlic = $licencia['fec_fin'];
                $licact = $licencia['id_licmp'];
                $diainac = $licencia['dias_inactivo'];
                $diahab = $licencia['dias_habiles'];
                ?>
                <div class="form-group col-md-6">
                    <label class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divUpCantDiasLic">
                        <?php echo $diainac ?>
                        <input type="number" id="numUpCantDiasLic" name="numUpCantDiasLic" value="<?php echo $diainac ?>" hidden>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="numUpCantDiasHabLic">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numUpCantDiasHabLic" name="numUpCantDiasHabLic" value="<?php echo $diahab ?>">
                    <div id="enumUpCantDiasHabLic" class="invalid-tooltip">
                        Debe ser mayor a 0 y menor o igual a Dias inactivo
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecInicioLic">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecInicioLic" name="datUpFecInicioLic" value="<?php echo $fecinlic ?>">
                        <div id="edatUpFecInicioLic" class="invalid-tooltip">
                            Inicio debe ser menor
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="datUpFecFinLic">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datUpFecFinLic" name="datUpFecFinLic" value="<?php echo $fecfinlic ?>">
                        <div id="edatUpFecFinLic" class="invalid-tooltip">
                            Fin debe ser mayor
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center mb-3">
                    <button class="btn btn-primary btn-sm actualizarLic">Actualizar</button>
                    <a class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>