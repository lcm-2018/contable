<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT SBG.*,
            CONCAT_WS(' - ',CACT.cuenta,CACT.nombre) AS cuenta_act,
            CONCAT_WS(' - ',CDEP.cuenta,CDEP.nombre) AS cuenta_dep,
            CONCAT_WS(' - ',CGAS.cuenta,CGAS.nombre) AS cuenta_gas
        FROM far_subgrupos_cta_af AS SBG
        INNER JOIN ctb_pgcp AS CACT ON (CACT.id_pgcp=SBG.id_cuenta)
        INNER JOIN ctb_pgcp AS CDEP ON (CDEP.id_pgcp=SBG.id_cuenta_dep)
        INNER JOIN ctb_pgcp AS CGAS ON (CGAS.id_pgcp=SBG.id_cuenta_gas)
        WHERE SBG.id_subgrupo_cta=" . $id . " LIMIT 1";
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
            <h7 style="color: white;">REGISRTAR CUENTAS CONTABLES DE SUBGRUPO - ACTIVO FIJO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Cuenta-->
            <form id="frm_reg_subgrupos_cta_af">
                <input type="hidden" id="id_subgrupocta_af" name="id_subgrupocta_af" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-12">
                        <label for="txt_cta_con_act" class="small">Cuenta Contable</label>
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_con_act" data-campoid="id_txt_cta_con_act" value="<?php echo $obj['cuenta_act'] ?>">
                        <input type="hidden" id="id_txt_cta_con_act" name="id_txt_cta_con_act" value="<?php echo $obj['id_cuenta'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_cta_con_dep" class="small">Cuenta Contable Depreciación</label>
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_con_dep" data-campoid="id_txt_cta_con_dep" value="<?php echo $obj['cuenta_dep'] ?>">
                        <input type="hidden" id="id_txt_cta_con_dep" name="id_txt_cta_con_dep" value="<?php echo $obj['id_cuenta_dep'] ?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_cta_con_gas" class="small">Cuenta Contable Gasto de Depreciación</label>
                        <input type="text" class="form-control form-control-sm cuenta" id="txt_cta_con_gas" data-campoid="id_txt_cta_con_gas" value="<?php echo $obj['cuenta_gas'] ?>">
                        <input type="hidden" id="id_txt_cta_con_gas" name="id_txt_cta_con_gas" value="<?php echo $obj['id_cuenta_gas'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_fec_vig" class="small">Fecha Inicio de Vigencia</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_vig" name="txt_fec_vig" value="<?php echo $obj['fecha_vigencia'] ?>">
                    </div> 
                    <div class="form-group col-md-2">
                        <label for="sl_estado_cta" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado_cta" name="sl_estado_cta">
                            <?php estados_registros('',$obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_cta_af">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>
