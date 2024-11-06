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
$sql = "SELECT
        `ctb_retenciones`.`id_retencion`
        ,`ctb_retenciones`.`nombre_retencion`
        FROM
        `ctb_retenciones`
        INNER JOIN `ctb_retencion_tipo` 
            ON (`ctb_retenciones`.`id_retencion_tipo` = `ctb_retencion_tipo`.`id_retencion_tipo`)
        WHERE (`ctb_retencion_tipo`.`id_retencion_tipo` = 6)";
$rs = $cmd->query($sql);
$otras = $rs->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-md-12 ">
        <div class="card">
            <h5 class="card-header small">INFORME BALANCE DE PRUEBA</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-1">
                        <div class="col-3"></div>
                        <div class="col-md-2"><span class="small">Fecha de inicial:</span></div>
                        <div class="col-md-4"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-3"></div>
                        <div class="col-md-2 small">Fecha de corte:</div>
                        <div class="col-md-4"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-3"></div>
                        <div class="col-md-2 small">Por tercero: </div>
                        <div class="col-md-4"><input type="checkbox" name="xTercero" id="xTercero"></div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <button class="btn btn-primary" onclick="generarInformeCtb(this)" value="12"><span></span>Libro auxiliar</button>
                                <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                                    <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                                </a>
                                <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <br>
            <div id="areaImprimir" class="table-responsive px-2" style="font-size: 80%;">
            </div>
        </div>
    </div>
</div>