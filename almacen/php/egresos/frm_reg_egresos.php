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
$sql = "SELECT EE.fec_egreso,EE.hor_egreso,EE.num_egreso,EE.id_sede,EE.id_bodega,EE.id_tipo_egreso,
            EE.id_centrocosto,IF(FA.id_area=0,0,FA.id_sede) AS id_sede_des,
            EE.id_area,TE.id_tercero,TE.nom_tercero,
            EE.estado,EE.detalle,EE.val_total,
            CASE EE.estado WHEN 1 THEN 'PENDIENTE' WHEN 2 THEN 'CERRADO' WHEN 0 THEN 'ANULADO' END AS nom_estado,
            EGRESO.id_pedido,CONCAT(EGRESO.detalle,'(',EGRESO.fec_pedido,')') AS des_pedido,
            II.id_ingreso,CONCAT(II.detalle,'(',II.fec_ingreso,')') AS des_ingreso,
            EETP.con_pedido,EETP.dev_fianza    
        FROM far_orden_egreso AS EE
        INNER JOIN far_orden_egreso_tipo AS EETP ON (EETP.id_tipo_egreso=EE.id_tipo_egreso)
        INNER JOIN tb_terceros AS TE ON (TE.id_tercero=EE.id_cliente)
        LEFT JOIN far_centrocosto_area AS FA ON (FA.id_area=EE.id_area)
        LEFT JOIN far_orden_ingreso AS II ON (II.id_ingreso=EE.id_ingreso_fz)
        LEFT JOIN (SELECT ED.id_egreso,PD.id_pedido,PP.detalle,PP.fec_pedido
                    FROM far_orden_egreso_detalle AS ED 
                    INNER JOIN far_cec_pedido_detalle AS PD ON (PD.id_ped_detalle=ED.id_ped_detalle)
                    INNER JOIN far_cec_pedido AS PP ON (PP.id_pedido=PD.id_pedido)
                    GROUP BY ED.id_egreso) AS EGRESO ON (EGRESO.id_egreso=EE.id_egreso)   
        WHERE EE.id_egreso=" . $id . " LIMIT 1";
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
    $obj['id_sede'] = sede_unica_usuario($cmd)['id_sede'];
    $obj['id_tipo_egreso'] = 0;
    $obj['id_tercero'] = 0;
    $obj['nom_tercero'] = 'NINGUNO';
    $obj['estado'] = 1;
    $obj['nom_estado'] = 'PENDIENTE';
    $obj['val_total'] = 0;

    $fecha = fecha_hora_servidor();
    $obj['fec_egreso'] = $fecha['fecha'];
    $obj['hor_egreso'] = $fecha['hora'];
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
            <h5 style="color: white;">REGISRTAR ORDEN DE EGRESO</h5>
        </div>
        <div class="px-2">
            <!--Formulario de registro de Ordenes de egreso-->
            <form id="frm_reg_orden_egreso">
                <input type="hidden" id="id_egreso" name="id_egreso" value="<?php echo $id ?>">
                <div class="form-row">
                    <div class="form-group col-md-1">
                        <label for="txt_fec_ing" class="small">Id.</label>
                        <input type="text" class="form-control form-control-sm" id="txt_ide" name="txt_ide" class="small" value="<?php echo ($id==-1?'':$id) ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-5">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="sl_sede_egr" class="small" required>Sede Origen</label>
                                <select class="form-control form-control-sm" id="sl_sede_egr" name="sl_sede_egr" <?php echo $editar ?>>
                                    <?php sedes_usuario($cmd, '', $obj['id_sede']) ?>
                                </select>
                                <input type="hidden" id="id_sede_egr" name="id_sede_egr" value="<?php echo $obj['id_sede'] ?>">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="sl_bodega_egr" class="small" required>Bodega Origen</label>
                                <select class="form-control form-control-sm" id="sl_bodega_egr" name="sl_bodega_egr" <?php echo $editar ?>>
                                    <?php bodegas_usuario($cmd, '', $obj['id_sede'], $obj['id_bodega']) ?>   
                                </select>
                                <input type="hidden" id="id_bodega_egr" name="id_bodega_egr" value="<?php echo $obj['id_bodega'] ?>">
                            </div>
                        </div>
                    </div>    
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="txt_fec_egr" class="small">Fecha egreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_fec_egr" name="txt_fec_egr" class="small" value="<?php echo $obj['fec_egreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_hor_egr" class="small">Hora egreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_hor_egr" name="txt_hor_egr" class="small" value="<?php echo $obj['hor_egreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_num_egr" class="small">No. egreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_num_egr" name="txt_num_egr" class="small" value="<?php echo $obj['num_egreso'] ?>" readonly="readonly">
                            </div>
                            <div class="form-group col-md-3">
                                <label for="txt_est_egr" class="small">Estado egreso</label>
                                <input type="text" class="form-control form-control-sm" id="txt_est_egr" name="txt_est_egr" class="small" value="<?php echo $obj['nom_estado'] ?>" readonly="readonly">
                            </div>
                        </div>    
                    </div>
                    <div class="form-group col-md-2">
                        <label for="sl_tip_egr" class="small" required>Tipo egreso</label>
                        <select class="form-control form-control-sm" id="sl_tip_egr" name="sl_tip_egr" <?php echo $editar ?>>
                            <?php tipo_egreso($cmd, '', 0, $obj['id_tipo_egreso']) ?>
                        </select>
                        <input type="hidden" id="id_tip_egr" name="id_tip_egr" value="<?php echo $obj['id_tipo_egreso'] ?>">
                    </div>                                       
                    <div class="form-group col-md-3">
                        <label for="sl_centrocosto" class="small">Centro de Costo</label>
                        <select class="form-control form-control-sm" id="sl_centrocosto" name="sl_centrocosto">
                            <?php centros_costo($cmd, '', $obj['id_centrocosto']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="sl_sede_des" class="small">Sede Destino</label>
                        <select class="form-control form-control-sm" id="sl_sede_des" name="sl_sede_des">
                            <?php sedes($cmd, '', $obj['id_sede_des']) ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="sl_area" class="small">Area Destino</label>
                        <select class="form-control form-control-sm" id="sl_area" name="sl_area">
                            <?php areas_centrocosto_sede($cmd, '', $obj['id_centrocosto'], $obj['id_sede_des'], $obj['id_area']) ?>   
                        </select>
                    </div>   
                    <div class="form-group col-md-5">
                        <label for="txt_tercero" class="small">Tercero</label>
                        <input type="text" class="form-control form-control-sm" id="txt_tercero" value="<?php echo $obj['nom_tercero'] ?>">
                        <input type="hidden" id="id_txt_tercero" name="id_txt_tercero" value="<?php echo $obj['id_tercero'] ?>">
                    </div>             
                </div>    
                
                <div class="form-row" id="divConPedido" <?php echo $obj['con_pedido'] == 1 ? '' : 'style="display: none;"' ?>>
                    <div class="form-group col-md-1">
                        <label for="txt_id_pedido" class="small">Id. Pedido</label>
                        <input type="text" class="form-control form-control-sm" id="txt_id_pedido" name="txt_id_pedido" class="small" value="<?php echo $obj['id_pedido'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <label for="txt_des_pedido" class="small">Pedido de una Dependencia</label>
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
                <div class="form-row" id="divDevFianza" <?php echo $obj['dev_fianza'] == 1 ? '' : 'style="display: none;"' ?>>
                    <div class="form-group col-md-1">
                        <label for="txt_id_ingreso" class="small">Id. Ingreso</label>
                        <input type="text" class="form-control form-control-sm" id="txt_id_ingreso" name="txt_id_ingreso" class="small" value="<?php echo $obj['id_ingreso'] ?>" readonly="readonly">
                    </div>
                    <div class="form-group col-md-6">
                        <div class="form-row">
                            <div class="form-group col-md-10">
                                <label for="txt_des_ingreso" class="small">ingreso de Almacen con Fianza</label>
                                <input type="text" class="form-control form-control-sm" id="txt_des_ingreso" name="txt_des_ingreso" class="small" value="<?php echo $obj['des_ingreso'] ?>" readonly="readonly" title="Doble Click para Seleccionar el No. de Ingreso Fianza">
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
                <div class="form-group col-md-12">
                        <label for="txt_det_egr" class="small">Detalle</label>                   
                        <textarea class="form-control" id="txt_det_egr" name="txt_det_egr" rows="2"><?php echo $obj['detalle'] ?></textarea>
                    </div>
                </div>    
            </form>    
            <table id="tb_egresos_detalles" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%; font-size:80%">
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
                    <label for="txt_val_tot" class="small">Total Orden Egreso</label>
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

<script type="text/javascript" src="../../js/egresos/egresos_reg.js?v=<?php echo date('YmdHis') ?>"></script>