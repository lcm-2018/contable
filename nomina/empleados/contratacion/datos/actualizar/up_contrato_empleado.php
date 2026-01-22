<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../../conexion.php';
$id = isset($_POST['idupce']) ? explode('-', $_POST['idupce']) : exit('Acción no permitida');
$id_contrato = $id[0];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_contratos_empleados WHERE id_contrato_emp = '$id_contrato'";
    $rs = $cmd->query($sql);
    $contrato = $rs->fetch();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">ACTUALIZAR CONTRATO DE EMPLEADO</h5>
        </div>
        <form id="formActContratoEmpleado">
            <input type="hidden" name="tip_ce" id="tip_ce" value="<?php echo $id[1] ?>">
            <input type="hidden" name="id_ce" value="<?php echo $id_contrato ?>">
            <input type="hidden" name="id_emp" value="<?php echo $contrato['id_empleado'] ?>">
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label for="datFecInicio" class="small">FECHA INICIO</label>
                    <input type="date" name="datFecInicio" id="datFecInicio" class="form-control form-control-sm" value="<?php echo $contrato['fec_inicio'] ?>">
                </div>
                <div class="form-group col-md-6">
                    <label for="datFecFin" class="small">FECHA TERMINACIÓN</label>
                    <input type="date" name="datFecFin" id="datFecFin" class="form-control form-control-sm" value="<?php echo $contrato['fec_fin'] ?>">
                </div>
            </div>
            <!--<div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="datFecInicio" class="small">SALARIO</label>
                    <input type="number" name="numSalario" id="numSalario" class="form-control form-control-sm">
                </div>
            </div>-->
            <div class="form-row px-4 pt-2">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnActContratoEmpleado">Actualizar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>