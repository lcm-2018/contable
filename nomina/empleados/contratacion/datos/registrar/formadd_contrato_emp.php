<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../../conexion.php';
include '../../../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$error = "Debe diligenciar este campo";
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT * FROM nom_empleado WHERE estado= '1' AND tipo_contrato= '1' ORDER BY apellido1, apellido2,nombre1, nombre2 ASC";
    $rs = $cmd->query($sql);
    $empleado = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CONTRATO DE EMPLEADO</h5>
        </div>
        <form id="formAddContratoEmpleado">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="slcEmpleado" class="small">EMPLEADO</label>
                    <select id="slcEmpleado" name="slcEmpleado" class="form-control form-control-sm py-0 sm" aria-label="Default select example">
                        <option value="0">-- Seleccionar --</option>
                        <?php
                        foreach ($empleado as $e) {
                            echo '<option value="' . $e['id_empleado'] . '">' . $e['no_documento'] . ' || ' . mb_strtoupper($e['apellido1'] . ' ' . $e['apellido2'] . ' ' . $e['nombre1'] . ' ' . $e['nombre2']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-6">
                    <label for="datFecInicio" class="small">FECHA INICIO</label>
                    <input type="date" name="datFecInicio" id="datFecInicio" class="form-control form-control-sm">
                </div>
                <div class="form-group col-md-6">
                    <label for="datFecFin" class="small">FECHA TERMINACIÓN</label>
                    <input type="date" name="datFecFin" id="datFecFin" class="form-control form-control-sm">
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
                    <button class="btn btn-primary btn-sm" id="btnAddContratoEmp">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>