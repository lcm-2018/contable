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
// obtengo la lista de municipio asociados a las sedes de la empresa
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
?>

<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card">
            <h5 class="card-header small">Informe de impuestos municipales aplicados</h5>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col-3 small">Entidad:</div>
                        <div class="col-3">
                            <select name="tipo_sede" id="tipo_sede" class="form-control form-control-sm">
                                <option value="680">DIAN</option>

                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col-3 small">Fecha de inicial:</div>
                        <div class="col-3"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col-3"></div>
                        <div class="col-3 small">Fecha de corte:</div>
                        <div class="col-3"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>



                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <button class="btn btn-primary" onclick="generarInformeCtb(this)" value="4"><span></span>Resumen</button>
                                <button class="btn btn-primary" onclick="generarInformeCtb(this)" value="5"><span></span>Detalle</button>
                                <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                                    <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                                </a>
                                <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>

    </div>
    <div class="col-md-9">
        <div class="row">
            <br>
            <div id="areaImprimir" class="table-responsive px-2" style="font-size: 100%;"></div>
        </div>
    </div>
</div>