<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../../conexion.php';
include '../../permisos.php';
include '../../financiero/consultas.php';

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
    <div class="col-sm-12 ">
        <div class="card">
            <h5 class="card-header small">Libros presupuestales de ejecución presupuestal</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Tipo de libro:</div>
                        <div class="col-md-3">
                            <select name="tipo_libro" id="tipo_libro" class="form-control form-control-sm">
                                <option value="0">-- Selecionar --</option>
                                <option value="1">Certificado de disponibilidad presupuestal</option>
                                <option value="2">Certificado de registro presupuestal</option>
                                <option value="3">Obligaciones presupuestales</option>
                                <option value="4">Relación de pagos</option>
                                <option value="6">Relación de compromisos y cuentas por pagar</option>
                                <option value="9">Relación de reconocimientos</option>
                                <option value="10">Relación de recaudos</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Fecha de inicial:</div>
                        <div class="col-md-3"><input type="date" name="fecha_ini" id="fecha_ini" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_min; ?>"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-3"></div>
                        <div class="col-md-3 small">Fecha de corte:</div>
                        <div class="col-md-3"><input type="date" name="fecha" id="fecha" class="form-control form-control-sm" min="<?php echo $fecha_min; ?>" max="<?php echo $fecha_max; ?>" value="<?php echo $fecha_actual; ?>"></div>
                    </div>
                    <div class="text-center">
                        <button value="1" class="btn btn-primary" onclick="generarInformeLibros(this);"><span></span> Consultar</button>
                        <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                        </a>
                        <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
                    </div>
                </form>
            </div>
            <br>
            <div id="areaImprimir" class="table-responsive px-2" style="font-size: 80%;">
            </div>
        </div>
    </div>
</div>