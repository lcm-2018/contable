<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../common/cargar_combos.php';
include '../common/funciones_generales.php';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$id = isset($_POST['id']) ? $_POST['id'] : -1;
$sql = "SELECT far_cec_pedido.*,		            
            CASE far_cec_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CONFIRMADO' WHEN 3 THEN 'FINALIZADO' END AS nom_estado 
        FROM far_cec_pedido
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
    $obj['id_sede'] = 0;
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $fecha = fecha_hora_servidor();
    $obj['fec_pedido'] = $fecha['fecha'];
    $obj['hor_pedido'] = $fecha['hora'];
}
$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$confirmar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$finalizar = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR PEDIDO DE DEPENDENCIA</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Pedido-->
            <form id="frm_reg_pedidos">
                <input type="hidden" id="id_pedido" name="id_pedido" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_ide" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_ide" name="txt_ide" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_fec_pedido" class="small">Fecha Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_fec_pedido" name="txt_fec_pedido" class="small" value="<?php echo $obj['fec_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_hor_pedido" class="small">Hora Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_hor_pedido" name="txt_hor_pedido" class="small" value="<?php echo $obj['hor_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_num_pedido" class="small">No. Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_num_pedido" name="txt_num_pedido" class="small" value="<?php echo $obj['num_pedido'] ?>" readonly="readonly">
                    </div>                  
                    <div class="form-group col-md-2">
                        <label for="txt_est_pedido" class="small">Estado Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_est_pedido" name="txt_est_pedido" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                    </div>
                </div>    
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="sl_dependencia" class="small">Dependencia que solicita</label>
                        <select class="form-control form-control-sm" id="sl_dependencia" name="sl_dependencia">
                            <?php centros_costo_usuario($cmd, '', $obj['id_cencosto']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_sede_prov" class="small">Sede Proveedor</label>
                        <select class="form-control form-control-sm" id="sl_sede_prov" name="sl_sede_prov">
                            <?php sedes($cmd, '', $obj['id_sede']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_bodega_prov" class="small">Bodega Proveedor</label>
                        <select class="form-control form-control-sm" id="sl_bodega_prov" name="sl_bodega_prov"> 
                            <?php bodegas_sede($cmd, '', $obj['id_sede'], $obj['id_bodega']) ?>   
                        </select>
                    </div> 
                    <div class="form-group col-md-12">
                        <label for="txt_det_pedido" class="small">DETALLE</label>
                        <textarea class="form-control" id="txt_det_pedido" name="txt_det_pedido" rows="2"><?php echo $obj['detalle'] ?></textarea>
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
                    <label for="txt_val_tot" class="small">Total Pedido</label>
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
        <button type="button" class="btn btn-primary btn-sm" id="btn_finalizar" <?php echo $finalizar ?>>Finalizar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_anular" <?php echo $anular ?>>Anular</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/pedidos_cec/pedidos_cec_reg.js?v=<?php echo date('YmdHis') ?>"></script>