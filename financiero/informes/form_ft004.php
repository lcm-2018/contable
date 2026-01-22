<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
?>

<div class="row justify-content-center">
    <div class="col-sm-12 ">
        <div class="card">
            <h5 class="card-header small">FT004 - CUENTAS POR PAGAR</h5>
            <div class="card-body">
                <form>
                    <div class="row mb-3">
                        <div class="col-3"></div>
                        <div class="col-2 small">FECHA CORTE:</div>
                        <div class="col-3">
                            <input type="date" name="periodo" id="periodo" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="text-center">
                        <button value="5" class="btn btn-primary" onclick="InformeFinanciero(this);"><span></span> Consultar</button>
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