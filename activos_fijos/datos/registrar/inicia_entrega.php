<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$id_c = isset($_POST['ids']) ? $_POST['ids'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT 
                `id_entra_af`, `id_tercero_api`, `id_tipo_entrada`, `factura`, `acta_remision`, `fec_acta_remision`, `observacion`, `identificador`, `estado`, `vigencia`
            FROM
                `seg_entrada_activo_fijo`
            WHERE `identificador` = '$id_c' limit 1";
    $rs = $cmd->query($sql);
    $esta = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$dd = explode('|', $id_c);
if (empty($esta['id_entra_af'])) {
    $id_inaf = 0;
    $id_tercero = $dd[0];
    $factura = $remision = $observacion = $fecha =  null;
    $id_tipo_entrada = 1;
} else {
    $id_inaf = $esta['id_entra_af'];
    $id_tercero = $esta['id_tercero_api'];
    $factura = $esta['factura'];
    $remision = $esta['acta_remision'];
    $observacion = $esta['observacion'];
    $id_tipo_entrada = $esta['id_tipo_entrada'];
    $fecha = $esta['fec_acta_remision'];
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">DATOS DE ENTRADA</h5>
        </div>
        <div class="px-4">
            <form id="formRegEntraActFijoPr">
                <input type="hidden" id="id_tercero" name="id_tercero_pd" value="<?php echo $id_tercero ?>">
                <input type="hidden" id="id_inaf" name="id_inaf" value="<?php echo $id_inaf ?>">
                <input type="hidden" id="id_tipo_entrada" name="id_tipo_entrada" value="<?php echo $id_tipo_entrada ?>">
                <input type="hidden" id="identificador" name="id_c" value="<?php echo $id_c ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="numActaRem" class="small"># acta y/o remisión</label>
                        <input type="text" id="numActaRem" name="numActaRem" class="form-control form-control-sm" value="<?php echo $remision ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="numFactura" class="small"># Factura</label>
                        <input type="text" id="numFactura" name="numFactura" class="form-control form-control-sm" value="<?php echo $factura ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fecActRem" class="small">Fecha de acta y/o remisión</label>
                        <input type="date" id="fecActRem" name="fecActRem" class="form-control form-control-sm" value="<?php echo $fecha ?>">
                    </div>
                </div>
                <div class="form-row text-center">
                    <div class="form-group col-md-12">
                        <label for="txtObservaActFijo" class="small">Observaciones</label>
                        <textarea class="form-control" id="txtObservaActFijo" name="txtObservaActFijo" rows="3"><?php echo $observacion ?></textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btnRegEntraActFijoPr">Continuar <span class="fas fa-arrow-alt-circle-right fa-lg"></span></button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
    </div>
</div>