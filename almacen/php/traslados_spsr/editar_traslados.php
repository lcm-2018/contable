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

    if ((PermisosUsuario($permisos, 5017, 2) && $oper == 'add' && $_POST['id_traslado'] == -1) ||
        (PermisosUsuario($permisos, 5017, 3) && $oper == 'add' && $_POST['id_traslado'] != -1) ||
        (PermisosUsuario($permisos, 5017, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5017, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5017, 3) && $oper == 'send') ||
        (PermisosUsuario($permisos, 5017, 5) && $oper == 'annul' || $id_rol == 1)
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
                    $sql = "INSERT INTO far_traslado_r(fec_traslado,hor_traslado,tipo,id_ingreso,id_sede_origen,id_bodega_origen,
                                id_sede_destino,id_bodega_destino,detalle,val_total,id_usr_crea,fec_creacion,estado)
                            VALUES('$fec_traslado','$hor_traslado',$tip_traslado,$id_ingreso,$id_sede_origen,$id_bodega_origen,
                                $id_sede_destino,$id_bodega_destino,'$detalle',0,$id_usr_ope,'$fecha_ope',1)";
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
                    $sql = "SELECT estado FROM far_traslado_r WHERE id_traslado=" . $id;
                    $rs = $cmd->query($sql);
                    $obj_tra = $rs->fetch();

                    if ($obj_tra['estado'] == 1) {
                        $sql = "UPDATE far_traslado_r 
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
                                            FROM far_traslado_r_detalle AS TRD
                                            INNER JOIN far_traslado_r AS TR ON (TR.id_traslado=TRD.id_traslado)
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
                            $sql = "INSERT INTO far_traslado_r_detalle(id_traslado,id_lote_origen,cantidad,valor,id_ped_detalle) 
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
                        $sql = "INSERT INTO far_traslado_r_detalle(id_traslado,id_lote_origen,cantidad,valor) 
                                SELECT $id_traslado,ID.id_lote,(ID.cantidad*PC.cantidad),FM.val_promedio
                                FROM far_orden_ingreso_detalle AS ID
                                INNER JOIN far_presentacion_comercial AS PC ON (PC.id_prescom=ID.id_presentacion)
                                INNER JOIN far_medicamento_lote AS ML ON (ML.id_lote=ID.id_lote)
                                INNER JOIN far_medicamentos AS FM ON (FM.id_med = ML.id_med) 
                                WHERE ID.id_ingreso=" . $id_ingreso;
                        $rs = $cmd->query($sql);                            
                    }    

                    $sql = "UPDATE far_traslado_r SET val_total=(SELECT SUM(valor*cantidad) FROM far_traslado_r_detalle WHERE id_traslado=$id_traslado) WHERE id_traslado=$id_traslado";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM far_traslado_r WHERE id_traslado=" . $id_traslado;
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

            $sql = "SELECT estado FROM far_traslado_r WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();

            if ($obj_tra['estado'] == 1) {
                $sql = "DELETE FROM far_traslado_r WHERE id_traslado=" . $id;
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

        if ($oper == 'close') { // Egreso de la Bodega principal
            $id = $_POST['id'];
            $sql = "SELECT estado FROM far_traslado_r WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $estado = isset($obj_tra['estado']) ? $obj_tra['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_traslado_r_detalle WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $num_detalles = $obj_tra['total'];

            $sql = "SELECT COUNT(*) AS total FROM far_kardex WHERE id_egreso_tra_r=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $num_reg_kardex = $obj_tra['total'];

            if ($estado == 1 && $num_detalles > 0 && $num_reg_kardex == 0) {
                $respuesta = verificar_existencias($cmd, $id, "TR");

                if ($respuesta == 'ok') {

                    $error = 0;
                    $cmd->beginTransaction();

                    $sql = 'SELECT far_traslado_r.id_sede_origen,far_traslado_r.id_bodega_origen,far_traslado_r_detalle.id_tra_detalle,far_traslado_r.detalle,
                                far_traslado_r_detalle.id_lote_origen,far_traslado_r_detalle.cantidad
                            FROM far_traslado_r_detalle 
                            INNER JOIN far_traslado_r ON (far_traslado_r.id_traslado = far_traslado_r_detalle.id_traslado) 
                            WHERE far_traslado_r_detalle.id_traslado=' . $id;
                    $rs = $cmd->query($sql);
                    $objs_detalles = $rs->fetchAll();

                    foreach ($objs_detalles as $obj_det) {
                        $id_sede = $obj_det['id_sede_origen'];
                        $id_bodega = $obj_det['id_bodega_origen'];
                        $detalle = $obj_det['detalle'];
                        $fec_movimiento = date('Y-m-d');

                        $id_detalle = $obj_det['id_tra_detalle'];
                        $id_lote = $obj_det['id_lote_origen'];
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
                        $sql = "INSERT INTO far_kardex(id_lote,fec_movimiento,id_egreso_tra_r,id_sede,id_bodega,detalle,can_egreso,existencia_lote,val_promedio_lote,id_med,existencia,val_promedio,estado) 
                                VALUES($id_lote,'$fec_movimiento',$id,$id_sede,$id_bodega,'$detalle',$cantidad,$existencia_lote_kdx,$val_promedio_lote,$id_medicamento,$existencia_med_kdx,$val_promedio_med,1)";
                        $rs1 = $cmd->query($sql);
    
                        $sql = "UPDATE far_medicamento_lote SET existencia=$existencia_lote_kdx WHERE id_lote=" . $id_lote;
                        $rs2 = $cmd->query($sql);
    
                        $sql = "UPDATE far_medicamentos SET existencia=$existencia_med_kdx WHERE id_med=" . $id_medicamento;
                        $rs3 = $cmd->query($sql);
    
                        $sql = "UPDATE far_traslado_r_detalle SET valor=$val_promedio_med WHERE id_tra_detalle=" . $id_detalle;
                        $rs4 = $cmd->query($sql);

                        if ($rs1 == false || $rs2 == false || $rs3 == false || $rs4 == false || error_get_last()) {
                            $error = 1;
                            break;
                        }
                    }
                    if ($error == 0) {
                        $sql = 'SELECT num_trasladoactual FROM tb_datos_ips LIMIT 1';
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $num_traslado = $obj['num_trasladoactual'];
                        $res['num_traslado'] = $num_traslado;

                        $sql = "UPDATE far_traslado_r SET num_traslado=$num_traslado,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope',val_total=(SELECT SUM(valor*cantidad) FROM far_traslado_r_detalle WHERE id_traslado=$id) WHERE id_traslado= $id";
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
                    $res['mensaje'] = 'Solo puede Cerrar-Egresar Traslados SPSR en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'El Traslado SPSR no tiene detalles';
                } else if ($num_reg_kardex > 0) {
                    $res['mensaje'] = 'El Traslado SPSR ya tiene registro de movimientos en Kardex';
                }
            }            
        }

        if ($oper == 'send') {
            $id = $_POST['id'];

            $sql = "SELECT * FROM far_traslado_r WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $estado = $obj_tra['estado'];
            
            if ($estado == 2) {
                $id_sede_destino = $obj_tra['id_sede_destino'];
                $sql = "SELECT ip_sede,bd_sede,pw_sede,us_sede,pt_http FROM tb_sedes WHERE id_sede=$id_sede_destino LIMIT 1";
                $rs = $cmd->query($sql);
                $obj_sede = $rs->fetch();
                
                $ip_pr = explode(':', $obj_sede['ip_sede']);
                $ip = $ip_pr[0];
                $port = $ip_pr[1];
                $database = $obj_sede['bd_sede'];
                $password = $obj_sede['pw_sede'];
                $user = $obj_sede['us_sede'];
                $continuar = true;

                // Verifica conexión a servicio remoto
                if (!isHostReachable($ip) && $continuar) {
                    $res['mensaje'] = "Error: No hay respuesta de la IP del servidor ($ip). Verifique la red.";
                    $continuar = false;
                } 
                if (!isMySQLPortOpen($ip, $port) && $continuar) {
                    $res['mensaje'] = "Error: El servidor MySQL no responde en $ip:$port. Verifique el servicio.";
                    $continuar = false;
                } 
                if($continuar){
                    list($ok, $msg) = canConnectToDatabase($ip, $port, $user, $password, $database);
                    if (!$ok) {
                        $res['mensaje'] = "Error: No se pudo conectar a la base de datos '$database' en $ip:$port.<br>Detalle: $msg";
                        $continuar = false;
                    }    
                }    

                if ($continuar){
                    $cmd1 = new PDO("$bd_driver:host=$ip;port=$port;dbname=$database;$charset", $user, $password);

                    // Verificar los medicamentos y lotes que existan en la sede remota
                    $sql = "SELECT far_medicamento_lote.id_med,far_medicamentos.cod_medicamento,far_medicamentos.nom_medicamento,
                                far_medicamento_lote.id_lote,far_medicamento_lote.lote,
                                far_traslado_r_detalle.cantidad,far_traslado_r_detalle.valor
                            FROM far_traslado_r_detalle
                            INNER JOIN far_medicamento_lote ON (far_medicamento_lote.id_lote=far_traslado_r_detalle.id_lote_origen)
                            INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
                            WHERE far_traslado_r_detalle.id_traslado=" . $id;
                    $rs = $cmd->query($sql);
                    $objs_med = $rs->fetchAll(PDO::FETCH_ASSOC);

                    $no_existe = "";
                    foreach ($objs_med as $k => $med) {                                                
                        $sql = "SELECT far_medicamento_lote.id_lote
                                FROM far_medicamento_lote 
                                INNER JOIN far_medicamentos ON (far_medicamentos.id_med=far_medicamento_lote.id_med)
                                WHERE far_medicamentos.id_origen=" . $med['id_med'] . " AND far_medicamento_lote.id_origen=" . $med['id_lote'];
                        $rs = $cmd1->query($sql);
                        $obj = $rs->fetch();

                        if(!$obj || !$obj['id_lote']) {                              
                            $no_existe .= '</br>Lote: ' . $med['lote'] . ' Medicamento: ' . $med['cod_medicamento'] . '-' . $med['nom_medicamento'];
                        } else {
                            $objs_med[$k]['id_lote_destino'] = $obj['id_lote'];
                        }
                    }
                    if ($no_existe <> ''){
                        $res['mensaje'] = "Medicamentos y Lotes no existen en la sede remota:$no_existe";
                    } else {
                        //Crea el traslado en la sede remota
                        $cmd1->beginTransaction();
                        
                        // Consulta el usuario qen la sede remota
                        $sql = "SELECT id_usuario FROM seg_usuarios_sistema WHERE id_origen=" . $id_usr_ope;
                        $rs = $cmd1->query($sql);
                        $obj = $rs->fetch();
                        $id_usr = isset($obj['id_usuario']) ? $obj['id_usuario'] : 1;

                        // Insertar el encabezado del traslado
                        $sql = "INSERT INTO far_traslado_r(fec_traslado,hor_traslado,id_sede_origen,id_bodega_origen,
                                id_sede_destino,id_bodega_destino,detalle,val_total,id_usr_envia,fec_envio,id_traslado_origen,estado,estado2)
                                VALUES(:fec_traslado,:hor_traslado,:id_sede_origen,:id_bodega_origen,
                                :id_sede_destino,:id_bodega_destino,:detalle,:val_total,:id_usr_envia,:fec_envio,:id_traslado_origen,:estado,:estado2)";
                        $sql = $cmd1->prepare($sql);
                        $sql->bindValue(':fec_traslado', $obj_tra['fec_traslado']);
                        $sql->bindValue(':hor_traslado', $obj_tra['hor_traslado']);
                        $sql->bindValue(':id_sede_origen', $obj_tra['id_sede_origen'], PDO::PARAM_INT);
                        $sql->bindValue(':id_bodega_origen', $obj_tra['id_bodega_origen'], PDO::PARAM_INT);
                        $sql->bindValue(':id_sede_destino', $obj_tra['id_sede_destino'], PDO::PARAM_INT);
                        $sql->bindValue(':id_bodega_destino', $obj_tra['id_bodega_destino'], PDO::PARAM_INT);
                        $sql->bindValue(':detalle', $obj_tra['detalle']);
                        $sql->bindValue(':val_total', $obj_tra['val_total']);
                        $sql->bindValue(':id_usr_envia', $id_usr);
                        $sql->bindValue(':fec_envio', $fecha_ope);
                        $sql->bindValue(':id_traslado_origen', $id);
                        $sql->bindValue(':estado', 3);
                        $sql->bindValue(':estado2', 1);
                        $rs = $sql->execute();    

                        if ($rs) {
                            $id_r = $cmd1->lastInsertId();

                            foreach ($objs_med as $med) {                                                
                                $sql = "INSERT INTO far_traslado_r_detalle(id_traslado,id_lote_destino,cantidad,valor)
                                        VALUES(:id_traslado,:id_lote_destino,:cantidad,:valor)";
                                $sql = $cmd1->prepare($sql);
                                $sql->bindValue(':id_traslado', $id_r);
                                $sql->bindValue(':id_lote_destino', $med['id_lote_destino'], PDO::PARAM_INT);
                                $sql->bindValue(':cantidad', $med['cantidad'], PDO::PARAM_INT);
                                $sql->bindValue(':valor', $med['valor']);                                
                                $rs = $sql->execute(); 
                                if (!$rs) break;
                            }
                        }

                        if ($rs) {
                            $sql = "UPDATE far_traslado_r SET estado=3,estado2=1,id_usr_envia=$id_usr_ope,fec_envio='$fecha_ope' WHERE id_traslado= $id";
                            $rs = $cmd->query($sql);
                        }

                        if ($rs) {
                            $cmd1->commit();
                            $res['mensaje'] = 'ok';
                            $accion = 'Enviar';
                            $opcion = 'Traslado SPSR ';
                            $detalle = 'Enviado Traslado SPSR Id: ' . $id;
                            bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                        } else {
                            $cmd1->rollBack();
                            $res['mensaje'] = $cmd->errorInfo()[2];
                        }
                    }
                }
            } else {
                $res['mensaje'] = 'Solo puede Enviar Traslados SPSR en estado Cerrado';
            }    
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_traslado_r WHERE id_traslado=" . $id;
            $rs = $cmd->query($sql);
            $obj_tra = $rs->fetch();
            $estado = $obj_tra['estado'];

            if ($estado == 2) {

                $cmd->beginTransaction();

                $sql = "UPDATE far_traslado_r 
                        INNER JOIN far_kardex ON(far_kardex.id_egreso_tra_r = far_traslado_r.id_traslado)
                        SET far_traslado_r.id_usr_anula=$id_usr_ope,far_traslado_r.fec_anulacion='$fecha_ope',far_traslado_r.estado=0,far_kardex.estado=0 
                        WHERE far_traslado_r.id_traslado=$id";
                $rs = $cmd->query($sql);

                if ($rs) {
                    $sql = "SELECT GROUP_CONCAT(id_lote_origen) AS lotes
                            FROM far_traslado_r_detalle WHERE id_traslado=" . $id;
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $lotes = $obj['lotes'];

                    recalcular_kardex($cmd, $lotes, 'ER', '', '', '', '', $id, '', '');
                }
                if ($rs) {
                    $cmd->commit();
                    $res['mensaje'] = 'ok';
                    $accion = 'Anular';
                    $opcion = 'Traslado SPSR ';
                    $detalle = 'Anulo Traslado SPSR Id: ' . $id;
                    bitacora($accion, $opcion, $detalle, $id_usr_ope, $_SESSION['user']);
                } else {
                    $cmd->rollBack();
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Anular Traslados SPSR en estado Cerrado';
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
