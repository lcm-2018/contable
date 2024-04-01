<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}

include '../../../../conexion.php';
$idemp  = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT genero FROM nom_empleado WHERE id_empleado = '$idemp'";
    $rs = $cmd->query($sql);
    $obj = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR LICENCIA MATERNA/PATERNA</h5>
        </div>
        <form id="formAddLicencia">
            <input type="number" id="idEmpLicencia" name="idEmpLicencia" value="<?php echo $idemp ?>" hidden>
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" class="small">Tipo</label>
                    <div class="form-control form-control-sm" id="divTipLic">
                        <?php if ($obj['genero'] === 'F') {
                            echo  'MATERNA';
                            $tipl = '1';
                        } else {
                            echo 'PATERNA';
                            $tipl = '0';
                        } ?>
                        <input type="txt" id="txtTipoLic" name="txtTipoLic" value="<?php echo $tipl ?>" hidden>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" class="small">Días inactivo</label>
                    <div class="form-control form-control-sm" id="divCantDiasLic">
                        0
                        <input type="number" id="numCantDiasLic" name="numCantDiasLic" value="0" hidden>
                    </div>
                </div>
                <div class="form-group col-md-3">
                    <label class="small" class="small">Días hábiles</label>
                    <input type="number" class="form-control form-control-sm" id="numCantDiasHabLic" name="numCantDiasHabLic" value="0">
                    <div id="enumCantDiasHabLic" class="invalid-tooltip">
                        <?php echo 'Debe ser mayor a 0 y menor o igual a Dias inactivo' ?>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label class="small" class="small" for="datFecInicioLic">Fecha Inicio</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecInicioLic" name="datFecInicioLic" value="<?php echo date('Y-m-d') ?>">
                        <div id="edatFecInicioLic" class="invalid-tooltip">
                            <?php echo 'Inicio debe ser menor' ?>
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6">
                    <label class="small" class="small" for="datFecFinLic">Fecha Fin</label>
                    <div class="form-group">
                        <input type="date" class="form-control form-control-sm" id="datFecFinLic" name="datFecFinLic">
                        <div id="edatFecFinLic" class="invalid-tooltip">
                            <?php echo 'Fin debe ser mayor' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddLicencia">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>