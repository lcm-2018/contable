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

    if ((PermisosUsuario($permisos, 5708, 2) && $oper == 'add' && $_POST['id_detalle'] == -1) ||
        (PermisosUsuario($permisos, 5708, 3) && $oper == 'add' && $_POST['id_detalle'] != -1) ||
        (PermisosUsuario($permisos, 5708, 4) && $oper == 'del') || $id_rol == 1) {

        $id_traslado = $_POST['id_traslado'];
        $id_area = isset($_POST['id_area']) ? $_POST['id_area'] : -1;

        if ($id_traslado > 0) {

            $sql = "SELECT estado,id_area_origen FROM acf_traslado WHERE id_traslado=" . $id_traslado;
            $rs = $cmd->query($sql);
            $obj_traslado = $rs->fetch();            
          
            if ($obj_traslado['estado'] == 1) {
                if ($oper == 'add') {
                    if ($obj_traslado['id_area_origen'] == $id_area) {
                        $id = $_POST['id_detalle'];
                        $id_activo_fijo = $_POST['id_txt_actfij'];
                        $estado_general = $_POST['txt_est_general'];
                        $observacion = $_POST['txt_observacion'];
                    
                        if ($id == -1) {   
                            $sql = "SELECT COUNT(*) AS count FROM acf_traslado_detalle WHERE id_traslado=$id_traslado AND id_activo_fijo=" . $id_activo_fijo;
                            $rs = $cmd->query($sql);
                            $obj = $rs->fetch();
                            if ($obj['count'] == 0) {
                                $sql = "INSERT INTO acf_traslado_detalle(id_traslado,id_activo_fijo,estado_general,observacion)
                                        VALUES($id_traslado,$id_activo_fijo,$estado_general,'$observacion')";
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
                                $res['mensaje'] = 'El Activo Fijo ya existe en los detalles del traslado';    
                            }    
                        } else {
                            $sql = "UPDATE acf_traslado_detalle SET observacion='$observacion' WHERE id_traslado_detalle=" . $id;
                            $rs = $cmd->query($sql);
                            if ($rs) {
                                $res['mensaje'] = 'ok';
                                $res['id'] = $id;
                            } else {
                                $res['mensaje'] = $cmd->errorInfo()[2];
                            }
                        }
                    }else{
                        $res['mensaje'] = 'Primero debe guardar el Traslado para adicionar detalles';  
                    }     
                }

                if ($oper == 'del') {
                    $id = $_POST['id'];
                    $sql = "DELETE FROM acf_traslado_detalle WHERE id_traslado_detalle=" . $id;
                    $rs = $cmd->query($sql);
                    if ($rs) {
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                }

            } else {
                $res['mensaje'] = 'Solo puede Modificar traslados en estado Pendiente';
            }
        } else {
            $res['mensaje'] = 'Primero debe guardar el traslado';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }

    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
