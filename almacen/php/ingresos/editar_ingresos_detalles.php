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

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5006, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') || $id_rol == 1
    ) {

        $id_ingreso = $_POST['id_ingreso'];

        if ($id_ingreso > 0) {

            $sql = "SELECT estado FROM far_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
            $rs = $cmd->query($sql);
            $obj_ingreso = $rs->fetch();

            if ($obj_ingreso['estado'] == 1) {
                if ($oper == 'add') {
                    $id = $_POST['id_detalle'];
                    $id_lote = $_POST['id_txt_nom_lot'];
                    $id_pre_lot = $_POST['id_txt_pre_lot'];
                    $cantidad = $_POST['txt_can_ing'] ? $_POST['txt_can_ing'] : 1;
                    $vr_unidad = $_POST['txt_val_uni'] ? $_POST['txt_val_uni'] : 0;
                    $iva = $_POST['sl_por_iva'] ? $_POST['sl_por_iva'] : 0;
                    $vr_costo = $_POST['txt_val_cos'];
                    $observacion = $_POST['txt_observacion'];

                    if ($id == -1) {
                        $sql = "SELECT COUNT(*) AS existe FROM far_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso AND id_lote=" . $id_lote;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();

                        if ($obj['existe'] == 0) {
                            $sql = "INSERT INTO far_orden_ingreso_detalle(id_ingreso,id_lote,id_presentacion,cantidad,valor_sin_iva,iva,valor,observacion)
                                    VALUES($id_ingreso,$id_lote,$id_pre_lot,$cantidad,$vr_unidad,$iva,$vr_costo,'$observacion')";
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
                            $res['mensaje'] = 'El Lote ya existe en los detalles de la Orden de Ingreso';
                        }
                    } else {
                        $sql = "UPDATE far_orden_ingreso_detalle 
                            SET id_presentacion=$id_pre_lot,cantidad=$cantidad,valor_sin_iva=$vr_unidad,iva=$iva,valor=$vr_costo,observacion='$observacion'
                            WHERE id_ing_detalle=" . $id;

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
                    $sql = "DELETE FROM far_orden_ingreso_detalle WHERE id_ing_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }

                if ($rs) {
                    $sql = "UPDATE far_orden_ingreso SET val_total=(SELECT SUM(valor*cantidad) FROM far_orden_ingreso_detalle WHERE id_ingreso=$id_ingreso) WHERE id_ingreso=$id_ingreso";
                    $rs = $cmd->query($sql);

                    $sql = "SELECT val_total FROM far_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
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
