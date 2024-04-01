<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5003, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5003, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5003, 4) && $oper == 'del') || $id_rol == 1) {

        $id_pedido = $_POST['id_pedido'];

        if ($id_pedido > 0) {

            $sql = "SELECT estado FROM far_pedido WHERE id_pedido=" . $id_pedido;
            $rs = $cmd->query($sql);
            $obj_pedido = $rs->fetch();            
          
            if ($obj_pedido['estado'] == 1) {
                if ($oper == 'add') {
                    $id = $_POST['id_detalle'];        
                    $id_med = $_POST['id_txt_nom_med'];                  
                    $cantidad = $_POST['txt_can_ped'] ? $_POST['txt_can_ped'] : 1;                    
                    $valor = $_POST['txt_val_pro'] ? $_POST['txt_val_pro'] : 0;
                   
                    if ($id == -1) {   
                        $sql = "SELECT COUNT(*) AS count FROM far_pedido_detalle WHERE id_pedido=$id_pedido AND id_medicamento=" . $id_med;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        if ($obj['count'] == 0) {
                            $sql = "INSERT INTO far_pedido_detalle(id_pedido,id_medicamento,cantidad,valor)
                                    VALUES($id_pedido,$id_med ,$cantidad,$valor)";
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
                            $res['mensaje'] = 'El Artículo ya existe en los detalles del Pedido';    
                        }    
                    } else {
                        $sql = "UPDATE far_pedido_detalle SET cantidad=$cantidad WHERE id_ped_detalle=" . $id;
                        $rs = $cmd->query($sql);
                        if ($rs) {
                            $res['mensaje'] = 'ok';
                            $res['id'] = $id;
                        } else {
                            $res['mensaje'] = $cmd->errorInfo()[2];
                        }
                    }
                }

                if ($oper == 'del') {
                    $id = $_POST['id'];
                    $sql = "DELETE FROM far_pedido_detalle WHERE id_ped_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }

                if ($res['mensaje'] == 'ok') {
                    $sql = "UPDATE far_pedido SET val_total=(SELECT SUM(valor*cantidad) FROM far_pedido_detalle WHERE id_pedido=$id_pedido) WHERE id_pedido=$id_pedido";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM far_pedido WHERE id_pedido=" . $id_pedido;
                    $rs = $cmd->query($sql);
                    $obj_pedido = $rs->fetch();
                    $res['val_total'] = formato_valor($obj_pedido['val_total']);
                }
            } else {
                $res['mensaje'] = 'Solo puede Modificar Pedidos en estado Pendiente';
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar el Pedido';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
