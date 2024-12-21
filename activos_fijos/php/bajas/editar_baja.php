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

    if ((PermisosUsuario($permisos, 5703, 2) && $oper == 'add' && $_POST['id_baja'] == -1) 
        || (PermisosUsuario($permisos, 5703, 3) && $oper == 'add' && $_POST['id_baja'] != -1) 
        || ($oper == 'del') 
        || $id_rol == 1
    ) {

        $id = isset($_POST['id_baja']) ? $_POST['id_baja'] : -1;

        if ($oper == 'add') {
            if ($id == -1) {
                
                $sql = "INSERT INTO acf_baja
                    (fecha_orden, hora_orden, observaciones, estado, fecha_baja, usuario_crea, usuario_cierra) 
                    VALUES (:fecha_orden, :hora_orden, :observaciones, :estado, :fecha_baja, :usuario_crea, :usuario_cierra)";

                $sql = $cmd->prepare($sql);

                $PENDIENTE = 1;

                $sql->bindParam(':fecha_orden', $_POST['fecha_orden']);
                $sql->bindParam(':hora_orden', $_POST['hora_orden']);
                $sql->bindParam(':observaciones', $_POST['observaciones']);
                $sql->bindParam(':estado', $PENDIENTE, PDO::PARAM_INT);
                $sql->bindParam(':fecha_baja', $fecha_crea);
                $sql->bindParam(':usuario_crea', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindValue(':usuario_cierra', null, PDO::PARAM_INT);

                $inserted = $sql->execute();

                if ($inserted) {
                    $id = $cmd->lastInsertId();
                    $res['mensaje'] = 'ok';
                    $res['id_baja'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }   
            } else {
            
                $sql = "UPDATE acf_baja SET observaciones = :observaciones WHERE id_baja = :id_baja";

                $sql = $cmd->prepare($sql);
               
                $sql->bindParam(':observaciones', $_POST['observaciones']);
                $sql->bindParam(':id_baja', $id, PDO::PARAM_INT);

                
                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_baja'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }
        }

        if ($oper == 'del') {
            $sql = "SELECT estado FROM acf_baja WHERE id_baja=" . $id;
            $rs = $cmd->query($sql);
            $obj = $rs->fetch();

            if ($obj['estado'] == 1) {
                $sql = "DELETE FROM acf_baja WHERE id_baja=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Baja en estado Pendiente' ;
            }
        }

        if ($oper == 'cerrar') {

            $sql = "UPDATE acf_baja SET estado = :estado WHERE id_baja = :id_baja";

            $CERRADO = 2;
            $sql = $cmd->prepare($sql);
            $sql->bindParam(':estado', $CERRADO, PDO::PARAM_INT);
            $sql->bindParam(':id_baja', $id, PDO::PARAM_INT);
   
            $updated = $sql->execute();

            $sql = "UPDATE acf_hojavida HV INNER JOIN acf_baja_detalle AFB ON HV.id_activo_fijo = AFB.id_activo_fijo
                    SET HV.estado = :estado
                    WHERE AFB.id_baja = :id_baja;";

            $DADO_DE_BAJA = 5;
            $sql = $cmd->prepare($sql);
            $sql->bindParam(':estado', $DADO_DE_BAJA, PDO::PARAM_INT);
            $sql->bindParam(':id_baja', $id, PDO::PARAM_INT);

            $updated = $sql->execute();

            if ($updated) {
                $res['mensaje'] = 'ok';
                $res['id_baja'] = $id;
            } else {
                $res['mensaje'] = $sql->errorInfo()[2];
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
