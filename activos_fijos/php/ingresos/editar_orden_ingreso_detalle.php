<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir
include '../common/funciones_generales.php';

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = date('Y-m-d H:i:s');
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5703, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5703, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5703, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_ingreso = $_POST['id_ingreso'];

        if ($id_ingreso > 0) {

            $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();

            if ($obj_ingreso['estado'] == 1) {
                if ($oper == 'add') {
                    $id = $_POST['id_detalle'];
                    $id_art = $_POST['id_txt_nom_art'];
                    $cantidad = $_POST['txt_can_ing'] ? $_POST['txt_can_ing'] : 1;
                    $vr_unidad = $_POST['txt_val_uni'] ? $_POST['txt_val_uni'] : 0;
                    $iva = $_POST['sl_por_iva'] ? $_POST['sl_por_iva'] : 0;
                    $vr_costo = $_POST['txt_val_cos'];
                    $observacion = $_POST['txt_observacion'];

                    if ($id == -1) {
                        $sql = "SELECT COUNT(*) AS existe FROM acf_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso AND id_articulo=" . $id_art;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();

                        if ($obj['existe'] == 0) {
                            $sql = "INSERT INTO acf_orden_ingreso_detalle(id_ingreso,id_articulo,observacion,cantidad,valor_sin_iva,iva,valor)
                                    VALUES($id_ingreso,$id_art,'$observacion',$cantidad,$vr_unidad,$iva,$vr_costo)";
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
                            $res['mensaje'] = 'El activo ya existe en los detalles de la Orden de Ingreso';
                        }
                    } else {
                        $sql = "SELECT COUNT(*) AS cantidad FROM acf_orden_ingreso_acfs WHERE id_ing_detalle=" . $id;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();

                        if ($cantidad >= $obj['cantidad']){
                            $sql = "UPDATE acf_orden_ingreso_detalle 
                                    SET cantidad=$cantidad,valor_sin_iva=$vr_unidad,iva=$iva,valor=$vr_costo,observacion='$observacion'
                                    WHERE id_ing_detalle=" . $id;

                            $rs = $cmd->query($sql);
                            if ($rs) {
                                $res['mensaje'] = 'ok';
                                $res['id'] = $id;
                            } else {
                                $res['mensaje'] = $cmd->errorInfo()[2];
                            }
                        } else {
                            $res['mensaje'] = 'La Cantidad no debe ser inferior al número de Activos Fijos registrados';    
                        }    
                    }
                }

                if ($oper == 'del') {
                    $id = $_POST['id'];
                    $sql = "DELETE FROM acf_orden_ingreso_detalle WHERE id_ing_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }

                if ($rs) {
                    $sql = "UPDATE acf_orden_ingreso SET val_total=(SELECT IFNULL(SUM(valor * cantidad), 0)  
                            FROM acf_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso) WHERE id_ingreso=$id_ingreso";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM acf_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
                    $rs = $cmd->query($sql);
                    $obj_ingreso = $rs->fetch();
                    $res['val_total'] = formato_valor($obj_ingreso['val_total']);
                }
            } else {
                $res['mensaje'] = 'Solo puede Modificar Ordenes de Ingreso en estado Pendiente';
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar la Orden de Ingreso';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
