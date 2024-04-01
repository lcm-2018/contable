<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$error = "Debe diligenciar este campo";
$id_cpto = $_POST['id_ejec'];
//Obtener fecha del sistema
$fecha = date('Y-m-d');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT * FROM pto_tipo_recurso WHERE id_pto_tipo=1 ORDER BY nombre_tipo ASC";
    $rs = $cmd->query($sql);
    $tiporecurso = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVO CDP</h5>
        </div>
        <form id="formAddEjecutaPresupuesto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="fecha" class="small">FECHA CDP</label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" value="<?php echo $fecha; ?>" onchange="buscarConsecutivo('CDP');">
                    <input type="hidden" name="id_pto" id="id_pto" value="<?php echo $_POST['id_ejec']; ?>">
                </div>
                   <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-6">
                    <label for="numCdp" class="small">NUMERO CDP</label>
                    <input type="text" name="numCdp" id="numCdp" class="form-control form-control-sm">
                </div>
                
            </div>
            <div class="form-row px-4  ">
                 <div class="form-group col-md-12">
                    <label for="Objeto" class="small">OBJETO CDP</label>
                    <textarea id="Objeto" type="text" name="Objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4"></textarea>
                </div>
                    <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">

            </div>
            <div class="form-row px-2 ">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddEjecutaPresupuesto">Agregar</button>
                    <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>
    </div>
</div>