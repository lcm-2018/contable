<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_dprecia = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
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
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_depreciacion`, `id_num_serie`, `id_metodo`, `fec_inicia`, `vida_util`, `valor_residual`, `capacidad_produccion`, `observacion` 
            FROM
                `seg_depreciacion`
            WHERE `id_depreciacion` = '$id_dprecia' LIMIT 1";
    $rs = $cmd->query($sql);
    $depreciacion = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">MODIFICAR O ACTUALIZAR MÉTODO DE DEPRECIACIÓN DE ACTIVO FIJO</h5>
        </div>
        <div class="px-2">
            <form id="formRegMetDepreciaAcFijo">
                <input name="id_depreciacion" hidden value="<?php echo $depreciacion['id_depreciacion'] ?>">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="metodoDeprecia" class="small">Método de Depreciación</label>
                        <select name="metodoDeprecia" id="metodoDeprecia" class="form-control form-control-sm">
                            <?php foreach ($metodo as $m) {
                                if ($m['id_metodo'] == $depreciacion['id_metodo']) {
                                    echo '<option value="' . $m['id_metodo'] . '" selected>' . $m['descripcion'] . '</option>';
                                } else {
                                    echo '<option value="' . $m['id_metodo'] . '">' . $m['descripcion'] . '</option>';
                                }
                            } ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="fecIniDeprecia" class="small">Fecha Inicia Depreciación</label>
                        <input type="date" id="fecIniDeprecia" name="fecIniDeprecia" class="form-control form-control-sm" value="<?php echo $depreciacion['fec_inicia'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="numMesesDeprecia" class="small">Vida útil (meses)</label>
                        <input type="number" id="numMesesDeprecia" name="numMesesDeprecia" class="form-control form-control-sm" value="<?php echo $depreciacion['vida_util'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numValResidual" class="small">Valor Residual</label>
                        <input type="number" id="numValResidual" name="numValResidual" class="form-control form-control-sm" value="<?php echo $depreciacion['valor_residual'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numCapacProd" class="small">Capacidad de producción</label>
                        <input type="number" id="numCapacProd" name="numCapacProd" class="form-control form-control-sm" value="<?php echo $depreciacion['capacidad_produccion'] ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaDeprecia" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaDeprecia" name="txtObservaDeprecia" rows="3"><?php echo $depreciacion['observacion'] ?></textarea>
                    </div>
                </div>
            </form>
            <div class="text-center pt-1 pb-3">
                <button id="btnUpMetDepreciaActFijo" type="button" class="btn btn-primary btn-sm">Actualizar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
            </div>
        </div>
    </div>
</div>