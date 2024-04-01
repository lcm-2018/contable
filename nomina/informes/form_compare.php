<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<?php include '../../head.php';
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">COMPARE PLANILLA vs SISTEMA</h5>
        </div>
        <form id="" enctype="multipart/form-data">
            <input type="hidden" id="id_nomina" value="<?php echo $id ?>">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-12">
                    <label for="filePlanilla" class="small">DOCUMENTO</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
                    <input type="file" class="form-control-file border" name="filePlanilla" id="filePlanilla">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" id="btnComparePlanilla">Comparar</button>
                <a href="../datos/soporte/formato_planilla.php" class="btn btn-warning btn-sm" title="Descargar formato de cargue para planilla"><i class="fas fa-download"></i> Formato</a>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>