<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) && $_POST['id'] ? $_POST['id'] : -1;
$id_subgrupo = $_POST['id_subgrupo'];

$sql = "SELECT CSG.*,CONCAT_WS(' - ',CTA.cuenta,CTA.nombre) AS cuenta
	    FROM tb_centrocostos_subgr_cta_detalle AS CSG
        INNER JOIN ctb_pgcp AS CTA ON (CTA.id_pgcp=CSG.id_cuenta)
        WHERE CSG.id_cecsubgrp_det=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto
    $obj['estado'] = 1;
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h7 style="color: white;">REGISRTAR CUENTA CONTABLE DE SUBGRUPO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Cuenta-->
            <form id="frm_reg_centrocostos_sg_cta">
                <input type="hidden" id="id_cec_sgcta" name="id_cec_sgcta" value="<?php echo $id ?>">
                <input type="hidden" id="id_subgrupo" name="id_subgrupo" value="<?php echo $id_subgrupo ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_cta_con" class="small">Cuenta Contable</label>
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_con" data-campoid="id_txt_cta_con" value="<?php echo $obj['cuenta'] ?>">
                        <input type="hidden" id="id_txt_cta_con" name="id_txt_cta_con" value="<?php echo $obj['id_cuenta'] ?>">
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_sg_cta">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
