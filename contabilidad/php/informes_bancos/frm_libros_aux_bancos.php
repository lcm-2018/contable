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
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LIBROS AUXILIARES</h5>
        </div>
        <div class="px-2">
            <form id="frm_libros_aux_bancos">
                <div class=" form-row">
                    <div class="form-group col-md-5">
                        <label for="txt_cuentainicial" class="small">Cuenta inicial</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_cuentainicial" name="txt_cuentainicial" placeholder="Cuenta Inicial">
                        <input type="hidden" id="id_txt_cuentainicial" name="id_txt_cuentainicial" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="txt_cuentafinal" class="small">Cuenta final</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_cuentafinal" name="txt_cuentafinal" placeholder="Cuenta final">
                        <input type="hidden" id="id_txt_cuentafinal" name="id_txt_cuentafinal" class="form-control form-control-sm">
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-5">
                        <label for="txt_fecini" class="small">Fecha inicial</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecini" name="txt_fecini" placeholder="Fecha Inicial" value="<?php echo $_SESSION['vigencia'] ?>-01-01">
                    </div>
                    <div class="form-group col-md-5">
                        <label for="txt_fecfin" class="small">Fecha final</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecfin" name="txt_fecfin" placeholder="Fecha final" value="<?php echo $_SESSION['vigencia'] ?>-12-31">
                    </div>
                </div>
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="sl_tipo_documento" class="small">Tipo documento</label>
                        <select class="filtro form-control form-control-sm" id="sl_tipo_documento" name="sl_tipo_documento">
                            <?php tipo_documento($cmd, '--Seleccione--', 0) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-5">
                        <label for="txt_tercero_filtro" class="small">Tercero</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_tercero_filtro" name="txt_tercero_filtro" placeholder="Tercero">
                        <input type="hidden" id="id_txt_tercero" name="id_txt_tercero" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_consultar" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" id="btn_consultar" class="btn btn-outline-success btn-sm" title="Consultar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_csv" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" id="btn_csv" class="btn btn-outline-success btn-sm" title="Exportar a CSV">
                            <span class="fas fa-file-csv fa-lg" aria-hidden="true"></span>
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

<!--<script type="text/javascript" src="js/funciontesoreria.js?v=<?php echo date('YmdHis') ?>"></script>
<script type="text/javascript" src="js/informes/informes.js?v=<?php echo date('YmdHis') ?>"></script>-->
<script type="text/javascript" src="../js/informes_bancos/common.js?v=<?php echo date('YmdHis') ?>"></script>
<script type="text/javascript" src="../js/informes_bancos/informes_bancos.js?v=<?php echo date('YmdHis') ?>"></script>

<?php

function tipo_documento($cmd, $titulo = '', $id = 0)
{
    try {
        echo '<option value="">' . $titulo . '</option>';
        $sql = "SELECT 
                 ctb_fuente.id_doc_fuente
                 ,ctb_fuente.cod
                 ,ctb_fuente.nombre
                FROM ctb_fuente ORDER BY ctb_fuente.nombre";
        $rs = $cmd->query($sql);
        $objs = $rs->fetchAll();
        foreach ($objs as $obj) {
            if ($obj['id_doc_fuente']  == $id) {
                echo '<option value="' . $obj['id_doc_fuente'] . '" selected="selected">' . $obj['cod'] . ' -> ' . $obj['nombre'] . '</option>';
            } else {
                echo '<option value="' . $obj['id_doc_fuente'] . '">' . $obj['cod'] . ' -> ' . $obj['nombre'] . '</option>';
            }
        }
        $cmd = null;
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
    }
}


?>