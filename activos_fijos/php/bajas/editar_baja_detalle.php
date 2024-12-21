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

    if ((PermisosUsuario($permisos, 5703, 2) && $oper == 'add' && $_POST['id_baja_detalle'] == -1) 
        || (PermisosUsuario($permisos, 5703, 3) && $oper == 'add' && $_POST['id_baja_detalle'] != -1) 
        || ($oper == 'del') 
        || $id_rol == 1
    ) {

        $id = isset($_POST['id_baja_detalle']) ? $_POST['id_baja_detalle'] : -1;

        if ($oper == 'add') {
            if ($id == -1) {
                
                $sql = "INSERT INTO acf_baja_detalle (
                    id_baja, 
                    id_activo_fijo, 
                    observacion_baja
                ) VALUES (
                    :id_baja, 
                    :id_activo_fijo, 
                    :observacion_baja
                )";
        
                $sql = $cmd->prepare($sql);
                
                $sql->bindParam(':id_baja', $_POST['id_baja'], PDO::PARAM_INT);
                $sql->bindParam(':id_activo_fijo', $_POST['id_txt_activo_fijo'], PDO::PARAM_INT);
                $sql->bindParam(':observacion_baja', $_POST['observacion_baja'], PDO::PARAM_STR);
             
                $inserted = $sql->execute();

                if ($inserted) {
                    $id = $cmd->lastInsertId();
                    $res['mensaje'] = 'ok';
                    $res['id_baja_detalle'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }   
            } else {
            
                $sql = "UPDATE `acf_baja_detalle` 
                        SET 
                            `id_activo_fijo` = :id_activo_fijo, 
                            `observacion_baja` = :observacion_baja
                        WHERE `id_baja_detalle` = :id_baja_detalle";

                $sql = $cmd->prepare($sql);

                $sql->bindParam(':id_activo_fijo', $_POST['id_txt_activo_fijo'], PDO::PARAM_INT);
                $sql->bindParam(':observacion_baja', $_POST['observacion_baja']);
                $sql->bindParam(':id_baja_detalle', $_POST['id_baja_detalle'], PDO::PARAM_INT);
                
                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_baja_detalle'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }
        }

        if ($oper == 'del') {
            $sql = "SELECT estado FROM acf_baja_detalle WHERE id_detalle_baja=" . $id;
            $rs = $cmd->query($sql);
            $obj = $rs->fetch();

            if ($obj['estado'] == 1) {
                $sql = "DELETE FROM acf_baja_detalle WHERE id_detalle_baja=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            } else {
                $res['mensaje'] = 'Solo puede Borrar Ordenes de Mantenimiento en estado Pendiente' ;
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
