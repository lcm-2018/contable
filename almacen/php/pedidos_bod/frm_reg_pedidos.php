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
$sql = "SELECT far_pedido.*,                    
            ss.nom_sede AS nom_sede_solicita,bs.nombre AS nom_bodega_solicita,                    
            sp.nom_sede AS nom_sede_provee,bp.nombre AS nom_bodega_provee,                    
            CASE far_pedido.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado 
        FROM far_pedido             
        INNER JOIN tb_sedes AS ss ON (ss.id_sede = far_pedido.id_sede_destino)
        INNER JOIN far_bodegas AS bs ON (bs.id_bodega = far_pedido.id_bodega_destino)           
        INNER JOIN tb_sedes AS sp ON (sp.id_sede = far_pedido.id_sede_origen)
        INNER JOIN far_bodegas AS bp ON (bp.id_bodega = far_pedido.id_bodega_origen)
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
    $obj['id_sede_origen'] = 0;
    $obj['id_sede_destino'] = sede_unica_usuario($cmd)['id_sede'];
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $fecha = fecha_hora_servidor();
    $obj['fec_pedido'] = $fecha['fecha'];
    $obj['hor_pedido'] = $fecha['hora'];
}
$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$cerrar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR PEDIDO DE BODDEGA</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Pedido-->
            <form id="frm_reg_pedidos">
                <input type="hidden" id="id_pedido" name="id_pedido" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_fec_ing" class="small">Id.</label>
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
                    <div class="form-group col-md-3">
                        <label for="sl_sede_solicitante" class="small">Sede DE donde se Solicita</label>
                        <select class="form-control form-control-sm" id="sl_sede_solicitante" name="sl_sede_solicitante">
                            <?php sedes_usuario($cmd, '', $obj['id_sede_destino']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_bodega_solicitante" class="small">Bodega DE donde se Solicita</label>
                        <select class="form-control form-control-sm" id="sl_bodega_solicitante" name="sl_bodega_solicitante">
                            <?php bodegas_usuario($cmd, '', $obj['id_sede_destino'], $obj['id_bodega_destino']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_sede_proveedor" class="small">Sede Proveedora A donde se solicita</label>
                        <select class="form-control form-control-sm" id="sl_sede_proveedor" name="sl_sede_proveedor">
                            <?php sedes($cmd, '', $obj['id_sede_origen']) ?>   
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_bodega_proveedor" class="small">Bodega Proveedora A donde se solicita</label>
                        <select class="form-control form-control-sm" id="sl_bodega_proveedor" name="sl_bodega_proveedor"> 
                            <?php bodegas_sede($cmd, '', $obj['id_sede_origen'], $obj['id_bodega_origen']) ?>   
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
        <button type="button" class="btn btn-primary btn-sm" id="btn_cerrar" <?php echo $cerrar ?>>Cerrar</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_anular" <?php echo $anular ?>>Anular</button>
        <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir" <?php echo $imprimir ?>>Imprimir</button>
        <a type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</a>
    </div>
</div>

<script type="text/javascript" src="../../js/pedidos_bod/pedidos_bod_reg.js?v=<?php echo date('YmdHis') ?>"></script>