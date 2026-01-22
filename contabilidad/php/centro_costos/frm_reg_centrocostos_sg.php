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

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT tb_centrocostos_subgr_cta.*
        FROM tb_centrocostos_subgr_cta
        WHERE id_cecsubgrp=" . $id . " LIMIT 1";
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
            <h7 style="color: white;">REGISRTAR CUENTA CONTABLE POR SUBGRUPO</h7>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Cuenta-->
            <form id="frm_reg_centrocostos_sg">
                <input type="hidden" id="id_cec_sg" name="id_cec_sg" value="<?php echo $id ?>">
                <div class=" form-row">
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
            
            <table id="tb_cuentas_sg_det" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Id.SubGrupo</th>
                        <th>SubGrupo</th>
                        <th>Cuenta Contable</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>

        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar_sg">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/centro_costos/centro_costos_reg_det.js?v=<?php echo date('YmdHis') ?>"></script>
