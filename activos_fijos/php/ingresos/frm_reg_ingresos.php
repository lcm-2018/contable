<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT AI.*,
            SE.nom_sede AS nom_sede,
            TE.id_tercero,TE.nom_tercero,
            CASE AI.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado
        FROM acf_orden_ingreso AS AI
        INNER JOIN tb_sedes AS SE ON (SE.id_sede=AI.id_sede)
        INNER JOIN tb_terceros AS TE ON (TE.id_tercero=AI.id_provedor)
        WHERE AI.id_ingreso=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

if ($obj === false) {
    $obj = array(); // Inicializa $obj como un array vacío
}

if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto
    $obj['id_tercero'] = 0;
    $obj['nom_tercero'] = 'NINGUNO';
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $bodega = sede_principal($cmd);
    $obj['id_sede'] = $bodega['id_sede'];
    $obj['nom_sede'] = $bodega['nom_sede'];

    $fecha = fecha_hora_servidor();
    $obj['fec_ingreso'] = $fecha['fecha'];
    $obj['hor_ingreso'] = $fecha['hora'];
}

$area = area_principal($cmd);

$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$cerrar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR ORDEN DE INGRESO DE ACTIVOS FIJOS</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Ordenes de Ingreso-->
            <form id="frm_reg_ingresos">
                <input type="hidden" id="id_ingreso" name="id_ingreso" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_fec_ing" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_ide" name="txt_ide" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>                    
                    <div class="form-group col-md-2">
                        <label for="txt_nom_sede" class="small">Sede</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_sede" class="small" value="<?php echo $obj['nom_sede'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_sede" name="id_txt_sede" value="<?php echo $obj['id_sede'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="txt_nom_sede" class="small">Area</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_area" class="small" value="<?php echo $area['nom_area'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_area" name="id_txt_area" value="<?php echo $area['id_area'] ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="txt_fec_ing" class="small">Fecha Ingreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_fec_ing" name="txt_fec_ing" class="small" value="<?php echo $obj['fec_ingreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_hor_ing" class="small">Hora Ingreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_hor_ing" name="txt_hor_ing" class="small" value="<?php echo $obj['hor_ingreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_num_ing" class="small">No. Ingreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_num_ing" name="txt_num_ing" class="small" value="<?php echo $obj['num_ingreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_est_ing" class="small">Estado Ingreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_est_ing" name="txt_est_ing" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                            </div>
                        </div>    
                    </div>    
                    <div class="form-group col-md-2">
                        <label for="txt_num_fac" class="small">No. Fact./Acta/Rem.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_num_fac" name="txt_num_fac" class="small" value="<?php echo $obj['num_factura'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fec_fac" class="small">Fecha Fact./Acta/Rem.</label>
                        <input type="date" class="form-control form-control-sm" id="txt_fec_fac" name="txt_fec_fac" class="small" value="<?php echo $obj['fec_factura'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_tip_ing" class="small" required>Tipo Ingreso</label>
                        <select class="form-control form-control-sm" id="sl_tip_ing" name="sl_tip_ing">
                            <?php tipo_ingreso($cmd, '', $obj['id_tipo_ingreso']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="txt_tercero" class="small">Tercero</label>
                        <input type="text" class="form-control form-control-sm" id="txt_tercero" value="<?php echo $obj['nom_tercero'] ?>">
                        <input type="hidden" id="id_txt_tercero" name="id_txt_tercero" value="<?php echo $obj['id_tercero'] ?>">
                    </div>                    
                    <div class="form-group col-md-12">
                        <label for="txt_det_ing" class="small">Detalle</label>                   
                        <textarea class="form-control" id="txt_det_ing" name="txt_det_ing" rows="2"><?php echo $obj['detalle'] ?></textarea>
                    </div>
                </div>
            </form>    
            <table id="tb_ingresos_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Vr. Unitario</th>
                        <th>%IVA</th>
                        <th>Vr. Costo</th>
                        <th>Total</th>
                        <th>Observación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
            <div class="form-row">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-2">
                    <label for="txt_val_tot" class="small">Total Orden Ingreso</label>
                </div>
                <div class="form-group col-md-2">
                    <input type="text" class="form-control form-control-sm" id="txt_val_tot" name="txt_val_tot" class="small" value="<?php echo formato_valor($obj['val_total']) ?>" readonly="readonly">
                </div>
            </div>    
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar" <?php echo $guardar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_cerrar" <?php echo $cerrar ?>>Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_anular" <?php echo $anular ?>>Anular</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/ingresos/ingresos_reg.js?v=<?php echo date('YmdHis') ?>"></script>