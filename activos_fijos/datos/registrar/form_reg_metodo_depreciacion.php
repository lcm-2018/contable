<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_serie = isset($_POST['id_ser']) ? $_POST['id_ser'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_metodo`,`descripcion` FROM `seg_metodo_deprecia`";
    $rs = $cmd->query($sql);
    $metodo = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR MÉTODO DE DEPRECIACIÓN DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formRegMetDepreciaAcFijo">
                <input name="id_serie_acfijo" hidden value="<?php echo $id_serie ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="metodoDeprecia" class="small">Método de Depreciación</label>
                        <select name="metodoDeprecia" id="metodoDeprecia" class="form-control form-control-sm">
                            <option value="0">--Seleccione--</option>
                            <?php foreach ($metodo as $m) { ?>
                                <option value="<?php echo $m['id_metodo'] ?>"><?php echo $m['descripcion'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecIniDeprecia" class="small">Fecha Inicia Depreciación</label>
                        <input type="date" id="fecIniDeprecia" name="fecIniDeprecia" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="numMesesDeprecia" class="small">Vida útil (meses)</label>
                        <input type="number" id="numMesesDeprecia" name="numMesesDeprecia" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numValResidual" class="small">Valor Residual</label>
                        <input type="number" id="numValResidual" name="numValResidual" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numCapacProd" class="small">Capacidad de producción</label>
                        <input type="number" id="numCapacProd" name="numCapacProd" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaDeprecia" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaDeprecia" name="txtObservaDeprecia" rows="3"></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnRegMetDepreciaActFijo" type="button" class="btn btn-primary btn-sm">Registrar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>