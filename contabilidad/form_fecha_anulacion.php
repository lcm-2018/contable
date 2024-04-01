<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';

$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$id_pto_doc = $_POST['id'];
// Estabelcer zona horaria bogota
date_default_timezone_set('America/Bogota');
// insertar fecha actual
$fecha = date("Y-m-d");
// consultar la fecha de cierre del periodo del módulo de presupuesto 
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT fecha_cierre FROM tb_fin_periodos WHERE id_modulo=3";
    $rs = $cmd->query($sql);
    $fecha_cierre = $rs->fetch();
    $fecha_cierre = $fecha_cierre['fecha_cierre'];
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre));
    // incrementar un dia a $fecha cierre
    $fecha_cierre = date('Y-m-d', strtotime($fecha_cierre . '+1 day'));
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// buscar el ultimo id y sumar 1 del campo id_pto_anula de la tabla pto_anula
try {
    $sql = "SELECT max(id_manu_anula) as id_manu_anula from seg_ctb_anula where id_modulo=3";
    $rs = $cmd->query($sql);
    $row = $rs->fetch();
    $id_manu_anula = $row['id_manu_anula'] + 1;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<div class="px-0">
    <form id="formAnulacionCtb">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;ANULACION DE DOCUMENTO CONTABILIDAD</h5>
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">NUMERO: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <input type="text" id="numero" class="form-control form-control-sm" name="numero" value="<?php echo $id_manu_anula; ?>" readonly>
                            <input type="text" id="id_pto_doc" name="id_pto_doc" value="<?php echo $id_pto_doc; ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">FECHA: </label></div>
                    </div>
                    <div class="col-4">
                        <div class="col">
                            <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" required value="<?php echo $fecha; ?>" min="<?php echo $fecha_cierre; ?>" max="<?php echo $fecha_max; ?>">
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="col"><label for="numDoc" class="small">CONCEPTO: </label></div>
                    </div>
                    <div class="col-8">
                        <div class="col">
                            <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="3" required="required"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-12">
                    </div>
                </div>



            </div>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-primary btn-sm" onclick="changeEstadoAnulaCtb()">Anular</button>
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>