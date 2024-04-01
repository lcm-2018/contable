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
$sql = "SELECT far_medicamentos.*,
            IF(id_uni=0,unidad,CONCAT(unidad,' (',descripcion,')')) AS unidad_medida
        FROM far_medicamentos 
        LEFT JOIN far_med_unidad ON (far_med_unidad.id_uni=far_medicamentos.id_unidadmedida_2)
        WHERE id_med=" . $id . " LIMIT 1";
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
    $obj['es_clinico'] = 0;
}
?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR ARTICULO</h5>
        </div>
        <div class="px-2">

            <!--Formulario de registro de Articulos-->
            <form id="frm_reg_articulos">
                <input type="hidden" id="id_articulo" name="id_articulo" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-2">
                        <label for="txt_cod_art" class="small">Código</label>
                        <input type="text" class="form-control form-control-sm number" id="txt_cod_art" name="txt_cod_art" required value="<?php echo $obj['cod_medicamento'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txt_nom_art" class="small">Nombre</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_art" name="txt_nom_art" required value="<?php echo $obj['nom_medicamento'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_subgrp_art" class="small">Subgrupo</label>
                        <select class="form-control form-control-sm" id="sl_subgrp_art" name="sl_subgrp_art" required>
                            <?php subgrupo_articulo($cmd, '', $obj['id_subgrupo']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_topmin_art" class="small">Tope Mínimo</label>
                        <input type="text" class="form-control form-control-sm numberint" id="txt_topmin_art" name="txt_topmin_art" required value="<?php echo $obj['top_min'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_topmax_art" class="small">Tope Máximo</label>
                        <input type="text" class="form-control form-control-sm numberint" id="txt_topmax_art" name="txt_topmax_art" required value="<?php echo $obj['top_max'] ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="txt_unimed_art" class="small">Unidad Medida</label>
                        <input type="text" class="form-control form-control-sm" id="txt_unimed_art" required value="<?php echo $obj['unidad_medida'] ?>">
                        <input type="hidden" id="id_txt_unimed_art" name="id_txt_unimed_art" value="<?php echo $obj['id_unidadmedida_2'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small">Para Uso Clínico</label>
                        <div class="form-control form-control-sm" id="rdo_escli_art">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_escli_art" id="rdo_escli_art_si" value="1" <?php echo $obj['es_clinico'] == 1 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_escli_art_si">SI</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="rdo_escli_art" id="rdo_escli_art_no" value="0" <?php echo $obj['es_clinico'] == 0 ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="rdo_escli_art_no">NO</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_medins_art" class="small">Med/Ins Uso Clínico</label>
                        <select class="form-control form-control-sm" id="sl_medins_art" name="sl_medins_art">
                            <?php tipo_Medicamento_insumo($cmd, $obj['id_tip_medicamento']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_estado" class="small">Estado</label>
                        <select class="form-control form-control-sm" id="sl_estado" name="sl_estado">
                            <?php estados_registros('', $obj['estado']) ?>
                        </select>
                    </div>
                </div>
            </form>

            <!--Tabs para CUMS y Lotes-->
            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active small" id="nav_lista_cums-tab" data-toggle="tab" href="#nav_lista_cums" role="tab" aria-controls="nav_lista_cums" aria-selected="true">CUMS</a>
                        <a class="nav-item nav-link small" id="nav_lista_lotes-tab" data-toggle="tab" href="#nav_lista_lotes" role="tab" aria-controls="nav_lista_lotes" aria-selected="false">LOTES</a>
                    </div>
                </nav>

                <div class="tab-content pt-2" id="nav-tabContent">
                    <!--Lista de CUMS-->
                    <div class="tab-pane fade show active" id="nav_lista_cums" role="tabpanel" aria-labelledby="nav_lista_cums-tab">
                        <table id="tb_articulos_cums" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                            <thead>
                                <tr class="text-center centro-vertical">
                                    <th>Id</th>
                                    <th>CUM</th>
                                    <th>IUM</th>
                                    <th>Laboratorio</th>
                                    <th>Presentación Comercial</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-left centro-vertical"></tbody>
                        </table>
                    </div>

                    <!--Lista de LOTES-->
                    <div class="tab-pane fade" id="nav_lista_lotes" role="tabpanel" aria-labelledby="nav_lista_lotes-tab">
                        <table id="tb_articulos_lotes" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                            <thead>
                                <tr class="text-center centro-vertical">
                                    <th>Id</th>
                                    <th>Lote</th>
                                    <th>Principal</th>                                    
                                    <th>Fecha<br>Vencimiento</th>                                    
                                    <th>Presentación del Lote</th>
                                    <th>Unidades en UMPL</th>
                                    <th>Existencia</th>
                                    <th>CUM</th>
                                    <th>Bodega</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-left centro-vertical"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center pt-3">    
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar">Guardar</button>
        <a type="button" class="btn btn-secondary  btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/articulos/articulos_reg.js?v=<?php echo date('YmdHis') ?>"></script>