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
            <h5 class="card-header small">CONTRALORÍA SIA</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-3"></div>
                        <div class="col-2 small">PERIODO:</div>
                        <div class="col-3">
                            <select name="periodo" id="periodo" class="form-control form-control-sm">
                                <option value="0">--Seleccionar--</option>
                                <option value="1">SEMESTRE I</option>
                                <option value="2">SEMESTRE II</option>
                                <option value="3">ANUAL</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center">
                        <button value="1" class="btn btn-primary" onclick="InformeFinanciero(this);"><span></span> Consultar</button>
                    </div>
                </form>
            </div>
            <div id="areaImprimir" class="table-responsive px-2">
            </div>
        </div>
    </div>
</div>