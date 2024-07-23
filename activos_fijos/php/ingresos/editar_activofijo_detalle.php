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

        $sql = "SELECT estado FROM acf_orden_ingreso WHERE id_ingreso=" . $id_ingreso;
        $rs = $cmd->query($sql);
        $obj_ingreso = $rs->fetch();

        if ($obj_ingreso['estado'] == 1) {
            if ($oper == 'add') {
                $id = $_POST['id_act_fijo'];
                $id_art = $_POST['id_articulo'];
                $id_ing_det = $_POST['id_ing_detalle'];
                $placa = $_POST['txt_placa'];
                $serial = $_POST['txt_serial'];
                $id_marca = $_POST['sl_marca'];
                $valor = $_POST['txt_val_uni'] ? $_POST['txt_val_uni'] : 0;
                $tip_act = $_POST['sl_tipoactivo'];

                if ($id == -1) {   
                    
                    $sql = "SELECT acf_orden_ingreso_detalle.cantidad,COUNT(acf_orden_ingreso_acfs.id_act_fij) AS registros
                            FROM acf_orden_ingreso_detalle
                            LEFT JOIN acf_orden_ingreso_acfs ON (acf_orden_ingreso_acfs.id_ing_detalle=acf_orden_ingreso_detalle.id_ing_detalle)
                            WHERE acf_orden_ingreso_detalle.id_ing_detalle=" . $id_ing_det;
                    $rs = $cmd->query($sql);
                    $obj = $rs->fetch();
                    $cantidad = $obj['cantidad'];
                    if ($obj['cantidad'] > $obj['registros']){
                        $sql = "INSERT INTO acf_orden_ingreso_acfs(id_ing_detalle,id_articulo,placa,serial,id_marca,valor,tipo_activo)
                                VALUES($id_ing_det,$id_art,'$placa','$serial',$id_marca,$valor,$tip_act)";
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
                        $res['mensaje'] = 'No puede registrar una Cantidad de Activos Fijos mayor a ' . $cantidad;
                    }    
                } else {
                    $sql = "UPDATE acf_orden_ingreso_acfs 
                            SET placa='$placa',serial='$serial',id_marca=$id_marca,valor=$valor,tipo_activo=$tip_act
                            WHERE id_act_fij=" . $id;

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
                $sql = "DELETE FROM acf_orden_ingreso_acfs WHERE id_act_fij=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }            
        } else {
            $res['mensaje'] = 'Solo puede Modificar Ordenes de Ingreso en estado Pendiente';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
