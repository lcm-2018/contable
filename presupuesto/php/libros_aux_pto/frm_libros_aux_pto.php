<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}

include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color:rgb(27, 104, 159) !important;">
            <h5 style="color: white;">LIBROS AUXILIARES DE PRESUPUESTO</h5>
        </div>
        <div class="px-2">
            <form id="frm_libros_aux_presupuesto">
                <div class=" form-row">
                    <div class="form-group col-md-6">
                        <label for="txt_fecini" class="small">Fecha inicial</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecini" name="txt_fecini" placeholder="Fecha Inicial" value="<?php echo $_SESSION['vigencia'] ?>-01-01">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txt_fecfin" class="small">Fecha final</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecfin" name="txt_fecfin" placeholder="Fecha final" value="<?php echo $_SESSION['vigencia'] ?>-12-31">
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_tipo_doc" class="small">Rubro presupuestal</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_tipo_doc" name="txt_tipo_doc" placeholder="Digite el rubro presupuestal" autocomplete="off">
                        <input type="hidden" id="id_cargue" name="id_cargue" class="form-control form-control-sm">
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="sl_doc_fuente" class="small">Documento fuente</label>
                        <select class="filtro form-control form-control-sm" id="sl_doc_fuente" name="sl_doc_fuente">
                            <option value="0">--Seleccione--</option>
                            <option value="1">CDP - Certificado de Disponibilidad Presupuestal</option>
                            <option value="2">CRP - Certificado de Registro Presupuestal</option>
                            <option value="3">COP - Certificado de Obligacion Presupuestal</option>
                            <option value="4">PAG - Pagos</option>
                        </select>
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-5">
                        <label class="small">&nbsp;</label>
                    </div>
                    <div class="form-group col-md-1">
                        <a type="button" id="btn_consultar" class="btn btn-outline-primary btn-sm" title="Consultar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div class="form-group col-md-1">
                        <a type="button" class="btn btn-outline-dark btn-sm" data-dismiss="modal">
                            <span class="fas fa-window-close fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript" src="js/libros_aux_pto/libros_aux_pto.js?v=<?php echo date('YmdHis') ?>"></script>
<script type="text/javascript" src="js/informes/common.js?v=<?php echo date('YmdHis') ?>"></script>