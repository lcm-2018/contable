<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
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
        `seg_ctb_retenciones`.`id_retencion`
        ,`seg_ctb_retenciones`.`nombre_retencion`
        FROM
        `seg_ctb_retenciones`
        INNER JOIN `seg_ctb_retencion_tipo` 
            ON (`seg_ctb_retenciones`.`id_retencion_tipo` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
        WHERE (`seg_ctb_retencion_tipo`.`id_retencion_tipo` =6);";
$rs = $cmd->query($sql);
$otras = $rs->fetchAll();
?>

<div class="row justify-content-center">
    <div class="col-sm-10 ">
        <div class="card">
            <h5 class="card-header small">Informe libro auxiliar </h5>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">CUENTA INICIAL:</div>
                        <div class="col-6">
                            <input type="text" name="codigocta_ini" id="codigocta_ini" class="form-control form-control-sm" value="" required>
                            <input type="hidden" name="id_codigoctaini" id="id_codigoctaini" class="form-control form-control-sm" value="">
                            <input type="hidden" name="tipo_sede" id="tipo_sede" class="form-control form-control-sm" value="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">CUENTA FINAL:</div>
                        <div class="col-6">
                            <input type="text" name="codigocta_fin" id="codigocta_fin" class="form-control form-control-sm" value="" required>
                            <input type="hidden" name="id_codigoctafin" id="id_codigoctafin" class="form-control form-control-sm" value="">

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
                        <div class="col-3"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>

                    <div class="px-50">&nbsp; </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <a type="button" class="btn btn-primary btn-sm" onclick="generarInformeCtb(9);"> Libro auxiliar</a>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>