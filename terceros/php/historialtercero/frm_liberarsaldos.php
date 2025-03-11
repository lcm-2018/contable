<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : -1;
$otro_form = isset($_POST['otro_form']) ? $_POST['otro_form'] : 0;

// se vuelve a consultar los datos del cdp con el id que viene del boton
//------------------------------------
$sql = "SELECT
	        pto_cdp.id_pto_cdp
            ,pto_cdp.id_manu
            , DATE_FORMAT(pto_cdp.fecha, '%Y-%m-%d') AS fecha                         
        FROM
            pto_cdp                          
        WHERE pto_cdp.id_pto_cdp = 679 LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();
//---------------------------------------------------
?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">LIBERACION DE SALDOS</h5>
        </div>
        <div class="px-2">
            <form id="frm_liberarsaldos">
                <input type="hidden" id="id_cdp" name="id_cdp" value="<?php echo $id_cdp ?>">
                <div class=" form-row">
                    <div class="form-group col-md-3">
                        <label for="txt_num_cdp" class="small">NUMERO CDP</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_num_cdp" name="txt_num_cdp" readonly="true" value="<?php echo $obj['id_manu'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_cdp" class="small">FECHA CDP</label>
                    </div>
                    <div class="form_group col-md-9">
                        <input type="text" class="filtro form-control form-control-sm" id="txt_fec_cdp" name="txt_fec_cdp" readonly="true" value="<?php echo $obj['fecha'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_liq" class="small">FECHA LIQUIDACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="date" class="form-control form-control-sm" id="txt_fec_liq" name="txt_fec_liq" placeholder="Fecha liquidacion" value="<?php echo $_SESSION['vigencia'] ?>-01-01">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_concepto_liq" class="small">CONCEPTO LIQUIDACION</label>
                    </div>
                    <div class="form-group col-md-9">
                        <input type="text" class="form-control form-control-sm" id="txt_concepto_liq" name="txt_concepto_liq" placeholder="Concepto liquidacion">
                    </div>
                </div>

                <div class=" w-100 text-left">
                    <table id="tb_saldos" class="table table-striped table-bordered table-sm nowrap table-hover shadow w-100" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id Rubro</th>
                                <th style="min-width: 60%;">Codigo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical" id="body_tb_saldos"></tbody>
                    </table>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_liquidar">Liquidar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>