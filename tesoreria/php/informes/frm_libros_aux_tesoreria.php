<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LIBROS AUXILIARES DE TESORERIA</h5>
        </div>
        <div class="px-2">
            <form id="frm_libros_aux_tesoreria">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="sl_tipo_libro" class="small">Tipo de libro</label>
                        <select class="filtro form-control form-control-sm" id="sl_tipo_libro" name="sl_tipo_libro">
                            <option value="1">Relación de obligaciones por pagar (Causación)</option>
                            <option value="2">Relación de comprobantes de egreso generados</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fecini" class="small">Fecha inicial</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecini" name="txt_fecini" placeholder="Fecha Inicial" value="<?php echo $_SESSION['vigencia'] ?>-01-01">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fecfin" class="small">Fecha final</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecfin" name="txt_fecfin" placeholder="Fecha final" value="<?php echo $_SESSION['vigencia'] ?>-12-31">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_consultar" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" id="btn_consultar" class="btn btn-outline-success btn-sm" title="Consultar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_cancelar" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">
                            <span class="fas fa-window-close fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!--<script type="text/javascript" src="js/funciontesoreria.js?v=<?php echo date('YmdHis') ?>"></script>-->
<script type="text/javascript" src="js/informes/informes.js?v=<?php echo date('YmdHis') ?>"></script>
<script type="text/javascript" src="js/informes/common.js?v=<?php echo date('YmdHis') ?>"></script>