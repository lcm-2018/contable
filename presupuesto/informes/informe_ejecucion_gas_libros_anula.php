<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';
?>
<!DOCTYPE html>
<html lang="es">

<?php
$vigencia = $_SESSION['vigencia'];
// concateno la fecha con el año vigencia
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_actual = $fecha->format('Y-m-d');
// obtener fecha actual para bogota

// la paso a formato fecha


?>

<div class="row justify-content-center">
    <div class="col-sm-8 ">
        <div class="card">
            <h5 class="card-header small">Libros presupuestales anulados</h5>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Tipo de libro:</div>
                        <div class="col-3">
                            <select name="tipo_libro" id="tipo_libro" class="form-control form-control-sm">
                                <option value="0">-- Selecionar --</option>
                                <option value="7">Certificado de disponibilidad presupuestal</option>
                                <option value="8">Certificado de registro presupuestal</option>
                                <option value="11">Documentos de modificación presupuestal</option>
                                <option value="12">Obligaciones presupuestales</option>
                                <option value="13">Relación de pagos</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de inicial:</div>
                        <div class="col-3"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de corte:</div>
                        <div class="col-3"><input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>

                    <div class="px-50">&nbsp; </div>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3"></div>
                        <div class="col-3"> <a href="#" class="btn btn-primary" onclick="generarInformeLibros(1);">Consultar</a></div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>