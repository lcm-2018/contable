<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';


?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">HISTORICO DE PAGOS PENDIENTES A TERCEROS</h5>
        </div>
        <div class="px-2">
            <form id="frm_historicopagos">
                <div class=" form-row">
                    <div class="form-group col-md-2">
                        <span class="small">Fecha</span>
                    </div>
                    <div class="form-group col-md-4">
                        <input type="date" class="form-control form-control-sm" id="txt_fecha" name="txt_fecha" placeholder="Fecha" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <a type="button" id="btn_buscar" class="btn btn-outline-success btn-sm" title="Buscar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_terceros" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th colspan="5">&nbsp;</th>
                                <th colspan="7">Antigüedad (dias)</th>
                            </tr>
                            <tr class="text-center centro-vertical">
                                <!--<th>Id tercero</th>-->
                                <th>ID Manu</th>
                                <th>Documento/Nit</th>
                                <th style="max-width: 40%;">Tercero</th>
                                <th>Fecha credito</th>
                                <th>Credito</th>
                                <th>< 30</th>
                                <th>30 a 60</th>
                                <th>60 a 90</th>
                                <th>90 a 180</th>
                                <th>180 a 360</th>
                                <th>> 360</th>
                                <th>Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_terceros"></tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <!--<button type="button" class="btn btn-primary btn-sm" id="btn_imprimir">Imprimir</button>-->
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>


<script type="text/javascript" src="js/historico_pagos_pendientes/historico_pagos_pendientes.js?v=<?php echo date('YmdHis') ?>"></script>