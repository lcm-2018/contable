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
$ruta = '../../documentos/';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5704, 3) && $oper == 'add' || $id_rol == 1)) {

        $id_hv = $_POST['id_hv'];

        if ($id_hv > 0) {
            if ($oper == 'add') {
                $id = isset($_POST['id_documento']) ? $_POST['id_documento'] : -1;
                if ($id == -1) {
                    
                    $sql = "INSERT INTO acf_hojavida_documentos (id_activo_fijo,tipo,descripcion,id_usr_crea,fec_creacion) 
                            VALUES (:id_activo_fijo,:tipo,:descripcion,:id_usr_crea,:fec_creacion)";
                    $sql = $cmd->prepare($sql);

                    $sql->bindParam(':id_activo_fijo', $id_hv, PDO::PARAM_INT);
                    $sql->bindParam(':tipo', $_POST['tipo'], PDO::PARAM_INT);
                    $sql->bindParam(':descripcion', $_POST['descripcion'], PDO::PARAM_STR);
                    $sql->bindParam(':id_usr_crea', $id_usr_crea, PDO::PARAM_INT);
                    $sql->bindParam(':fec_creacion', $fecha_crea, PDO::PARAM_STR);
                    $rs = $sql->execute();

                    if ($rs) {
                        $id = $cmd->lastInsertId();
                        $res['id_documento'] = $id;                        
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } else { 
                    $sql = "UPDATE acf_hojavida_documentos SET tipo=:tipo,descripcion=:descripcion
                            WHERE id_documento=:id_documento";
                    $sql = $cmd->prepare($sql);

                    $sql->bindValue(':tipo', $_POST['tipo'], PDO::PARAM_INT);
                    $sql->bindValue(':descripcion', $_POST['descripcion'], PDO::PARAM_STR);
                    $sql->bindValue(':id_documento', $id, PDO::PARAM_INT);
                    $rs = $sql->execute();

                    if ($rs) {                        
                        $res['id_documento'] = $id;
                        $res['nombre_archivo'] = $_POST['archivo'];
                        $res['mensaje'] = 'ok';
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } 

                if ($rs){                    
                    if($_POST['nuevo_file'] == 1){
                        if ($_POST['archivo'] && file_exists($ruta . $_POST['archivo'])) {
                            unlink($ruta . $_POST['archivo']);
                        }
                        $fileNombre =  $_FILES["uploadDocAcf"]['name'];
                        $nombre = $id . '_' .  date('Ymd_His') . $fileNombre;
                        $temporal = $_FILES['uploadDocAcf']['tmp_name'];
                        if (!file_exists($ruta)) {
                            mkdir($ruta, 0777, true);
                        }
                        if ((move_uploaded_file($temporal, $ruta . $nombre))) {
                            $sql = "UPDATE acf_hojavida_documentos SET archivo=:archivo WHERE id_documento=:id_documento";
                            $sql = $cmd->prepare($sql);
                            $sql->bindValue(':archivo', $nombre);
                            $sql->bindValue(':id_documento', $id, PDO::PARAM_INT);
                            
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

                $sql = "SELECT archivo FROM acf_hojavida_documentos WHERE id_documento=" . $id;
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();
                $archivo = $obj['archivo'];

                $sql = "DELETE FROM acf_hojavida_documentos WHERE id_documento=" . $id;
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
            $res['mensaje'] = 'Primero debe guardar la Hoja de Vida';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
