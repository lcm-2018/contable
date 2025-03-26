<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
//include 'cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_ctb_fuente = isset($_POST['id_ctb_fuente']) ? $_POST['id_ctb_fuente'] : 0;
$id_ctb_referencia = isset($_POST['id_ctb_referencia']) ? $_POST['id_ctb_referencia'] : 0;
$accion_pto = isset($_POST['accion_pto']) ? $_POST['accion_pto'] : 0;

// se vuelve a consultar los datos del tercero con el id que viene del boton
//------------------------------------
/*$sql = "SELECT tb_terceros.id_tercero_api,tb_terceros.nom_tercero
        FROM tb_terceros 
        WHERE id_tercero_api= $id_tercero LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();*/
//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LISTA DE AFECTACION PRESUPUESTAL DE INGRESOS</h5>
        </div>
        <div class="px-2">
            <form id="frm_afectacion_presupuestal">
                <input type="hidden" id="hd_id_ctb_fuente" name="hd_id_ctb_fuente" value="<?php echo $id_ctb_fuente ?>">
                <input type="hidden" id="hd_id_ctb_referencia" name="hd_id_ctb_referencia" value="<?php echo $id_ctb_referencia ?>">
                <input type="hidden" id="hd_accion_pto" name="hd_accion_pto" value="<?php echo $accion_pto ?>">
                <div class=" form-row">
                    <div class="form-group col-md-8">
                        <label for="txt_rubro" class="small">Rubro</label>
                        <input type="text" class="form-control form-control-sm" id="txt_rubro" name="txt_rubro" placeholder="Rubro">
                        <input type="hidden" id="hd_id_txt_rubro" name="hd_id_txt_rubro" class="form-control form-control-sm">
                        <input type="hidden" id="hd_id_tipo" name="hd_id_txt_rubro" class="form-control form-control-sm">
                        <input type="hidden" id="hd_anio" name="hd_id_txt_rubro" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_nrodisponibilidad_filtro" class="small">Valor</label>
                        <input type="text" class="form-control form-control-sm" id="txt_valor" name="txt_valor" placeholder="Valor">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_agregar_rubro" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" id="btn_agregar_rubro" class="btn btn-outline-success btn-sm" title="Agregar">
                            <span class="fas fa-plus fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_rubros" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th style="min-width: 70%;">Rubro</th>
                                <th class="text-right">Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_rubros"></tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../tesoreria/js/afectacion_presupuestal/afectacion_presupuestal.js?v=<?php echo date('YmdHis') ?>"></script>