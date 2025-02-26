<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_kardex.php';
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' && $_POST['id_ingreso'] == -1) ||
        (PermisosUsuario($permisos, 5006, 3) && $oper == 'add' && $_POST['id_ingreso'] != -1) ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5006, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5006, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_ingreso'];
            $id_bodega = $_POST['id_txt_nom_bod'];
            $id_sede = $_POST['id_txt_sede'];
            $fec_ing = $_POST['txt_fec_ing'];
            $hor_ing = $_POST['txt_hor_ing'];
            $id_tiping = $_POST['id_tip_ing'];
            $id_pedido = $_POST['txt_id_pedido'];        
            $num_fac = $_POST['txt_num_fac'];
            $fec_fac = $_POST['txt_fec_fac'];                  
            $id_tercero = $_POST['sl_tercero'] ? $_POST['sl_tercero'] : 0;            
            $detalle = $_POST['txt_det_ing'];

            $sql = "SELECT orden_compra FROM far_orden_ingreso_tipo WHERE id_tipo_ingreso=" . $id_tiping;
            $rs = $cmd->query($sql);
            $obj_tiping = $rs->fetch();
            $id_pedido = $obj_tiping['orden_compra'] == 1 ? $id_pedido : 'NULL';
            
            if ($id == -1) {
                $sql = "INSERT INTO far_orden_ingreso(fec_ingreso,hor_ingreso,num_factura,fec_factura,id_tipo_ingreso,
                        id_provedor,id_centrocosto,detalle,val_total,id_sede,id_sedetraslado,id_bodega,id_pedido,id_usr_crea,fec_creacion,creado_far,estado)
                    VALUES('$fec_ing','$hor_ing','$num_fac','$fec_fac',$id_tiping,
                        $id_tercero,0,'$detalle',0,$id_sede,$id_sede,$id_bodega,$id_pedido,$id_usr_ope,'$fecha_ope',0,1)";
                $rs = $cmd->query($sql);

                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $sql_i = 'SELECT LAST_INSERT_ID() AS id';
                    $rs = $cmd->query($sql_i);
                    $obj = $rs->fetch();
                    $res['id'] = $obj['id'];
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $sql = "SELECT estado FROM far_orden_ingreso WHERE id_ingreso=" . $id;
                $rs = $cmd->query($sql);
                $obj_ingreso = $rs->fetch();

                if ($obj_ingreso['estado'] == 1) {
                    $sql = "UPDATE far_orden_ingreso 
                            SET num_factura='$num_fac',fec_factura='$fec_fac',id_tipo_ingreso=$id_tiping,id_provedor=$id_tercero,detalle='$detalle',id_pedido=$id_pedido
                            WHERE id_ingreso=" . $id;                           
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Ordenes de Ingreso en estado Pendiente';
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();

            if ($obj_ingreso['estado'] == 1) {
                $sql = "DELETE FROM far_orden_ingreso WHERE id_ingreso=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Ingreso en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $estado = isset($obj_ingreso['estado']) ? $obj_ingreso['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_orden_ingreso_detalle WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $num_detalles = $obj_ingreso['total'];

            $sql = "SELECT COUNT(*) AS total FROM far_kardex WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $num_reg_kardex = $obj_ingreso['total'];

            if ($estado == 1 && $num_detalles > 0 && $num_reg_kardex == 0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = 'SELECT far_orden_ingreso.id_sede,far_orden_ingreso.id_bodega,far_orden_ingreso.detalle,
                            far_orden_ingreso_detalle.id_lote,
                            far_orden_ingreso_detalle.cantidad*IFNULL(far_presentacion_comercial.cantidad,1) AS cantidad,
                            far_orden_ingreso_detalle.valor/IFNULL(far_presentacion_comercial.cantidad,1) AS valor                             
                        FROM far_orden_ingreso_detalle 
                        INNER JOIN far_orden_ingreso ON (far_orden_ingreso.id_ingreso = far_orden_ingreso_detalle.id_ingreso) 
                        INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote = far_orden_ingreso_detalle.id_lote)
                        INNER JOIN far_presentacion_comercial ON (far_presentacion_comercial.id_prescom = far_orden_ingreso_detalle.id_presentacion)
                        WHERE far_orden_ingreso_detalle.id_ingreso=' . $id;
                $rs = $cmd->query($sql);
                $objs_detalles = $rs->fetchAll();

                foreach ($objs_detalles as $obj_det) {
                    $id_sede = $obj_det['id_sede'];
                    $id_bodega = $obj_det['id_bodega'];
                    $detalle = $obj_det['detalle'];
                    $fec_movimiento = date('Y-m-d');

                    $id_lote = $obj_det['id_lote'];
                    $cantidad = $obj_det['cantidad'];
                    $valor = $obj_det['valor'];

                    /* Actualizar valores del Lote */
                    $sql = 'SELECT existencia,val_promedio,id_med FROM far_medicamento_lote WHERE id_lote=' . $id_lote . ' LIMIT 1';
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $id_medicamento = $obj['id_med'];
                    $val_promedio_lote = $obj['val_promedio'] ? $obj['val_promedio'] : 0;
                    $existencia_lote = $obj['existencia'];

                    /* Nuevos Valores [far_medicamento_lote] */
                    $valor_promedio_lote_kdx = $val_promedio_lote;
                    $existencia_lote_kdx = $existencia_lote + $cantidad;
                    if ($existencia_lote_kdx > 0) {
                        $valor_promedio_lote_kdx = ($val_promedio_lote * $existencia_lote + $cantidad * $valor) / $existencia_lote_kdx;
                    }

                    /* Valores [far_medicamentos] */
                    $sql = 'SELECT existencia,val_promedio FROM far_medicamentos WHERE id_med=' . $id_medicamento . ' LIMIT 1';
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $val_promedio_med = $obj['val_promedio'] ? $obj['val_promedio'] : 0;
                    $existencia_med = $obj['existencia'];

                    $val_promedio_medicamento_kdx = $val_promedio_med;
                    $existencia_medicamento_kdx = $existencia_med + $cantidad;
                    if ($existencia_medicamento_kdx > 0) {
                        $val_promedio_medicamento_kdx = ($val_promedio_med * $existencia_med + $valor * $cantidad) / $existencia_medicamento_kdx;
                    }

                    /* Inserta registros en kardex de estaod=1-activo */
                    $sql = "INSERT INTO far_kardex(id_lote,fec_movimiento,id_ingreso,id_sede,id_bodega,detalle,can_ingreso,val_ingreso,existencia_lote,val_promedio_lote,id_med,existencia,val_promedio,estado) 
                            VALUES($id_lote,'$fec_movimiento',$id,$id_sede,$id_bodega,'$detalle',$cantidad,$valor,$existencia_lote_kdx,$valor_promedio_lote_kdx,$id_medicamento,$existencia_medicamento_kdx,$val_promedio_medicamento_kdx,1)";
                    $rs1 = $cmd->query($sql);

                    $sql = "UPDATE far_medicamento_lote SET existencia=$existencia_lote_kdx,val_promedio=$valor_promedio_lote_kdx WHERE id_lote=" . $id_lote;
                    $rs2 = $cmd->query($sql);

                    $sql = "UPDATE far_medicamentos SET existencia=$existencia_medicamento_kdx,val_promedio=$val_promedio_medicamento_kdx WHERE id_med=" . $id_medicamento;
                    $rs3 = $cmd->query($sql);

                    if ($rs1 == false || $rs2 == false || $rs3 == false || error_get_last()) {
                        $error = 1;
                        break;
                    }
                }
                if ($error == 0) {
                    $sql = 'SELECT num_ingresoactual FROM tb_datos_ips LIMIT 1';
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $num_ingreso = $obj['num_ingresoactual'];
                    $res['num_ingreso'] = $num_ingreso;

                    $sql = "UPDATE far_orden_ingreso SET num_ingreso=$num_ingreso,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_ingreso=$id";
                    $rs1 = $cmd->query($sql);
                    $sql = 'UPDATE tb_datos_ips SET num_ingresoactual=num_ingresoactual+1';
                    $rs2 = $cmd->query($sql);

                    if ($rs1 == false || $rs2 == false || error_get_last()) {
                        $error = 1;
                    }
                }
                if ($error == 0) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = 'Error de Ejecución de Proceso';
                    $cmd->rollBack();
                }
            } else {
                if ($estado != 1) {
                    $res['mensaje'] = 'Solo puede Cerrar Ordenes de Ingreso en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'La Ordenes de Ingreso no tiene detalles';
                } else if ($num_reg_kardex > 0) {
                    $res['mensaje'] = 'La Orden de Ingreso ya tiene registro de movimientos en Kardex';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_ingreso WHERE id_ingreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();
            $estado = $obj_ingreso['estado'];

            if ($estado == 2) {
                $respuesta = verificar_kardex($cmd, $id, "I");

                if ($respuesta == 'ok') {
                    
                    $cmd->beginTransaction();

                    $sql = "UPDATE far_orden_ingreso 
                            INNER JOIN far_kardex ON(far_kardex.id_ingreso = far_orden_ingreso.id_ingreso)
                            SET far_orden_ingreso.id_usr_anula=$id_usr_ope,far_orden_ingreso.fec_anulacion='$fecha_ope',far_orden_ingreso.estado=0,far_kardex.estado=0 
                            WHERE far_orden_ingreso.id_ingreso=$id";
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $sql = "SELECT GROUP_CONCAT(id_lote) AS lotes
                                FROM far_orden_ingreso_detalle WHERE id_ingreso=" . $id;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $lotes = $obj['lotes'];

                        recalcular_kardex($cmd,$lotes,'I',$id,'','','','');                        
                    }
                    if ($rs) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                        $accion = 'Anular';
                        $opcion = 'Orden de Ingreso';
                        $detalle = 'Anulo Orden Ingreso Id: ' . $id;
                        bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                    } else {
                        $cmd->rollBack();
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = $respuesta;
                }
            } else {
                $res['mensaje'] = 'Solo puede Anular Ordenes de Ingreso en estado Cerrado';
            }
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
