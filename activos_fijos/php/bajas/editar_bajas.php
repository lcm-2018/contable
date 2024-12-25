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

    if ((PermisosUsuario($permisos, 5709, 2) && $oper == 'add' && $_POST['id_baja'] == -1) ||
        (PermisosUsuario($permisos, 5709, 3) && $oper == 'add' && $_POST['id_baja'] != -1) ||
        (PermisosUsuario($permisos, 5709, 4) && $oper == 'del') ||
        (PermisosUsuario($permisos, 5709, 3) && $oper == 'close') ||
        (PermisosUsuario($permisos, 5709, 5) && $oper == 'annul' || $id_rol == 1)
    ) {

        if ($oper == 'add') {
            $id = $_POST['id_baja'];
            $fec_orden = $_POST['txt_fec_orden'];
            $hor_orden = $_POST['txt_hor_orden'];            
            $observaciones = $_POST['txt_obs_baja'];

            if ($id == -1) {                
                $sql = "INSERT INTO acf_baja(fec_orden,hor_orden,observaciones,id_usr_crea,fec_crea,estado) 
                    VALUES('$fec_orden','$hor_orden','$observaciones',$id_usr_ope,'$fecha_ope',1)";
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
                $sql = "SELECT estado FROM acf_baja WHERE id_baja=" . $id;
                $rs = $cmd->query($sql);
                $obj_baja = $rs->fetch();

                if ($obj_baja['estado'] == 1) {
                    $sql = "UPDATE acf_baja SET observaciones='$observaciones'
                            WHERE id_baja=" . $id;
                    $rs = $cmd->query($sql);

                    if ($rs) {
                        $res['mensaje'] = 'ok';
                        $res['id'] = $id;
                    } else {
                        $res['mensaje'] = $cmd->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Solo puede Modificar Ordenes de Baja en estado Pendiente';
                }
            }    
        }

        if ($oper == 'del') {
            $id = $_POST['id'];

            $sql = "SELECT estado FROM acf_baja WHERE id_baja=" . $id;
            $rs = $cmd->query($sql);
            $obj_baja = $rs->fetch();

            if ($obj_baja['estado'] == 1) {
                $sql = "DELETE FROM acf_baja WHERE id_baja=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Baja en estado Pendiente';
            }
        }

        if ($oper == 'close') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_baja WHERE id_baja=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_baja = $rs->fetch();
            $estado = isset($obj_baja['estado']) ? $obj_baja['estado'] : -1;

            $sql = "SELECT COUNT(*) AS total FROM acf_baja_detalle WHERE id_baja=" . $id;
            $rs = $cmd->query($sql);
            $obj_baja = $rs->fetch();
            $num_detalles = $obj_baja['total'];

            if ($estado == 1 && $num_detalles > 0) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_baja SET estado=2,id_usr_cierre=$id_usr_ope,fec_cierre='$fecha_ope' WHERE id_baja=$id";
                $rs1 = $cmd->query($sql);

                $sql = "UPDATE acf_hojavida SET estado=5
                        WHERE id_activo_fijo IN (SELECT id_activo_fijo FROM acf_baja_detalle WHERE id_baja=$id)";
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
                    $res['mensaje'] = 'Solo puede Cerrar Ordenes de Bajas en estado Pendiente';
                } else if ($num_detalles == 0) {
                    $res['mensaje'] = 'La Orden de Baja no tiene detalles';
                }
            }
        }

        if ($oper == 'annul') {
            $id = $_POST['id'];

            $sql = 'SELECT estado FROM acf_baja WHERE id_baja=' . $id . ' LIMIT 1';
            $rs = $cmd->query($sql);
            $obj_baja = $rs->fetch();
            $estado = $obj_baja['estado'];

            if ($estado == 2) {
                $error = 0;
                $cmd->beginTransaction();

                $sql = "UPDATE acf_baja SET estado=0,id_usr_anula=$id_usr_ope,fec_anula='$fecha_ope' WHERE id_baja=$id";
                $rs1 = $cmd->query($sql);

                $sql = "UPDATE acf_hojavida SET estado=4
                        WHERE id_activo_fijo IN (SELECT id_activo_fijo FROM acf_baja_detalle WHERE id_baja=$id)";
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
                $res['mensaje'] = 'Solo se puede anular Ordenes de Baja en estado cerrado';
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
