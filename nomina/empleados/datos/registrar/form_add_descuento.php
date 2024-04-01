<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR DESCUENTO(OTRO)</h5>
        </div>
        <form id="formAddOtroDcto">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label class="small" for="datFecDcto">Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="datFecDcto" name="datFecDcto" value="<?php echo date('Y-m-d') ?>">
                </div>
                <div class="form-group col-md-6">
                    <label class="small" for="numValDcto">Valor</label>
                    <input type="number" class="form-control form-control-sm" id="numValDcto" name="numValDcto">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label class="small" for="datFecInicioIncap">Concepto por el que se hace el descuento</label>
                    <textarea class="form-control form-control-sm" id="txtConDcto" name="txtConDcto" rows="3"></textarea>
                </div>
            </div>
            <div class="form-row px-4">
                <div class="text-center pb-3">
                    <button class="btn btn-primary btn-sm" id="btnAddOtroDcto">Agregar</button>
                    <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
                </div>
            </div>
        </form>

    </div>
</div>