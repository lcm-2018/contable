<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permite crear botones en la cuadricula si tiene permisos de 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5003, 2) && $oper == 'add' && $_POST['id_pedido'] == -1) ||
        (PermisosUsuario($permisos, 5003, 3) && $oper == 'add' && $_POST['id_pedido'] != -1) ||
        (PermisosUsuario($permisos, 5003, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5003, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5003, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_pedido'];
            $fec_pedido = $_POST['txt_fec_pedido'];
            $hor_pedido = $_POST['txt_hor_pedido'];
            $detalle = $_POST['txt_det_pedido']; //detalle pedido

            //Verifica si los datos estas activos o bloqueados en el formulario
            if (isset($_POST['sl_sede_proveedor'])){
                $id_sede_origen = $_POST['sl_sede_proveedor'];
                $id_bodega_origen = $_POST['sl_bodega_proveedor'];
                $id_sede_destino = $_POST['sl_sede_solicitante'];
                $id_bodega_destino = $_POST['sl_bodega_solicitante'];
            }else{
                $sql = "SELECT id_sede_origen,id_bodega_origen,id_sede_destino,id_bodega_destino FROM far_pedido WHERE id_pedido=" . $id;
                $rs = $cmd->query($sql);
                $obj_pedido = $rs->fetch();
                $id_sede_origen = $obj_pedido['id_sede_origen'];    
                $id_bodega_origen = $obj_pedido['id_bodega_origen'];                
                $id_sede_destino = $obj_pedido['id_sede_destino'];    
                $id_bodega_destino = $obj_pedido['id_bodega_destino'];                
            }

            if ($id == -1) {
                if($id_bodega_origen != $id_bodega_destino){
                    $sql = "INSERT INTO far_pedido(fec_pedido,hor_pedido,detalle,id_sede_origen,id_bodega_origen,
                            id_sede_destino,id_bodega_destino,val_total,id_usr_crea,fec_creacion,estado) 
                        VALUES('$fec_pedido','$hor_pedido','$detalle',$id_sede_origen,$id_bodega_origen,
                            $id_sede_destino,$id_bodega_destino,0,$id_usr_ope,'$fecha_ope',1)";
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
                $sql = "SELECT estado FROM far_pedido WHERE id_pedido=" . $id;
                $rs = $cmd->query($sql);
                $obj_pedido = $rs->fetch();

                if ($obj_pedido['estado'] == 1) {
                    $sql = "UPDATE far_pedido SET detalle='$detalle',id_sede_origen=$id_sede_origen,id_bodega_origen=$id_bodega_origen,id_sede_destino=$id_sede_destino,id_bodega_destino=$id_bodega_destino
                            WHERE id_pedido=" . $id;
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Pedidos en estado Pendiente';
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM far_pedido WHERE id_pedido=" . $id;
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();

            if ($obj_pedido['estado'] == 1) {
                $sql = "DELETE FROM far_pedido WHERE id_pedido=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Pedidos en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM far_pedido WHERE id_pedido=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $estado = isset($obj_pedido['estado']) ? $obj_pedido['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_pedido_detalle WHERE id_pedido=" . $id;
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $num_detalles = $obj_pedido['total'];

            if ($estado == 1 && $num_detalles > 0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = 'SELECT num_pedidoactual FROM tb_datos_ips LIMIT 1';
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();
                $num_pedido = $obj['num_pedidoactual'];
                $res['num_pedido'] = $num_pedido;

                $sql = "UPDATE far_pedido SET num_pedido=$num_pedido,estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_pedido=$id";
                $rs1 = $cmd->query($sql);
                $sql = 'UPDATE tb_datos_ips SET num_pedidoactual=num_pedidoactual+1';
                $rs2 = $cmd->query($sql);

                if ($rs1 == false || $rs2 == false || error_get_last()) {
                    $error = 1;
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
                    $res['mensaje'] = 'Solo puede Cerrar Pedidos en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'El Pedido no tiene detalles';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM far_pedido WHERE id_pedido=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $estado = $obj_pedido['estado'];

            $sql = 'SELECT COUNT(*) AS total FROM far_pedido_detalle
                    INNER JOIN far_traslado_detalle ON (far_traslado_detalle.id_ped_detalle = far_pedido_detalle.id_ped_detalle) 
                    INNER JOIN far_traslado ON (far_traslado.id_traslado = far_traslado_detalle.id_traslado)
                    WHERE far_pedido_detalle.id_pedido=' . $id . ' AND far_traslado.estado>=1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $det_traslado = $obj_pedido['total'];

            if ($estado == 2 && $det_traslado == 0) {
                $sql = "UPDATE far_pedido SET id_usr_anula=$id_usr_ope,fec_anulacion='$fecha_ope',estado=0 WHERE id_pedido=$id";
                $rs = $cmd->query($sql);
                if ($rs == false) {
                    $error = $cmd->errorInfo();
                    $res['mensaje'] = 'Error en base de datos-far_pedido:' . $error[2];
                } else {
                    $res['mensaje'] = 'ok';
                }
            } else {
                if ($estado != 2) {
                    $res['mensaje'] = 'Solo se puede anular pedidos en estado cerrado.<br/>';
                } else if ($det_traslado >= 1) {
                    $msg = 'El Pedido ya tiene registros de entrega en un Traslado';
                }
                $res['mensaje'] = $msg;
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
