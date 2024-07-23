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

    if ((PermisosUsuario($permisos, 5006, 2) && $oper == 'add' ||
        (PermisosUsuario($permisos, 5006, 4) && $oper == 'del') || $id_rol == 1)
    ) {

        $id_hv = isset($_POST['id_hv']) ? $_POST['id_hv'] : -1;
        $id_hv_doc = $_POST['id_hv_doc'];
        
        $rs = $cmd->query($sql);
        $obj_ingreso = $rs->fetch();

        if ($oper == 'add') {

            $archivo_actual = $_POST['archivo'];

            // Datos del formulario
            $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : null;
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : null;
            $id_usuario_crea = isset($_POST['id_usuario_crea']) ? $_POST['id_usuario_crea'] : null;
            $fecha_creacion = date('Y-m-d H:i:s'); // Fecha actual

            if ($id_hv_doc == -1) {

                // Consulta SQL
                $sql = "INSERT INTO acf_hojavida_documentos (
                            id_activo_fijo, tipo, descripcion, archivo, id_usuario_crea, fecha_creacion) 
                        VALUES (:id_activo_fijo, :tipo, :descripcion, :archivo, :id_usuario_crea, :fecha_creacion)";

                // Preparar la consulta
                $sql = $cmd->prepare($sql);

                $nombre = 'temp_file';
                $sql->bindParam(':id_activo_fijo', $id_hv, PDO::PARAM_INT);
                $sql->bindParam(':tipo', $tipo, PDO::PARAM_INT);
                $sql->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $sql->bindParam(':archivo', $nombre, PDO::PARAM_STR);
                $sql->bindParam(':id_usuario_crea', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindParam(':fecha_creacion', $fecha_creacion, PDO::PARAM_STR);

                $inserted = $sql->execute();

                if ($inserted) {
                    $id_hv_doc = $cmd->lastInsertId();

                    $nombreImagenLocal =  $_FILES["uploadDocAcf"]['name'];
                    $fileExtension = '.' . strtolower( pathinfo($nombreImagenLocal)['extension']);
                    $nombre = $id_hv_doc . '_' .  date('Ymd_His') . $fileExtension;
                    $temporal = $_FILES['uploadDocAcf']['tmp_name'];
                    $ruta = '../../imagenes/activos_fijos/';
                    if (!file_exists($ruta)) {
                        $ruta = mkdir($ruta, 0777, true);
                        $ruta = '../../imagenes/activos_fijos/';
                    }
                    if ((move_uploaded_file($temporal, $ruta . $nombre))) {
                        $sql = "UPDATE acf_hojavida_documentos SET archivo = :archivo WHERE id_documento = :id_documento";
                        $sql = $cmd->prepare($sql);
                        $sql->bindValue(':archivo', $nombre);
                        $sql->bindValue(':id_documento', $id_hv_doc, PDO::PARAM_INT);
                        $updated = $sql->execute();
                        if ($updated) {
                            $res['mensaje'] = 'ok';
                            $res['id_hv'] = $id_hv;
                            $res['id_hv_doc'] = $id_hv_doc;
                            $res['nombre_archivo'] = $nombre;
                        } else {
                            $res['mensaje'] = $sql->errorInfo()[2];
                        }
                    } 
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                    $res['nombre_imagen'] = $nombre;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }  else {
                $nombre = $archivo_actual;

                if(isset($_FILES["uploadDocAcf"])) {
                    
                    $nombreImagenLocal =  $_FILES["uploadDocAcf"]['name'];
                    $fileExtension = '.' . strtolower( pathinfo($nombreImagenLocal)['extension']);
                    $nombre = $id_hv_doc . '_' .  date('Ymd_His') . $fileExtension;
                    $temporal = $_FILES['uploadDocAcf']['tmp_name'];
                    $ruta = '../../imagenes/activos_fijos/';

                    if (!file_exists($ruta)) {
                        $ruta = mkdir($ruta, 0777, true);
                        $ruta = '../../imagenes/activos_fijos/';
                    }
                    move_uploaded_file($temporal, $ruta . $nombre);
                }

                $sql = "UPDATE acf_hojavida_documentos 
                        SET tipo = :tipo, descripcion = :descripcion, archivo = :archivo, id_usuario_crea = :id_usuario_crea, fecha_creacion = :fecha_creacion
                        WHERE id_documento = :id_documento";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(':archivo', $nombre);
                $sql->bindValue(':tipo', $tipo);
                $sql->bindValue(':descripcion', $descripcion);
                $sql->bindValue(':id_documento', $id_hv_doc, PDO::PARAM_INT);
                $sql->bindParam(':id_usuario_crea', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindParam(':fecha_creacion', $fecha_creacion, PDO::PARAM_STR);
                $updated = $sql->execute();
                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                    $res['id_hv_doc'] = $id_hv_doc;
                    $res['nombre_archivo'] = $nombre;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
                
            }

        } 

        if ($oper == 'del') {
            $sql = "DELETE FROM acf_hojavida_documentos WHERE id_documento=" . $id_hv_doc;
            $rs = $cmd->query($sql);
            if ($rs) {
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
