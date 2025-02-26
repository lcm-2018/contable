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
$sql = "SELECT TT.fec_traslado,TT.hor_traslado,TT.num_traslado,TT.tipo,
            TT.id_sede_origen,TT.id_bodega_origen,TT.id_sede_destino,TT.id_bodega_destino,
            TT.estado,TT.detalle,TT.val_total,
            CASE TT.estado WHEN 0 THEN 'ANULADO' WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' END AS nom_estado,
            PEDIDO.id_pedido,CONCAT(PEDIDO.detalle,'(',PEDIDO.fec_pedido,')') AS des_pedido,
            OI.id_ingreso,CONCAT(OI.detalle,'(',OI.fec_ingreso,')') AS des_ingreso 
        FROM far_traslado AS TT
        LEFT JOIN far_orden_ingreso AS OI ON (OI.id_ingreso=TT.id_ingreso)
        LEFT JOIN (SELECT TD.id_traslado,PD.id_pedido,PP.detalle,PP.fec_pedido
                    FROM far_traslado_detalle AS TD 
                    INNER JOIN far_pedido_detalle AS PD ON (PD.id_ped_detalle=TD.id_ped_detalle)
                    INNER JOIN far_pedido AS PP ON (PP.id_pedido=PD.id_pedido)
                    GROUP BY TD.id_traslado) AS PEDIDO ON (PEDIDO.id_traslado=TT.id_traslado)    
        WHERE TT.id_traslado=" . $id . " LIMIT 1";
$rs = $cmd->query($sql);
$obj = $rs->fetch();

$editar = 'disabled="disabled"';
if (empty($obj)) {
    $n = $rs->columnCount();
    for ($i = 0; $i < $n; $i++) :
        $col = $rs->getColumnMeta($i);
        $name = $col['name'];
        $obj[$name] = NULL;
    endfor;
    //Inicializa variable por defecto
    $obj['id_sede_origen'] = sede_unica_usuario($cmd)['id_sede'];    
    $obj['id_sede_destino'] = 0;
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $fecha = fecha_hora_servidor();
    $obj['fec_traslado'] = $fecha['fecha'];
    $obj['hor_traslado'] = $fecha['hora'];
    $editar = '';
}
$guardar = in_array($obj['estado'],[1]) ? '' : 'disabled="disabled"';
$cerrar = in_array($obj['estado'],[1]) && $id != -1 ? '' : 'disabled="disabled"';
$anular = in_array($obj['estado'],[2]) ? '' : 'disabled="disabled"';
$imprimir = $id != -1 ? '' : 'disabled="disabled"';

?>

