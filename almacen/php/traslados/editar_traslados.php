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
        (PermisosUsuario($permisos, 5008, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5008, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_traslado'];
            $fec_traslado = $_POST['txt_fec_traslado'];
            $hor_traslado = $_POST['txt_hor_traslado'];
            $tip_traslado = $_POST['id_tip_traslado'] ? $_POST['id_tip_traslado'] : 0;
            $id_ingreso = $_POST['txt_id_ingreso'] ? $_POST['txt_id_ingreso'] : 'NULL';
            $id_sede_origen = $_POST['id_sede_origen'];
            $id_bodega_origen = $_POST['id_bodega_origen'];
            $id_sede_destino = $_POST['id_sede_destino'];
            $id_bodega_destino = $_POST['id_bodega_destino'];
            $detalle = $_POST['txt_det_traslado'];

            if($id_bodega_origen != $id_bodega_destino){        
                
                $cmd->beginTransaction();

                if ($id == -1) {                
                    $sql = "INSERT INTO far_traslado(fec_traslado,hor_traslado,tipo,id_ingreso,id_sede_origen,id_bodega_origen,
                            id_sede_destino,id_bodega_destino,detalle,val_total,id_usr_crea,fec_creacion,creado_far,estado)
                        VALUES('$fec_traslado','$hor_traslado',$tip_traslado,$id_ingreso,$id_sede_origen,$id_bodega_origen,$id_sede_destino,$id_bodega_destino,'$detalle',0,$id_usr_ope,'$fecha_ope',0,1)";
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
                    $sql = "SELECT estado FROM far_traslado WHERE id_traslado=" . $id;
                    $rs = $cmd->query($sql);
                    $obj_tra = $rs->fetch();

                    if ($obj_tra['estado'] == 1) {
                        $sql = "UPDATE far_traslado 
                            SET tipo=$tip_traslado,id_ingreso=$id_ingreso,id_sede_origen=$id_sede_origen,id_bodega_origen=$id_bodega_origen,
                                id_sede_destino=$id_sede_destino,id_bodega_destino=$id_bodega_destino,detalle='$detalle'
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

                //Generar el traslado en base al pedido o el ingreso
                //1-Traslado en base a un pedido de bodega, 2-Traslado total del una orden de ingreso
                $generar_traslado = $_POST['generar_traslado'];                

                if ($res['mensaje'] == 'ok' && ($generar_traslado == 1 || $generar_traslado == 2)){

                    $id_traslado = $res['id'];

                    if ($generar_traslado == 1){

                        $id_pedido = $_POST['txt_id_pedido'];
                        $sql = "SELECT far_pedido_detalle.id_ped_detalle,far_pedido_detalle.id_medicamento,
                                        far_pedido_detalle.cantidad-IFNULL(TRASLADO.cantidad,0) AS cantidad,
                                        far_medicamentos.val_promedio,
                                        far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento	
                                    FROM far_pedido_detalle 
                                    INNER JOIN far_medicamentos ON (far_medicamentos.id_med = far_pedido_detalle.id_medicamento) 
                                    LEFT JOIN (SELECT TRD.id_ped_detalle,SUM(TRD.cantidad) AS cantidad     
                                            FROM far_traslado_detalle AS TRD
                                            INNER JOIN far_traslado AS TR ON (TR.id_traslado=TRD.id_traslado)
                                            WHERE TR.estado<>0 AND TRD.id_ped_detalle IS NOT NULL
                                            GROUP BY TRD.id_ped_detalle
                                        ) AS TRASLADO ON (TRASLADO.id_ped_detalle=far_pedido_detalle.id_ped_detalle) 
                                    WHERE far_pedido_detalle.cantidad>IFNULL(TRASLADO.cantidad,0) AND far_pedido_detalle.id_pedido=" . $id_pedido;
                        $rs = $cmd->query($sql);
                        $objs = $rs->fetchAll();

                        $fec_actual = date('Y-m-d');
                        $sql = "SELECT id_lote,existencia 
                                FROM far_medicamento_lote 
                                WHERE id_med=:id_med AND existencia>=0 AND id_bodega=$id_bodega_origen AND estado=1 AND fec_vencimiento>='$fec_actual' 
                                ORDER BY fec_vencimiento,existencia";
                        $rs1 = $cmd->prepare($sql);

                        $lotes = array();
                        foreach ($objs as $obj) {
                            $rs1->bindParam(':id_med', $obj['id_medicamento']);
                            $rs1->execute();
                            $obj_lotes = $rs1->fetchAll();
                            $cantidad = $obj['cantidad'];
                            $val_promedio = $obj['val_promedio'];
                            $id_detalle = $obj['id_ped_detalle'];

                            if (count($obj_lotes) >= 1) {
                                $i = 0;
                                while ($cantidad >= 1) {
                                    if (!isset($obj_lotes[$i])) {
                                        break;
                                    }
                                    $id_lote = $obj_lotes[$i]['id_lote'];
                                    $cantidad_lote = $obj_lotes[$i]['existencia'];

                                    $q = 0;
                                    if ($cantidad_lote >= $cantidad) {
                                        $q = $cantidad;
                                        $cantidad = 0;
                                    } else {
                                        $q = $cantidad_lote;
                                        $cantidad = $cantidad - $cantidad_lote;
                                    }
                                    $lotes[] = array('id_lote' => $id_lote, 'cantidad' => (int) $q, 'val_promedio' => $val_promedio, 'id_detalle' => $id_detalle);
                                    $i++;
                                }

                                if ($cantidad >= 1) {/* Completar la cantidad cuando ya no hay mas lotes en el ultimo lote encontrado */
                                    $index = count($lotes) - 1;
                                    $id_lote = $lotes[$index]['id_lote'];
                                    $q = $lotes[$index]['cantidad'] + $cantidad;
                                    $lotes[$index] = array('id_lote' => $id_lote, 'cantidad' => (int) $q, 'val_promedio' => $val_promedio, 'id_detalle' => $id_detalle);
                                }
                            } else {
                                if ($res['mensaje'] == 'ok'){
                                    $res['mensaje'] = 'Los Artículos no tienen lotes disponibles para generar el traslado: ' . $obj['cod_medicamento'] . '-' . $obj['nom_medicamento'];
                                } else {
                                    $res['mensaje'] .= ', ' . $obj['cod_medicamento'] . '-' . $obj['nom_medicamento'];
                                }    
                            }
                        }

                        if ($res['mensaje'] == 'ok'){
                            $sql = "INSERT INTO far_traslado_detalle(id_traslado,id_lote_origen,cantidad,valor,id_ped_detalle) 
                                    VALUES (:id_traslado,:id_loteorigen,:cantidad,:val_promedio,:id_detalle)";
                            $rs2 = $cmd->prepare($sql);
                            foreach ($lotes as $lt) {
                                if ($lt['cantidad'] > 0) {
                                    $rs2->bindParam(':id_traslado', $id_traslado);
                                    $rs2->bindParam(':id_loteorigen', $lt['id_lote']);
                                    $rs2->bindParam(':cantidad', $lt['cantidad']);
                                    $rs2->bindParam(':val_promedio', $lt['val_promedio']);
                                    $rs2->bindParam(':id_detalle', $lt['id_detalle']);
                                    $rs2->execute();
                                }
                            }    
                        }  

                    } else if($generar_traslado == 2){

                        $id_ingreso = $_POST['txt_id_ingreso'];
                        $sql = "INSERT INTO far_traslado_detalle(id_traslado,id_lote_origen,cantidad,valor) 
                                SELECT $id_traslado,ID.id_lote,(ID.cantidad*PC.cantidad),FM.val_promedio
                                FROM far_orden_ingreso_detalle AS ID
                                INNER JOIN far_presentacion_comercial AS PC ON (PC.id_prescom=ID.id_presentacion)
                                INNER JOIN far_medicamento_lote AS ML ON (ML.id_lote=ID.id_lote)
                                INNER JOIN far_medicamentos AS FM ON (FM.id_med = ML.id_med) 
                                WHERE ID.id_ingreso=" . $id_ingreso;
                        $rs = $cmd->query($sql);                            
                    }    

                    $sql = "UPDATE far_traslado SET val_total=(SELECT SUM(valor*cantidad) FROM far_traslado_detalle WHERE id_traslado=$id_traslado) WHERE id_traslado=$id_traslado";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM far_traslado WHERE id_traslado=" . $id_traslado;
                    $rs = $cmd->query($sql);
                    $obj_traslado = $rs->fetch();
                    $res['val_total'] = formato_valor($obj_traslado['val_total']);                    
                }

                if ($res['mensaje'] == 'ok'){
                    $cmd->commit();
                } else {
                    $cmd->rollBack();
                }

            }else {
                $res['mensaje'] = 'La Bodega que Solicita y la Bodega Proveedora deben ser diferentes';    
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
                $respuesta = verificar_kardex($cmd, $id, "T");

                if ($respuesta == 'ok') {
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
                    $res['mensaje'] = $respuesta;
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
