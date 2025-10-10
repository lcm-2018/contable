<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
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
            <h5 class="card-header small">EJECUCIÓN PRESUPUESTAL</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-3"></div>
                        <div class="col-2 small">PRESUPUESTO:</div>
                        <div class="col-3">
                            <select name="tp_presupuesto" id="tp_presupuesto" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">Ingresos</option>
                                <option value="2">Gastos</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-3"></div>
                        <div class="col-2 small">PERIODO DE REPORTE:</div>
                        <div class="col-3">
                            <select name="periodo" id="periodo" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="03">Marzo</option>
                                <option value="06">Junio</option>
                                <option value="09">Septiembre</option>
                                <option value="12">Diciembre</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button value="4" class="btn btn-primary" onclick="InformeFinanciero(this);"><span></span> Consultar</button>
                        <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                        </a>
                        <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir','<?php echo 0; ?>');"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
                    </div>
                </form>
            </div>
            <div id="areaImprimir" class="table-responsive px-2">
            </div>
        </div>
    </div>
</div>