<div class="px-0">
    <div class="shadow">
        <div class="card-header mb-3" style="background-color: #16a085 !important;">
            <h5 style="color: white;">REGISTRAR TRASLADO</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de traslado-->
            <form id="frm_reg_traslados">
                <input type="hidden" id="id_traslado" name="id_traslado" value="<?php echo $id ?>">
                <div class="form-row">  
                    <div class="form-group col-md-1">
                        <label for="txt_fec_ing" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_ide" name="txt_ide" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>                  
                    <div class="form-group col-md-2">
                        <label for="txt_fec_traslado" class="small">Fecha traslado</label>
                        <input type="text" class="form-control form-control-sm" id="txt_fec_traslado" name="txt_fec_traslado" class="small" value="<?php echo $obj['fec_traslado'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_hor_traslado" class="small">Hora traslado</label>
                        <input type="text" class="form-control form-control-sm" id="txt_hor_traslado" name="txt_hor_traslado" class="small" value="<?php echo $obj['hor_traslado'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-2">
                        <label for="txt_num_traslado" class="small">No. traslado</label>
                        <input type="text" class="form-control form-control-sm" id="txt_num_traslado" name="txt_num_traslado" class="small" value="<?php echo $obj['num_traslado'] ?>" readonly="readonly">
                    </div>                  
                    <div class="form-group col-md-2">
                        <label for="txt_est_traslado" class="small">Estado traslado</label>
                        <input type="text" class="form-control form-control-sm" id="txt_est_traslado" name="txt_est_traslado" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_tip_traslado" class="small" required>Tipo Traslado</label>
                        <select class="form-control form-control-sm" id="sl_tip_traslado" name="sl_tip_traslado" <?php echo $editar ?>>
                            <?php tipo_traslado('', $obj['tipo']) ?>
                        </select>
                        <input type="hidden" id="id_tip_traslado" name="id_tip_traslado" value="<?php echo $obj['tipo'] ?>">
                    </div>
                </div>

                <div class="form-row" id="divPedido" <?php echo $obj['tipo'] == 1 ? '' : 'style="display: none;"' ?>>
                    <div class="form-group col-md-1">
                        <label for="txt_id_pedido" class="small">Id. Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_id_pedido" name="txt_id_pedido" class="small" value="<?php echo $obj['id_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <label for="txt_des_pedido" class="small">Pedido de una Bodega para el Traslado</label>
                                <input type="text" class="form-control form-control-sm" id="txt_des_pedido" name="txt_des_pedido" class="small" value="<?php echo $obj['des_pedido'] ?>" readonly="readonly" title="Doble Click para Seleccionar el No. de Pedido">
                            </div>
                            <div class="form-group col-md-1">            
                                <label class="small">&emsp;&emsp;&emsp;&emsp;</label>            
                                <a type="button" id="btn_imprime_pedido" class="btn btn-outline-success btn-sm" title="Imprimir Pedido Seleccionado">
                                    <span class="fas fa-print" aria-hidden="true"></span>                                       
                                </a>
                            </div>
                            <div class="form-group col-md-1">            
                                <label class="small">&emsp;&emsp;&emsp;&emsp;</label>            
                                <a type="button" id="btn_cancelar_pedido" class="btn btn-outline-success btn-sm" title="Cancelar Selección">
                                    <span class="fas fa-ban" aria-hidden="true"></span>                                       
                                </a>
                            </div>
                        </div>
                    </div>        
                </div>   
                <div class="form-row" id="divIngreso" <?php echo $obj['tipo'] == 2 ? '' : 'style="display: none;"' ?>>
                    <div class="form-group col-md-1">
                        <label for="txt_id_ingreso" class="small">Id. Ingreso</label>
                        <input type="text" class="form-control form-control-sm" id="txt_id_ingreso" name="txt_id_ingreso" class="small" value="<?php echo $obj['id_ingreso'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <label for="txt_des_ingreso" class="small">Ingreso de Almacen A Trasladar</label>
                                <input type="text" class="form-control form-control-sm" id="txt_des_ingreso" name="txt_des_ingreso" class="small" value="<?php echo $obj['des_ingreso'] ?>" readonly="readonly" title="Doble Click para Seleccionar el No. de Ingreso">
                            </div>
                            <div class="form-group col-md-1">            
                                <label class="small">&emsp;&emsp;&emsp;&emsp;</label>            
                                <a type="button" id="btn_imprime_ingreso" class="btn btn-outline-success btn-sm" title="Imprimir Ingreso Seleccionado">
                                    <span class="fas fa-print" aria-hidden="true"></span>                                       
                                </a>
                            </div>
                            <div class="form-group col-md-1">            
                                <label class="small">&emsp;&emsp;&emsp;&emsp;</label>            
                                <a type="button" id="btn_cancelar_ingreso" class="btn btn-outline-success btn-sm" title="Cancelar Selección">
                                    <span class="fas fa-ban" aria-hidden="true"></span>                                       
                                </a>
                            </div>
                        </div>
                    </div>        
                </div> 

                <div class="form-row">                    
                    <div class="form-group col-md-3">
                        <label for="sl_sede_origen" class="small">Sede Origen</label>
                        <select class="form-control form-control-sm" id="sl_sede_origen" name="sl_sede_origen" <?php echo $editar ?>>
                            <?php sedes_usuario($cmd, '', $obj['id_sede_origen']) ?>   
                        </select>
                        <input type="hidden" id="id_sede_origen" name="id_sede_origen" value="<?php echo $obj['id_sede_origen'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_bodega_origen" class="small">Bodega Origen</label>
                        <select class="form-control form-control-sm" id="sl_bodega_origen" name="sl_bodega_origen" <?php echo $editar ?>>
                            <?php bodegas_usuario($cmd, '', $obj['id_sede_origen'], $obj['id_bodega_origen']) ?>   
                        </select>
                        <input type="hidden" id="id_bodega_origen" name="id_bodega_origen" value="<?php echo $obj['id_bodega_origen'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_sede_destino" class="small">Sede Destino</label>
                        <select class="form-control form-control-sm" id="sl_sede_destino" name="sl_sede_destino" <?php echo $editar ?>>
                            <?php sedes($cmd, '', $obj['id_sede_destino']) ?>   
                        </select>
                        <input type="hidden" id="id_sede_destino" name="id_sede_destino" value="<?php echo $obj['id_sede_destino'] ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_bodega_destino" class="small">Bodega Destino</label>
                        <select class="form-control form-control-sm" id="sl_bodega_destino" name="sl_bodega_destino" <?php echo $editar ?>> 
                            <?php bodegas_sede($cmd, '', $obj['id_sede_destino'], $obj['id_bodega_destino']) ?>   
                        </select>
                        <input type="hidden" id="id_bodega_destino" name="id_bodega_destino" value="<?php echo $obj['id_bodega_destino'] ?>">
                    </div>                
                    <div class="form-group col-md-12">
                        <label for="txt_det_traslado" class="small">DETALLE</label>
                        <textarea class="form-control" id="txt_det_traslado" name="txt_det_traslado" rows="2"><?php echo $obj['detalle'] ?></textarea>
                    </div>                      
                </div>
            </form>    
            <table id="tb_traslados_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>Id</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Lote</th>
                        <th>Existencia</th>
                        <th>Fecha Vencimiento</th>
                        <th>Cantidad</th>
                        <th>Vr. Unitario</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-left centro-vertical"></tbody>                
            </table>
            <div class="form-row">
                <div class="form-group col-md-4"></div>
                <div class="form-group col-md-2">
                    <label for="txt_val_tot" class="small">Total traslado</label>
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

<script type="text/javascript" src="../../js/traslados/traslados_reg.js?v=<?php echo date('YmdHis') ?>"></script>