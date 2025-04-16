<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$tipo = $_POST['tipo'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">RANGO A IMPRIMIR</h5>
        </div>
        <form id="formRangoImp" class="px-3">
            <input type="hidden" name="tipo_dc" id="tipo_dc" value="<?php echo $tipo; ?>">
            <div class="form-row px-12 pt-2">
                <div class="form-group col-md-12">
                    <label for="docInicia" class="small">No. Inicia</label>
                    <input type="number" name="docInicia" id="docInicia" class="form-control form-control-sm" placeholder="Rango Inicia">
                </div>
            </div>
            <div class="form-row px-12">
                <div class="form-group col-md-12">
                    <label for="docTermina" class="small">No. Termina</label>
                    <input type="number" name="docTermina" id="docTermina" class="form-control form-control-sm" placeholder="Rango Termina">
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" onclick="imprimirFormatoDoc(0)">Imprimir</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>