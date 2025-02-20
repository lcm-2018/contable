<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../terceros.php';
include 'cargar_combos.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
/*
$sql = "SELECT far_centrocosto_area.*,
            CONCAT_WS(' ',usr.nombre1,usr.nombre2,usr.apellido1,usr.apellido2) AS usr_responsable
        FROM far_centrocosto_area 
        INNER JOIN seg_usuarios_sistema AS usr ON (usr.id_usuario=far_centrocosto_area.id_responsable) 
        WHERE far_centrocosto_area.id_area=" . $id . " LIMIT 1";
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
    $obj['id_centrocosto'] = 0;
    $obj['id_tipo_area'] = 0;
    $obj['id_sede'] = 0;
}
*/

?>
<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">HISTORIAL MOVIMIENTOS POR TERCERO</h5>
        </div>
        <div class="px-2">
            <form id="frm_historialtercero">
                <input type="hidden" id="id_area" name="id_area" value="<?php echo $id ?>">
                <div class=" form-row">
                    <div class="form-group col-md-4">
                        <label for="txt_tercero_filtro" class="small">Tercero</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_tercero_filtro" name="txt_tercero_filtro" placeholder="Tercero" value="">
                        <input type="hidden" id="id_txt_tercero" name="id_txt_tercero" class="form-control form-control-sm" value="0">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_nrodisponibilidad_filtro" class="small">Nro Disponibilidad</label>
                        <input type="text" class="filtro form-control form-control-sm" id="txt_nrodisponibilidad_filtro" placeholder="Nro disponibilidad">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fecini_filtro" class="small">Fecha inicial</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecini_filtro" name="txt_fecini_filtro" placeholder="Fecha Inicial" value="<?php echo $_SESSION['vigencia'] ?>-01-01">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fecfin_filtro" class="small">Fecha final</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fecfin_filtro" name="txt_fecfin_filtro" placeholder="Fecha final" value="<?php echo $_SESSION['vigencia'] ?>-12-31">
                    </div>
                    <div class="form-group col-md-1">
                        <label for="btn_buscar_filtro" class="small">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
                        <a type="button" id="btn_buscar_filtro" class="btn btn-outline-success btn-sm" title="Filtrar">
                            <span class="fas fa-search fa-lg" aria-hidden="true"></span>
                        </a>
                    </div>
                </div>

                <div class=" form-row">
                    <table id="tb_terceros" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                        <thead>
                            <tr class="text-center centro-vertical">
                                <th>Id</th>
                                <th>Numero</th>
                                <th>Rp</th>
                                <th>Fecha</th>
                                <th>Tercero</th>
                                <th>Valor</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="text-left centro-vertical"></tbody>
                    </table>
                </div>
            </form>

            <!--Tabs-->
            <div class="p-3">
                <nav>
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active small" id="nav_lista_contratacion-tab" data-toggle="tab" href="#nav_lista_contratacion" role="tab" aria-controls="nav_lista_contratacion" aria-selected="true">CONTRATACIÓN</a>
                        <a class="nav-item nav-link small" id="nav_lista_regpresupuestal-tab" data-toggle="tab" href="#nav_lista_regpresupuestal" role="tab" aria-controls="nav_lista_regpresupuestal" aria-selected="false">REGISTRO PRESUPUESTAL</a>
                        <a class="nav-item nav-link small" id="nav_lista_obligaciones-tab" data-toggle="tab" href="#nav_lista_obligaciones" role="tab" aria-controls="nav_lista_obligaciones" aria-selected="false">OBLIGACIONES</a>
                        <a class="nav-item nav-link small" id="nav_lista_pagos-tab" data-toggle="tab" href="#nav_lista_pagos" role="tab" aria-controls="nav_lista_pagos" aria-selected="false">PAGOS</a>
                    </div>
                </nav>

                <div class="tab-content pt-2" id="nav-tabContent">
                    <!--Lista de contratacion-->
                    <div class="tab-pane fade show active" id="nav_lista_contratacion" role="tabpanel" aria-labelledby="nav_lista_contratacion-tab">
                        <table id="tb_cuentas" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                            <thead>
                                <tr class="text-center centro-vertical">
                                    <th>Id</th>
                                    <th>Cuenta contable</th>
                                    <th>Fecha inicio de vigencia</th>
                                    <th>Cuenta vigente</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="text-left centro-vertical"></tbody>
                        </table>
                    </div>

                    <!--Lista de reg presupuestal-->
                    <div class="tab-pane fade" id="nav_lista_regpresupuestal" role="tabpanel" aria-labelledby="nav_lista_regpresupuestal-tab">
                        <!--<table id="tb_articulos_lotes" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
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
                        </table>-->
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

<?php include '../../../scripts.php' ?>
<script type="text/javascript" src="../../js/historialtercero/historialtercero.js?v=<?php echo date('YmdHis') ?>"></script>
<script type="text/javascript" src="../../js/historialtercero/historialtercero_reg.js?v=<?php echo date('YmdHis') ?>"></script>