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

    if ((PermisosUsuario($permisos, 5008, 2) && $oper == 'add' && $_POST['id_traslado'] == -1) ||
        (PermisosUsuario($permisos, 5008, 3) && $oper == 'add' && $_POST['id_traslado'] != -1) ||
        (PermisosUsuario($permisos, 5008, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5008, 2) && PermisosUsuario($permisos, 5006, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5008, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_traslado'];
            $id_sede_ori = isset($_POST['sl_sede_origen']) ? $_POST['sl_sede_origen'] : 0;
            $id_bodega_ori = isset($_POST['sl_bodega_origen']) ? $_POST['sl_bodega_origen'] : 0;
            $id_sede_des = isset($_POST['sl_sede_destino']) ? $_POST['sl_sede_destino'] : 0;
            $id_bodega_des = isset($_POST['sl_bodega_destino']) ? $_POST['sl_bodega_destino'] : 0;
            $fec_traslado = $_POST['txt_fec_traslado'];
            $hor_traslado = $_POST['txt_hor_traslado'];
            $detalle = $_POST['txt_det_traslado'];

            if ($id == -1) {
                if($id_bodega_ori != $id_bodega_des){
                    $sql = "INSERT INTO far_traslado(fec_traslado,hor_traslado,id_sede_origen,id_bodega_origen,
                            id_sede_destino,id_bodega_destino,detalle,val_total,id_usr_crea,fec_creacion,estado)
                        VALUES('$fec_traslado','$hor_traslado',$id_sede_ori,$id_bodega_ori,$id_sede_des,$id_bodega_des,'$detalle',0,$id_usr_ope,'$fecha_ope',1)";
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
                    $res['mensaje'] = 'La Bodega que Solicita y la Bodega Proveedora deben ser diferentes';    
                } 
            } else {
                $sql = "SELECT estado FROM far_traslado WHERE id_traslado=" . $id;
                $rs = $cmd->query($sql);
                $obj_tra = $rs->fetch();

                if ($obj_tra['estado'] == 1) {
                    $sql = "UPDATE far_traslado 
                        SET detalle='$detalle'
                        WHERE id_traslado=" . $id;
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Traslados en estado Pendiente';
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_traslado WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();

            if ($obj_tra['estado'] == 1) {
                $sql = "DELETE FROM far_traslado WHERE id_traslado=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Traslados en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_traslado WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $estado = isset($obj_tra['estado']) ? $obj_tra['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_traslado_detalle WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $num_detalles = $obj_tra['total'];

            $sql = "SELECT COUNT(*) AS total FROM far_kardex WHERE id_ingreso_tra=$id OR id_egreso_tra=$id";
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $num_reg_kardex = $obj_tra['total'];

            if ($estado == 1 && $num_detalles > 0 && $num_reg_kardex == 0) {
                $respuesta = verificar_existencias($cmd, $id, "T");

                if ($respuesta == 'ok') {

                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = 'SELECT id_sede_origen,id_bodega_origen,id_sede_destino,id_bodega_destino,detalle FROM far_traslado WHERE id_traslado=' . $id;
                    $rs = $cmd->query($sql);
                    $obj_tra = $rs->fetch();
                    $id_sede_origen = $obj_tra['id_sede_origen'];
                    $id_bodega_origen = $obj_tra['id_bodega_origen'];
                    $id_sede_destino = $obj_tra['id_sede_destino'];
                    $id_bodega_destino = $obj_tra['id_bodega_destino'];
                    $detalle = 'TRASLADO BODEGAS: ' . $obj_tra['detalle'];
                    $fec_movimiento = date('Y-m-d');

                    /*Crear los lotes en la bodega destino si no existen*/
                    $sql = 'SELECT id_tra_detalle,id_lote_origen FROM far_traslado_detalle WHERE id_traslado=' . $id;
                    $rs = $cmd->query($sql);
                    $objs_detalles = $rs->fetchAll();

                    foreach ($objs_detalles as $obj_det) {
                        $id_detalle = $obj_det['id_tra_detalle'];
                        $id_lote_origen = $obj_det['id_lote_origen'];
                        $id_lote_destino = '';

                        /*trae los datos del lote de la bodega origen*/
                        $sql = "SELECT lote,id_med,id_cum,fec_vencimiento FROM far_medicamento_lote WHERE id_lote=$id_lote_origen LIMIT 1";
                        $rs = $cmd->query($sql);
                        $obj_lo = $rs->fetch();
                        $lote = $obj_lo['lote'];
                        $id_med = $obj_lo['id_med'];
                        $id_cum = $obj_lo['id_cum'];
                        $fec_ven = $obj_lo['fec_vencimiento']; 

                        $sql = "SELECT id_lote AS id_lote_destino FROM far_medicamento_lote WHERE lote='$lote' AND id_med=$id_med AND id_cum=$id_cum AND id_bodega=$id_bodega_destino LIMIT 1";
                        $rs = $cmd->query($sql);
                        $obj_ld = $rs->fetch();

                        if (isset($obj_ld['id_lote_destino'])){
                            $id_lote_destino = $obj_ld['id_lote_destino'];
                        } else {                        
                            $sql1 = "INSERT INTO far_medicamento_lote(lote,id_med,id_cum,id_bodega,id_lote_pri,fec_vencimiento,id_usr_crea,estado) 
                                    VALUES ('$lote',$id_med,$id_cum,$id_bodega_destino,$id_lote_origen,'$fec_ven',$id_usr_ope,1)";
                            $rs1 = $cmd->query($sql1);

                            $sql = 'SELECT LAST_INSERT_ID() AS id_lote_destino';
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $id_lote_destino = $obj['id_lote_destino'];
                            
                            if ($rs1 == false || error_get_last()) {
                                $error = 1;
                                break;
                            }
                        }

                        $sql2 = "UPDATE far_traslado_detalle SET id_lote_destino=$id_lote_destino WHERE id_tra_detalle=" . $id_detalle;
                        $rs2 = $cmd->query($sql2);

                        if ($error == 1 || $rs2 == false || error_get_last()) {
                            $error = 1;
                            break;
                        }
                    }  

                    if ($error == 0) {

                        /*Generar movimientos kardex*/
                        $sql = 'SELECT id_tra_detalle,id_lote_origen,id_lote_destino,cantidad FROM far_traslado_detalle WHERE id_traslado=' . $id;
                        $rs = $cmd->query($sql);
                        $objs_detalle = $rs->fetchAll();
                        
                        foreach ($objs_detalle as $obj_det) {
                            $id_detalle = $obj_det['id_tra_detalle'];
                            $id_lote_origen = $obj_det['id_lote_origen'];
                            $id_lote_destino = $obj_det['id_lote_destino'];
                            $cantidad = $obj_det['cantidad'];

                            /* Valores del Lote Origen */
                            $sql = 'SELECT existencia,val_promedio,id_med FROM far_medicamento_lote WHERE id_lote=' . $id_lote_origen . ' LIMIT 1';
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $id_medicamento = $obj['id_med'];
                            $val_promedio_lote = $obj['val_promedio'] ? $obj['val_promedio'] : 0;
                            $existencia_lote = $obj['existencia'];

                            /* Valores del Medicamento */
                            $sql = 'SELECT existencia,val_promedio FROM far_medicamentos WHERE id_med=' . $id_medicamento . ' LIMIT 1';                        
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $val_promedio_med = $obj['val_promedio'] ? $obj['val_promedio'] : 0;
                            $existencia_med = $obj['existencia'];

                            $existencia_lote_kdx = $existencia_lote - $cantidad;
                            $existencia_med_kdx = $existencia_med - $cantidad;

                            /* Genera el egreso de la bodega origen */
                            $sql = "INSERT INTO far_kardex(id_lote,fec_movimiento,id_egreso_tra,id_sede,id_bodega,id_egr_tra_detalle,detalle,can_egreso,existencia_lote,val_promedio_lote,id_med,existencia,val_promedio,estado) 
                                    VALUES($id_lote_origen,'$fec_movimiento',$id,$id_sede_origen,$id_bodega_origen,$id_detalle,'$detalle',$cantidad,$existencia_lote_kdx,$val_promedio_lote,$id_medicamento,$existencia_med_kdx,$val_promedio_med,1)";
                            $rs1 = $cmd->query($sql);
        
                            $sql = "UPDATE far_medicamento_lote SET existencia=$existencia_lote_kdx WHERE id_lote=" . $id_lote_origen;
                            $rs2 = $cmd->query($sql);
        
                            $sql = "UPDATE far_traslado_detalle SET valor=$val_promedio_med WHERE id_tra_detalle=" . $id_detalle;
                            $rs3 = $cmd->query($sql);

                            /* Genera el ingreso de la bodega destino */    
                            $sql = 'SELECT existencia,val_promedio FROM far_medicamento_lote WHERE id_lote=' . $id_lote_destino . ' LIMIT 1';
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            $val_promedio_lote = $obj['val_promedio'] ? $obj['val_promedio'] : 0;
                            $existencia_lote = $obj['existencia'];

                            $valor_promedio_lote_kdx = $val_promedio_lote;
                            $existencia_lote_kdx = $existencia_lote + $cantidad;
                            
                            if ($existencia_lote_kdx > 0) {
                                $valor_promedio_lote_kdx = ($val_promedio_lote * $existencia_lote + $cantidad * $val_promedio_med) / $existencia_lote_kdx;
                            }

                            $sql = "INSERT INTO far_kardex(id_lote,fec_movimiento,id_ingreso_tra,id_sede,id_bodega,id_ing_tra_detalle,detalle,can_ingreso,val_ingreso,existencia_lote,val_promedio_lote,id_med,existencia,val_promedio,estado) 
                                    VALUES($id_lote_destino,'$fec_movimiento',$id,$id_sede_destino,$id_bodega_destino,$id_detalle,'$detalle',$cantidad,$val_promedio_med,$existencia_lote_kdx ,$valor_promedio_lote_kdx,$id_medicamento,$existencia_med,$val_promedio_med,1)";
                            $rs4 = $cmd->query($sql);

                            $sql = "UPDATE far_medicamento_lote SET existencia=$existencia_lote_kdx,val_promedio=$valor_promedio_lote_kdx WHERE id_lote=" . $id_lote_destino;
                            $rs5 = $cmd->query($sql);

                            if ($rs1 == false || $rs2 == false || $rs3 == false || $rs4 == false || $rs5 == false || error_get_last()) {
                                $error = 1;
                                break;
                            }
                        }
                    }    
                    if ($error == 0) {
                        $sql = 'SELECT num_trasladoactual FROM tb_datos_ips LIMIT 1';
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $num_traslado = $obj['num_trasladoactual'];
                        $res['num_traslado'] = $num_traslado;

                        $sql = "UPDATE far_traslado SET num_traslado=$num_traslado,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope',val_total=(SELECT SUM(valor*cantidad) FROM far_traslado_detalle WHERE id_traslado=$id) WHERE id_traslado= $id";
                        $rs1 = $cmd->query($sql);
                        $sql = 'UPDATE tb_datos_ips SET num_trasladoactual=num_trasladoactual+1';
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
                    $res['mensaje'] = $respuesta;
                }    
            } else {
                if ($estado != 1) {
                    $res['mensaje'] = 'Solo puede Cerrar Traslados en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'El Traslado no tiene detalles';
                } else if ($num_reg_kardex > 0) {
                    $res['mensaje'] = 'El Traslado ya tiene registro de movimientos en Kardex';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_traslado WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $estado = $obj_tra['estado'];

            if ($estado == 2) {
                $cmd->beginTransaction();
                    
                    $sql = "UPDATE far_traslado SET id_usr_anula=$id_usr_ope,fec_anulacion='$fecha_ope',estado=0 WHERE id_traslado=$id";
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $sql = 'UPDATE far_kardex SET estado=0 WHERE id_ingreso_tra=' . $id;
                        $rs = $cmd->query($sql);
                    }
                    if ($rs) {
                        $sql = 'UPDATE far_kardex SET estado=0 WHERE id_egreso_tra=' . $id;
                        $rs = $cmd->query($sql);
                    }
                    if ($rs){
                        /* Llama a la funcion recalcular kardex */
                        $sql = "SELECT CONCAT(GROUP_CONCAT(id_lote_origen),',',GROUP_CONCAT(id_lote_destino)) AS lotes
                                FROM far_traslado_detalle WHERE id_traslado=" . $id;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $lotes = $obj['lotes'];

                        recalcular_kardex($cmd,$lotes,'T','','',$id,'','');
                    }
                    if ($rs) {
                        $cmd->commit();
                        $res['mensaje'] = 'ok';
                        $accion = 'Anular';
                        $opcion = 'Traslado';
                        $detalle = 'Anulo Traslado Id: ' . $id;
                        bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                    } else {
                        $cmd->rollBack();
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
            } else {
                $res['mensaje'] = 'Solo puede Anular Traslados en estado Cerrado';
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
