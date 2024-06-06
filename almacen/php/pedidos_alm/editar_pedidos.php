<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
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

    if ((PermisosUsuario($permisos, 5005, 2) && $oper == 'add' && $_POST['id_pedido'] == -1) ||
        (PermisosUsuario($permisos, 5005, 3) && $oper == 'add' && $_POST['id_pedido'] != -1) ||
        (PermisosUsuario($permisos, 5005, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5005, 2) && PermisosUsuario($permisos, 5005, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5005, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_pedido'];
            $id_bodega = $_POST['id_txt_nom_bod'];
            $id_sede = $_POST['id_txt_sede'];
            $fec_ped = $_POST['txt_fec_ped'];
            $hor_ped = $_POST['txt_hor_ped'];            
            $detalle = $_POST['txt_det_ped'];

            if ($id == -1) {
                $sql = "INSERT INTO far_alm_pedido(tipo,fec_pedido,hor_pedido,detalle,val_total,id_sede,id_bodega,id_usr_crea,fec_creacion,estado)
                    VALUES(1,'$fec_ped','$hor_ped','$detalle',0,$id_sede,$id_bodega,$id_usr_ope,'$fecha_ope',1)";
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
                $sql = "SELECT estado FROM far_alm_pedido WHERE id_pedido=" . $id;
                $rs = $cmd->query($sql);
                $obj_pedido = $rs->fetch();

                if ($obj_pedido['estado'] == 1) {
                    $sql = "UPDATE far_alm_pedido SET detalle='$detalle'  WHERE id_pedido=" . $id;
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

            $sql = "SELECT estado FROM far_alm_pedido WHERE id_pedido=" . $id;
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();

            if ($obj_pedido['estado'] == 1) {
                $sql = "DELETE FROM far_alm_pedido WHERE id_pedido=" . $id;
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

        if ($oper == 'conf') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM far_alm_pedido WHERE id_pedido=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $estado = isset($obj_pedido['estado']) ? $obj_pedido['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM far_alm_pedido_detalle WHERE id_pedido=" . $id;
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $num_detalles = $obj_pedido['total'];

            if ($estado == 1 && $num_detalles > 0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = 'SELECT num_pedidoactual_alm FROM tb_datos_ips LIMIT 1';
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();
                $num_pedido = $obj['num_pedidoactual_alm'];
                $res['num_pedido'] = $num_pedido;

                $sql = "UPDATE far_alm_pedido SET num_pedido=$num_pedido,estado=2,id_usr_confirma=$id_usr_ope,fec_confirma='$fecha_ope' WHERE id_pedido=$id";
                $rs1 = $cmd->query($sql);
                $sql = 'UPDATE tb_datos_ips SET num_pedidoactual_alm=num_pedidoactual_alm+1';
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
                    $res['mensaje'] = 'Solo puede Confirmar Pedidos en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'El Pedido no tiene detalles';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM far_alm_pedido WHERE id_pedido=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $estado = $obj_pedido['estado'];

            if ($obj_pedido['estado'] == 2) {
                $sql = "UPDATE far_alm_pedido SET id_usr_anula=$id_usr_ope,fec_anulacion='$fecha_ope',estado=0 WHERE id_pedido=$id";
                $rs = $cmd->query($sql);
                if ($rs == false) {
                    $error = $cmd->errorInfo();
                    $res['mensaje'] = 'Error en base de datos-far_alm_pedido:' . $error[2];
                } else {
                    $res['mensaje'] = 'ok';
                }
            } else {
                $res['mensaje'] = 'Solo se puede Anular Pedidos en estado Confirmado.<br/>';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM far_alm_pedido WHERE id_pedido=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();
            $estado = $obj_pedido['estado'];

            if ($obj_pedido['estado'] == 3) {
                $sql = "UPDATE far_alm_pedido SET id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope',estado=4 WHERE id_pedido=$id";
                $rs = $cmd->query($sql);
                if ($rs == false) {
                    $error = $cmd->errorInfo();
                    $res['mensaje'] = 'Error en base de datos-far_alm_pedido:' . $error[2];
                } else {
                    $res['mensaje'] = 'ok';
                }
            } else {
                $res['mensaje'] = 'Solo se puede Cerrar Pedidos en estado Aceptado.<br/>';
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
