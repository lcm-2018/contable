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

    if ((PermisosUsuario($permisos, 5007, 2) && $oper == 'add' && $_POST['id_egreso'] == -1) ||
        (PermisosUsuario($permisos, 5007, 3) && $oper == 'add' && $_POST['id_egreso'] != -1) ||
        (PermisosUsuario($permisos, 5007, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5007, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5007, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_egreso'];            
            $fec_egr = $_POST['txt_fec_egr'];
            $hor_egr = $_POST['txt_hor_egr'];
            $id_tipegr = $_POST['sl_tip_egr'];
            $id_tercero = $_POST['sl_tercero'] ? $_POST['sl_tercero'] : 0;
            $id_cencosto = $_POST['sl_centrocosto'] ? $_POST['sl_centrocosto'] : 0;
            $id_area = $_POST['sl_area'] ? $_POST['sl_area'] : 0;
            $id_sede = $_POST['id_sede_egr'];
            $id_bodega = $_POST['id_bodega_egr'];                
            $detalle = $_POST['txt_det_egr'];
            
            $cmd->beginTransaction();

            if ($id == -1) {
                $sql = "INSERT INTO far_orden_egreso(fec_egreso,hor_egreso,id_tipo_egreso,
                        id_cliente,id_centrocosto,id_area,detalle,val_total,id_sede,id_bodega,id_usr_crea,fec_creacion,creado_far,estado)
                    VALUES('$fec_egr','$hor_egr',$id_tipegr,
                        $id_tercero,$id_cencosto,$id_area,'$detalle',0,$id_sede,$id_bodega,$id_usr_ope,'$fecha_ope',0,1)";
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
                $sql = "SELECT estado FROM far_orden_egreso WHERE id_egreso=" . $id;
                $rs = $cmd->query($sql);
                $obj_egreso = $rs->fetch();

                if ($obj_egreso['estado'] == 1) {
                    $sql = "UPDATE far_orden_egreso 
                        SET id_tipo_egreso=$id_tipegr,id_cliente=$id_tercero,id_centrocosto=$id_cencosto,id_area=$id_area,detalle='$detalle',id_sede=$id_sede,id_bodega=$id_bodega
                        WHERE id_egreso=" . $id;
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Ordenes de Egreso en estado Pendiente';
                }
            }

            //Generar el traslado en base al pedido
            $generar_egreso = $_POST['generar_egreso'];                

            if ($res['mensaje'] == 'ok' && $generar_egreso == 1){

                $id_pedido = $_POST['txt_id_pedido'];
                $id_egreso = $res['id'];

                $sql = 'SELECT PD.id_ped_detalle,PD.id_medicamento,PD.cantidad,
                            FM.val_promedio,FM.cod_medicamento,FM.nom_medicamento
                        FROM far_cec_pedido_detalle AS PD
                        INNER JOIN far_medicamentos AS FM ON (FM.id_med = PD.id_medicamento) 
                        WHERE PD.id_pedido=' . $id_pedido;
                $rs = $cmd->query($sql);
                $objs = $rs->fetchAll();

                $fec_actual = date('Y-m-d');
                $sql = "SELECT id_lote,existencia 
                        FROM far_medicamento_lote 
                        WHERE id_med=:id_med AND existencia>=0 AND id_bodega=$id_bodega AND estado=1 AND fec_vencimiento>='$fec_actual' 
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
                            $res['mensaje'] = 'Los Artículos no tienen lotes disponibles para generar el Egreso: ' . $obj['cod_medicamento'] . '-' . $obj['nom_medicamento'];
                        } else {
                            $res['mensaje'] .= ', ' . $obj['cod_medicamento'] . '-' . $obj['nom_medicamento'];
                        }    
                    }
                }                    

                if ($res['mensaje'] == 'ok'){
                    $sql = "INSERT INTO far_orden_egreso_detalle(id_egreso,id_lote,cantidad,valor,id_ped_detalle) 
                            VALUES (:id_egreso,:id_lote,:cantidad,:val_promedio,:id_detalle)";
                    $rs2 = $cmd->prepare($sql);
                    foreach ($lotes as $lt) {
                        if ($lt['cantidad'] > 0) {
                            $rs2->bindParam(':id_egreso', $id_egreso);
                            $rs2->bindParam(':id_lote', $lt['id_lote']);
                            $rs2->bindParam(':cantidad', $lt['cantidad']);
                            $rs2->bindParam(':val_promedio', $lt['val_promedio']);
                            $rs2->bindParam(':id_detalle', $lt['id_detalle']);
                            $rs2->execute();
                        }
                    }

                    $sql = "UPDATE far_orden_egresso SET val_total=(SELECT SUM(valor*cantidad) FROM far_orden_egreso_detalle WHERE id_egreso=$id_egreso) WHERE id_egreso=$id_egreso";
                    $rs = $cmd->query($sql);
                }    
            }

            if ($res['mensaje'] == 'ok'){
                $cmd->commit();
            } else {
                $cmd->rollBack();
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_egreso WHERE id_egreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_egreso = $rs->fetch();

            if ($obj_egreso['estado'] == 1) {
                $sql = "DELETE FROM far_orden_egreso WHERE id_egreso=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Egreso en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_egreso WHERE id_egreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_egreso = $rs->fetch();
            $estado = isset($obj_egreso['estado']) ? $obj_egreso['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_orden_egreso_detalle WHERE id_egreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_egreso = $rs->fetch();
            $num_detalles = $obj_egreso['total'];

            $sql = "SELECT COUNT(*) AS total FROM far_kardex WHERE id_egreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_egreso = $rs->fetch();
            $num_reg_kardex = $obj_egreso['total'];

            if ($estado == 1 && $num_detalles > 0 && $num_reg_kardex == 0) {
                $respuesta = verificar_existencias($cmd, $id, "E");

                if ($respuesta == 'ok') {

                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = 'SELECT far_orden_egreso.id_sede,far_orden_egreso.id_bodega,far_orden_egreso_detalle.id_egr_detalle,far_orden_egreso.detalle,
                                far_orden_egreso_detalle.id_lote,far_orden_egreso_detalle.cantidad
                            FROM far_orden_egreso_detalle 
                            INNER JOIN far_orden_egreso ON (far_orden_egreso.id_egreso = far_orden_egreso_detalle.id_egreso) 
                            WHERE far_orden_egreso_detalle.id_egreso=' . $id;
                    $rs = $cmd->query($sql);
                    $objs_detalles = $rs->fetchAll();

                    foreach ($objs_detalles as $obj_det) {
                        $id_sede = $obj_det['id_sede'];
                        $id_bodega = $obj_det['id_bodega'];
                        $detalle = $obj_det['detalle'];
                        $fec_movimiento = date('Y-m-d');

                        $id_detalle = $obj_det['id_egr_detalle'];
                        $id_lote = $obj_det['id_lote'];
                        $cantidad = $obj_det['cantidad'];

                        /* Valores del Lote */
                        $sql = 'SELECT existencia,val_promedio,id_med FROM far_medicamento_lote WHERE id_lote=' . $id_lote . ' LIMIT 1';
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $id_medicamento = $obj['id_med'];
                        $val_promedio_lote = $obj['val_promedio'];
                        $existencia_lote = $obj['existencia'];

                        /* Valores del Medicamento */
                        $sql = 'SELECT existencia,val_promedio FROM far_medicamentos WHERE id_med=' . $id_medicamento . ' LIMIT 1';                        
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $val_promedio_med = $obj['val_promedio'];
                        $existencia_med = $obj['existencia'];

                        $existencia_lote_kdx = $existencia_lote - $cantidad;
                        $existencia_med_kdx = $existencia_med - $cantidad;

                        /* Inserta registros en kardex de estaod=1-activo */
                        $sql = "INSERT INTO far_kardex(id_lote,fec_movimiento,id_egreso,id_sede,id_bodega,id_egr_detalle,detalle,can_egreso,existencia_lote,val_promedio_lote,id_med,existencia,val_promedio,estado) 
                                VALUES($id_lote,'$fec_movimiento',$id,$id_sede,$id_bodega,$id_detalle,'$detalle',$cantidad,$existencia_lote_kdx,$val_promedio_lote,$id_medicamento,$existencia_med_kdx,$val_promedio_med,1)";
                        $rs1 = $cmd->query($sql);
    
                        $sql = "UPDATE far_medicamento_lote SET existencia=$existencia_lote_kdx WHERE id_lote=" . $id_lote;
                        $rs2 = $cmd->query($sql);
    
                        $sql = "UPDATE far_medicamentos SET existencia=$existencia_med_kdx WHERE id_med=" . $id_medicamento;
                        $rs3 = $cmd->query($sql);
    
                        $sql = "UPDATE far_orden_egreso_detalle SET valor=$val_promedio_med WHERE id_egr_detalle=" . $id_detalle;
                        $rs4 = $cmd->query($sql);

                        if ($rs1 == false || $rs2 == false || $rs3 == false || $rs4 == false || error_get_last()) {
                            $error = 1;
                            break;
                        }
                    }
                    if ($error == 0) {
                        $sql = 'SELECT num_egresoactual FROM tb_datos_ips LIMIT 1';
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $num_egreso = $obj['num_egresoactual'];
                        $res['num_egreso'] = $num_egreso;

                        $sql = "UPDATE far_orden_egreso SET num_egreso=$num_egreso,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope',val_total=(SELECT SUM(valor*cantidad) FROM far_orden_egreso_detalle WHERE id_egreso=$id) WHERE id_egreso= $id";
                        $rs1 = $cmd->query($sql);
                        $sql = 'UPDATE tb_datos_ips SET num_egresoactual=num_egresoactual+1';
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
                    $res['mensaje'] = 'Solo puede Cerrar Ordenes de Egreso en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'La Ordenes de Egreso no tiene detalles';
                } else if ($num_reg_kardex > 0) {
                    $res['mensaje'] = 'La Orden de Egreso ya tiene registro de movimientos en Kardex';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_orden_egreso WHERE id_egreso=" . $id;
            $rs = $cmd->query($sql);
            $obj_egreso = $rs->fetch();
            $estado = $obj_egreso['estado'];

            if ($estado == 2) {

                $cmd->beginTransaction();

                $sql = "UPDATE far_orden_egreso 
                        INNER JOIN far_kardex ON(far_kardex.id_egreso = far_orden_egreso.id_egreso)
                        SET far_orden_egreso.id_usr_anula=$id_usr_ope,far_orden_egreso.fec_anulacion='$fecha_ope',far_orden_egreso.estado=0,far_kardex.estado=0 
                        WHERE far_orden_egreso.id_egreso=$id";
                $rs = $cmd->query($sql);

                if ($rs) {
                    $sql = "SELECT GROUP_CONCAT(id_lote) AS lotes
                                FROM far_orden_egreso_detalle WHERE id_egreso=" . $id;
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $lotes = $obj['lotes'];

                    recalcular_kardex($cmd, $lotes, 'E', '', $id, '', '', '');
                }
                if ($rs) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                    $accion = 'Anular';
                    $opcion = 'Orden de Egreso';
                    $detalle = 'Anulo Orden Egreso Id: ' . $id;
                    bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                } else {
                    $cmd->rollBack();
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Anular Ordenes de Egreso en estado Cerrado';
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
