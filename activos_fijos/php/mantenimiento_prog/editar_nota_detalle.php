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
$ruta = '../../documentos/';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5706, 3) && $oper == 'add' || $id_rol == 1)) {
    
        $id_md = $_POST['id_md'];

        if ($id_md > 0) {
            if ($oper == 'add') {
                $id = isset($_POST['id_nota']) ? $_POST['id_nota'] : -1;
                if ($id == -1) {
                    
                    $sql = "INSERT INTO acf_mantenimiento_detalle_nota(id_mant_detalle,fec_nota,hor_nota,observacion,id_usr_crea,fec_crea) 
                            VALUES (:id_mant_detalle,:fec_nota,:hor_nota,:observacion,:id_usr_crea,:fec_crea)";
                    $sql = $cmd->prepare($sql);

                    $sql->bindParam(':id_mant_detalle', $id_md, PDO::PARAM_INT);
                    $sql->bindParam(':fec_nota', $_POST['txt_fec_not'], PDO::PARAM_STR);
                    $sql->bindParam(':hor_nota', $_POST['txt_hor_not'], PDO::PARAM_STR);
                    $sql->bindParam(':observacion', $_POST['txt_observacio_not'], PDO::PARAM_STR);
                    $sql->bindParam(':id_usr_crea', $id_usr_ope, PDO::PARAM_INT);
                    $sql->bindParam(':fec_crea', $fecha_ope, PDO::PARAM_STR);
                    $rs = $sql->execute();

                    if ($rs) {
                        $id = $cmd->lastInsertId();
                        $res['id_nota'] = $id;                        
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } else { 
                    $sql = "UPDATE acf_mantenimiento_detalle_nota SET observacion=:observacion
                            WHERE id_det_nota=:id_det_nota";
                    $sql = $cmd->prepare($sql);

                    $sql->bindValue(':observacion', $_POST['txt_observacio_not'], PDO::PARAM_STR);
                    $sql->bindValue(':id_det_nota', $id, PDO::PARAM_INT);
                    $rs = $sql->execute();

                    if ($rs) {                        
                        $res['id_nota'] = $id;
                        $res['nombre_archivo'] = $_POST['archivo'];
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } 

                if ($rs){                    
                    if ($_POST['del_doc'] == 1 || $_POST['act_doc'] == 1){
                        $sql = "SELECT archivo FROM acf_mantenimiento_detalle_nota WHERE id_det_nota=" . $id;
                        $rs = $cmd->query($sql);
                        $obj = $rs->fetch();
                        $archivo = $obj['archivo'];
                        if ($archivo && file_exists($ruta . $archivo)) {
                            unlink($ruta . $archivo);                    
                        }

                        $sql = "UPDATE acf_mantenimiento_detalle_nota SET archivo=:archivo WHERE id_det_nota=:id_det_nota";
                        $sql = $cmd->prepare($sql);
                        $sql->bindValue(':archivo', '');
                        $sql->bindValue(':id_det_nota', $id, PDO::PARAM_INT);                            
                        $updated = $sql->execute();

                        if ($updated) {
                            $res['nombre_archivo'] = '';
                        } else {
                            $res['mensaje'] = $sql->errorInfo()[2];
                        }
                    }
                    
                    if ($_POST['act_doc'] == 1 && $res['mensaje'] == 'ok'){
                        $fileNombre =  $_FILES["uploadDocAcf"]['name'];
                        $nombre = $id . '_' .  date('Ymd_His') . $fileNombre;
                        $temporal = $_FILES['uploadDocAcf']['tmp_name'];
                        if (!file_exists($ruta)) {
                            mkdir($ruta, 0777, true);
                        }
                        if ((move_uploaded_file($temporal, $ruta . $nombre))) {
                            $sql = "UPDATE acf_mantenimiento_detalle_nota SET archivo=:archivo WHERE id_det_nota=:id_det_nota";
                            $sql = $cmd->prepare($sql);
                            $sql->bindValue(':archivo', $nombre);
                            $sql->bindValue(':id_det_nota', $id, PDO::PARAM_INT);                            
                            $updated = $sql->execute();

                            if ($updated) {
                                $res['nombre_archivo'] = $nombre;
                            } else {
                                $res['mensaje'] = $sql->errorInfo()[2];
                            }
                        } else {
                            $res['mensaje'] = 'Error al Adjuntar el Archivo';
                        }
                    }
                } 
            }

            if ($oper == 'del') {
                $id = $_POST['id'];

                $sql = "SELECT archivo FROM acf_mantenimiento_detalle_nota WHERE id_det_nota=" . $id;
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();
                $archivo = $obj['archivo'];

                $sql = "DELETE FROM acf_mantenimiento_detalle_nota WHERE id_det_nota=" . $id;
                $rs = $cmd->query($sql);
                if ($rs) {
                    if ($archivo && file_exists($ruta . $archivo)) {
                        unlink($ruta . $archivo);
                    }
                    $res['mensaje'] = 'ok';
                } else {
                    $res['mensaje'] = $cmd->errorInfo()[2];
                }
            }

        } else {
            $res['mensaje'] = 'Primero debe guardar El registro de Orden de mantenimiento';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
