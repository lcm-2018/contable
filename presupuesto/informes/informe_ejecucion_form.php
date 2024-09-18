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
            <h5 class="card-header small">CUIPO</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-2">
                        <div class="col-2"></div>
                        <div class="col-3 small">TIPO PRESUPUESTO:</div>
                        <div class="col-3">
                            <select name="tipo_pto" id="tipo_pto" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">INGRESO</option>
                                <option value="2">GASTO</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-2"></div>
                        <div class="col-3 small">CORTE TRIMESTRE:</div>
                        <div class="col-3">
                            <select name="fecha" id="fecha" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">31 DE MARZO</option>
                                <option value="2">30 DE JUNIO</option>
                                <option value="3">30 DE SEPTIEMBRE</option>
                                <option value="4">31 DE DICIEMBRE</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-2"></div>
                        <div class="col-3 small">TIPO INFORME:</div>
                        <div class="col-3">
                            <select name="informe" id="informe" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">PROGRAMACION</option>
                                <option value="2">EJECUCIÓN</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button value="4" class="btn btn-primary" onclick="generarInforme(this);"><span></span> Consultar</button>
                        <a type="" id="btnExcelEntrada" class="btn btn-outline-success" value="01" title="Exprotar a Excel">
                            <span class="fas fa-file-excel fa-lg" aria-hidden="true"></span>
                        </a>
                        <a type="button" class="btn btn-danger" title="Imprimir" onclick="imprSelecTes('areaImprimir',<?php echo 0; ?>);"><span class="fas fa-print fa-lg" aria-hidden="true"></span></a>
                        <a type="" id="btnPlanoEntrada" class="btn btn-outline-warning" value="01" title="Exprotar archivo plano separado por Tabulaciones">
                            <span class="fas fa-file-export fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </form>
            </div>
            <br>
            <div id="areaImprimir" class="table-responsive px-2">
            </div>
        </div>
    </div>
</div>