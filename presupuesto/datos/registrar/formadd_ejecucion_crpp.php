<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$error = "Debe diligenciar este campo";

$data = file_get_contents("php://input");
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    // consulta select tipo de recursos
    $sql = "SELECT id_pto_doc,id_pto_presupuestos,objeto,fecha FROM pto_documento WHERE id_pto_doc=$data";
    $rs = $cmd->query($sql);
    $datoscdp = $rs->fetch(PDO::FETCH_ASSOC);
    // Buscar el ultimo max  id_manu de la tabla pto_documento
    $sql = "SELECT max(id_manu) as id_manu FROM pto_documento WHERE tipo_doc='CRP'";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch(PDO::FETCH_ASSOC);
    $numero = $consecutivo['id_manu'] + 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$fecha = date('Y-m-d', strtotime($datoscdp['fecha']));

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">CREAR NUEVO CRP</h5>
        </div>
        <form id="formAddCrpp">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="fecha" class="small">FECHA CRP</label>
                    <input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha; ?>" value="<?php echo $fecha; ?>">
                    <input type="hidden" name="id_pto" id="id_pto" value="<?php echo $datoscdp['id_pto_presupuestos']; ?>">
                    <input type="hidden" name="id_doc" id="id_doc" value="<?php echo $datoscdp['id_pto_doc']; ?>">
                    <input type="hidden" name="id_pto_doc" id="id_pto_doc" value="">

                </div>
                <input type="hidden" name="datFecVigencia" value="<?php echo $_SESSION['vigencia'] ?>">
                <div class="form-group col-md-6">
                    <label for="numCdp" class="small">NUMERO CRP</label>
                    <input type="number" name="numCrp" id="numCrp" class="form-control form-control-sm" required value="<?php echo $numero; ?>">
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="Objeto" class="small">TERCERO</label>
                    <input type="text" name="tercerocrp" id="tercerocrp" class="form-control form-control-sm" value="">
                    <input type="hidden" name="id_tercero" id="id_tercero" class="form-control form-control-sm" value="">
                </div>

            </div>
            <div class="form-row px-4  ">
                <div class="form-group col-md-12">
                    <label for="Objeto" class="small">OBJETO CRP</label>
                    <textarea id="objeto" type="text" name="objeto" class="form-control form-control-sm py-0 sm" aria-label="Default select example" rows="4" required><?php echo $datoscdp['objeto'] ?></textarea>
                </div>

            </div>
            <div class="form-row px-2 ">
                <div class="text-center pb-3">
                    <!--a class="btn btn-primary btn-sm" style="width: 5rem;" id="registrarcrpp">Registrar</!--a-->
                    <button type="submit" class="btn btn-primary btn-sm" style="width: 5rem;" id="registrarcrpp">Aceptar</button>
                    <a type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Cancelar</a>
                </div>
        </form>
    </div>
</div>