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
$sql = "SELECT far_alm_pedido.*,
            far_bodegas.nombre AS nom_bodega,
            CASE far_alm_pedido.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' 
                                            WHEN 3 THEN 'ACEPTADO' WHEN 4 THEN 'CERRADO'
                                            WHEN 0 THEN 'ANULADO' END AS nom_estado
        FROM far_alm_pedido 
        INNER JOIN far_bodegas ON (far_bodegas.id_bodega=far_alm_pedido.id_bodega)
        WHERE id_pedido=" . $id . " LIMIT 1";
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
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $bodega = bodega_principal($cmd);
    $obj['id_bodega'] = $bodega['id_bodega'];
    $obj['nom_bodega'] = $bodega['nom_bodega'];
    $obj['id_sede'] = $bodega['id_sede'];

    $fecha = fecha_hora_servidor();
    $obj['fec_pedido'] = $fecha['fecha'];
    $obj['hor_pedido'] = $fecha['hora'];
}
$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$confirmar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$cerrar = in_array($obj['estado'],[3]) ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISRTAR PEDIDO DE ALMACEN</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Pedido-->
            <form id="frm_reg_pedidos">
                <input type="hidden" id="id_pedido" name="id_pedido" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="txt_nom_bod" class="small">Bodega</label>
                        <input type="text" class="form-control form-control-sm" id="txt_nom_bod" class="small" value="<?php echo $obj['nom_bodega'] ?>" readonly="readonly">
                        <input type="hidden" id="id_txt_nom_bod" name="id_txt_nom_bod" value="<?php echo $obj['id_bodega'] ?>">
                        <input type="hidden" id="id_txt_sede" name="id_txt_sede" value="<?php echo $obj['id_sede'] ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fec_ing" class="small">Fecha pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_fec_ped" name="txt_fec_ped" class="small" value="<?php echo $obj['fec_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_hor_ing" class="small">Hora pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_hor_ped" name="txt_hor_ped" class="small" value="<?php echo $obj['hor_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_num_ing" class="small">No. pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_num_ped" name="txt_num_ped" class="small" value="<?php echo $obj['num_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_est_ing" class="small">Estado pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_est_ped" name="txt_est_ped" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="txt_det_ing" class="small">Detalle</label>                   
                        <textarea class="form-control" id="txt_det_ped" name="txt_det_ped" rows="2"><?php echo $obj['detalle'] ?></textarea>
                    </div>
                </div>
            </form>    
            <table id="tb_pedidos_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Código</th>
                        <th>Descripción</th>                        
                        <th>Cantidad</th>
                        <th>Vr. Promedio</th> 
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>
            </table>
            <div class="form-row">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-2">
                    <label for="txt_val_tot" class="small">Total Orden pedido</label>
                </div>
                <div class="form-group col-md-2">
                    <input type="text" class="form-control form-control-sm" id="txt_val_tot" name="txt_val_tot" class="small" value="<?php echo formato_valor($obj['val_total']) ?>" readonly="readonly">
                </div>
            </div>    
        </div>
    </div>
    <div class="text-center pt-3">
        <button type="button" class="btn btn-primary btn-sm" id="btn_guardar" <?php echo $guardar ?>>Guardar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_confirmar" <?php echo $confirmar ?>>Confirmar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_cerrar" <?php echo $cerrar ?>>Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_anular" <?php echo $anular ?>>Anular</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/pedidos_alm/pedidos_alm_reg.js?v=<?php echo date('YmdHis') ?>"></script>