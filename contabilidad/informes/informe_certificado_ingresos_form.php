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
            <h5 class="card-header small">Certificado de ingresos y retenciones</h5>
            <div class="card-body">
                <form>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">tercero:</div>
                        <div class="col-5">
                            <input type="text" name="tercero_cert" id="tercero_cert" class="form-control form-control-sm" value="" required>
                            <input type="hidden" name="id_tercero_cert" id="id_tercero_cert" value="">

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de inicial:</div>
                        <div class="col-5"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col-2"></div>
                        <div class="col-3 small">Fecha de corte:</div>
                        <div class="col-5"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>

                    <div class="row">
                        <div class="col-5"></div>
                        <div class="col-4 small-font"><input class="form-check-input" type="checkbox" id="retefuente" name="retefuente" checked>Retención en la fuente</div>
                    </div>
                    <div class="row">
                        <div class="col-5"></div>
                        <div class="col-4 small-font"><input class="form-check-input" type="checkbox" id="reteiva" name="reteiva" checked>Retención en la fuente de IVA</div>
                    </div>
                    <div class="row">
                        <div class="col-5"></div>
                        <div class="col-4 small-font"><input class="form-check-input" type="checkbox" id="reteica" name="reteica" checked>Retención en Industria y Comercio</div>
                    </div>

                    <div class="row">
                        <div class="col-5"></div>
                        <div class="col-4 small-font"><input class="form-check-input" type="checkbox" id="retestampillas" name="retestampillas" checked>Retención Estampillas</div>
                    </div>
                    <div class="row">
                        <div class="col-5"></div>
                        <div class="col-4 small-font"><input class="form-check-input" type="checkbox" id="reteotras" name="reteotras" checked>Otras retenciones</div>
                    </div>

                    <div class="px-50">&nbsp; </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <a type="button" class="btn btn-primary btn-sm" onclick="imprimirCertificadoIngresos();">Generar reporte</a>
                            </div>
                        </div>
                    </div>
            </div>
            </form>
        </div>
    </div>
</div>
</div>