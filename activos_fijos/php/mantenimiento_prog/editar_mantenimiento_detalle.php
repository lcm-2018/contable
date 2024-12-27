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

    if ((PermisosUsuario($permisos, 5706, 2) && $oper == 'add') || 
        (PermisosUsuario($permisos, 5706, 3) && $oper == 'close') || $id_rol == 1) {

        $id = isset($_POST['id_mant_detalle']) ? $_POST['id_mant_detalle'] : -1;

        $sql = "SELECT estado,id_activo_fijo FROM acf_mantenimiento_detalle WHERE id_mant_detalle=" . $id;
        $rs = $cmd->query($sql);
        $obj_man = $rs->fetch();

        if (in_array($obj_man['estado'], [1, 2])) {
            if ($oper == 'add') {
                $sql = "UPDATE acf_mantenimiento_detalle 
                        SET observacion_mant=:observacion_mant,estado_fin_mant=:estado_fin_mant,observacion_fin_mant=:observacion_fin_mant,estado=:estado 
                        WHERE id_mant_detalle=:id_mant_detalle";
                $sql = $cmd->prepare($sql);
                
                $sql->bindValue(':observacion_mant', $_POST['txt_observacio_mant'], PDO::PARAM_STR);
                $sql->bindValue(':estado_fin_mant', $_POST['sl_estado_general'] ? $_POST['sl_estado_general'] : null, PDO::PARAM_INT);                
                $sql->bindValue(':observacion_fin_mant', $_POST['txt_observacio_fin_mant'], PDO::PARAM_STR);
                $sql->bindValue(':estado', 2, PDO::PARAM_INT);
                $sql->bindValue(':id_mant_detalle', $id, PDO::PARAM_INT);                    
                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }    
            
            if ($oper == 'close') {
                $sql = "UPDATE acf_mantenimiento_detalle 
                        SET observacion_mant=:observacion_mant,estado_fin_mant=:estado_fin_mant,observacion_fin_mant=:observacion_fin_mant,
                            estado=:estado,id_usr_finaliza=:id_usr_finaliza,fec_finaliza=:fec_finaliza
                        WHERE id_mant_detalle=:id_mant_detalle";
                $sql = $cmd->prepare($sql);
                
                $sql->bindValue(':observacion_mant', $_POST['txt_observacio_mant'], PDO::PARAM_STR);
                $sql->bindValue(':estado_fin_mant', $_POST['sl_estado_general'], PDO::PARAM_INT);
                $sql->bindValue(':observacion_fin_mant', $_POST['txt_observacio_fin_mant'], PDO::PARAM_STR);
                $sql->bindValue(':estado', 3, PDO::PARAM_INT);
                $sql->bindParam(':id_usr_finaliza', $id_usr_ope, PDO::PARAM_INT);
                $sql->bindParam(':fec_finaliza', $fecha_ope, PDO::PARAM_STR);
                $sql->bindValue(':id_mant_detalle', $id, PDO::PARAM_INT); 
                $updated = $sql->execute();

                if ($updated) {                    
                    //Actualiza el estado general del activo fijo
                    $sql = "UPDATE acf_hojavida SET estado_general=:estado_general,causa_est_general=:causa_est_general WHERE id_activo_fijo=:id_activo_fijo";
                    $sql = $cmd->prepare($sql);
                    $sql->bindValue(':estado_general', $_POST['sl_estado_general'], PDO::PARAM_INT);
                    $sql->bindValue(':causa_est_general',$_POST['txt_observacio_fin_mant'], PDO::PARAM_STR);
                    $sql->bindValue(':id_activo_fijo', $obj_man['id_activo_fijo'], PDO::PARAM_INT);                            
                    $updated = $sql->execute();
                }

                if ($updated) {                    
                    //Actualiza el estado=Inactivo cuando estado general=Fuera de servicio
                    if($_POST['sl_estado_general'] == 4){
                        $sql = "UPDATE acf_hojavida SET fecha_fuera_servicio=:fecha_fuera_servicio,estado=:estado WHERE id_activo_fijo=:id_activo_fijo";
                        $sql = $cmd->prepare($sql);
                        $sql->bindValue(':fecha_fuera_servicio',date('Y-m-d'), PDO::PARAM_STR);
                        $sql->bindValue(':estado', 4);
                        $sql->bindValue(':id_activo_fijo', $obj_man['id_activo_fijo'], PDO::PARAM_INT);                            
                        $updated = $sql->execute();
                    }   
                }

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            } 
        } else {
            $res['mensaje'] = 'El Procesos de Mantenimiento ya esta Finalizado';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }
    
    $cmd = null;

} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
