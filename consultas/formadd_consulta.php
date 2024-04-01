<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR CONSULTA</h5>
        </div>
        <form id="formAddConsulta">
            <div class="form-row px-4 pt-2">
                <div class="form-group col-md-6">
                    <label for="txtNombreConsulta" class="small">Nombre/descripción</label>
                    <input type="text" class="form-control form-control-sm" id="txtNombreConsulta" name="txtNombreConsulta">
                </div>
                <div class="form-group col-md-6">
                    <label for="jsonParam" class="small">Parámetros</label>
                    <input type="text" class="form-control form-control-sm" id="jsonParam" name="jsonParam">
                </div>
            </div>
            <div class="form-row px-4">
                <div class="form-group col-md-12">
                    <label for="txtConsultaSQL" class="small">consulta SQL</label>
                    <textarea class="form-control form-control-sm" id="txtConsultaSQL" name="txtConsultaSQL"></textarea>
                </div>
            </div>
            <div class="text-center">
                <button class="btn btn-primary btn-sm" id="btnAddConsulta">Agregar</button>
                <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal"> Cancelar</a>
            </div>
            <br>
        </form>
    </div>
</div>