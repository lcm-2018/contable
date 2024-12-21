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
$fecha_ope = date('Y-m-d H:i:s');
$id_usr_ope = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5705, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5705, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5705, 4) && $oper == 'del') || $id_rol == 1) {

        $id_mantenimiento = $_POST['id_mantenimiento'];

        if ($id_mantenimiento > 0) {

            $sql = "SELECT estado FROM acf_mantenimiento WHERE id_mantenimiento=" . $id_mantenimiento;
            $rs = $cmd->query($sql);
            $obj_mantenimiento = $rs->fetch();            
          
            if ($obj_mantenimiento['estado'] == 1) {
                if ($oper == 'add') {
                    $id = $_POST['id_mant_detalle'];        
                    $id_activo_fijo = $_POST['id_txt_actfij'];                  
                    $observacion = $_POST['txt_observaciones'];                    
                   
                    if ($id == -1) {   
                        $sql = "SELECT COUNT(*) AS count FROM acf_mantenimiento_detalle WHERE id_mantenimiento=$id_mantenimiento AND id_activo_fijo=$id_activo_fijo";
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        if ($obj['count'] == 0) {
                            $sql = "INSERT INTO acf_mantenimiento_detalle(id_mantenimiento,id_activo_fijo,observacion_mant,estado)
                                    VALUES($id_mantenimiento,$id_activo_fijo ,'$observacion',1)";
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
                            $res['mensaje'] = 'El Activo Fijo ya existe en los detalles del la Orden de Mantenimiento';    
                        }    
                    } else {
                        $sql = "UPDATE acf_mantenimiento_detalle SET observacion_mant='$observacion' WHERE id_mant_detalle=" . $id;
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
                    $sql = "DELETE FROM acf_mantenimiento_detalle WHERE id_mant_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }
            } else {
                $res['mensaje'] = 'Solo puede Modificar Ordenes de Mantenimiento en estado Pendiente';
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar la Orden de Mantenimiento';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
