<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';

$vigencia = $_SESSION['vigencia'];
// concateno la fecha con el año vigencia
$fecha_max = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-12-31'));
$fecha_min = date("Y-m-d", strtotime($_SESSION['vigencia'] . '-01-01'));
$fecha = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha_actual = $fecha->format('Y-m-d');
// obtengo la lista de municipio asociados a las sedes de la empresa
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
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
    $sql = "SELECT `id_doc_fuente`,`cod`,`nombre` FROM `ctb_fuente` ORDER BY `nombre`";
    $rs = $cmd->query($sql);
    $documentos = $rs->fetchAll();
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-md-12 ">
        <div class="card">
            <h5 class="card-header small">Informe libro auxiliar </h5>
            <div class="card-body">
                <form>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">CUENTA INICIAL:</div>
                        <div class="col-md-3">
                            <input type="text" name="codigoctaini" id="codigoctaini" class="form-control form-control-sm" value="">
                            <input type="hidden" name="id_codigoctaini" id="id_codigoctaini" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">CUENTA FINAL:</div>
                        <div class="col-md-3">
                            <input type="text" name="codigoctafin" id="codigoctafin" class="form-control form-control-sm" value="">
                            <input type="hidden" name="id_codigoctafin" id="id_codigoctafin" class="form-control form-control-sm" value="0">

                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Fecha de inicial:</div>
                        <div class="col-md-3"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Fecha de corte:</div>
                        <div class="col-md-3"><input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Tipo Documento:</div>
                        <div class="col-md-3">
                            <select name="slcTpDoc" id="slcTpDoc" class="form-control form-control-sm">
                                <option value="0">--Seleccione--</option>
                                <?php
                                foreach ($documentos as $row) {
                                    echo '<option value="' . $row['id_doc_fuente'] . '">' . $row['cod'] . ' -> ' . $row['nombre'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Tercero</div>
                        <div class="col-md-3">
                            <input type="text" id="bTercero" class="form-control form-control-sm">
                            <input type="hidden" id="id_tercero" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center pt-3">
                                <button class="btn btn-primary" onclick="generarInformeCtb(this)" value="9"><span></span>Libro auxiliar</button>
                                <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                                    <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                                </a>
                                <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
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