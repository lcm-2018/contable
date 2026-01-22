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
$ruta = '../../imagenes/';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5704, 3) && $oper == 'add' || $id_rol == 1)) {

        $id_hv = $_POST['id_hv'];

        if ($id_hv != -1) {
            $res['mensaje'] = 'ok';
            $res['nombre_imagen'] = $_POST['imagen'];

            if ($_POST['del_imagen'] == 1 || $_POST['act_imagen'] == 1){
                $sql = "SELECT imagen FROM acf_hojavida WHERE id_activo_fijo=" . $id_hv;
                $rs = $cmd->query($sql);
                $obj = $rs->fetch();
                $imagen = $obj['imagen'];
                if ($imagen && file_exists($ruta . $imagen)) {
                    unlink($ruta . $imagen);                    
                }

                $sql = "UPDATE acf_hojavida SET imagen=:imagen WHERE id_activo_fijo=:id_hv";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(':imagen', '');
                $sql->bindValue(':id_hv', $id_hv, PDO::PARAM_INT);
                $updated = $sql->execute();
                
                if ($updated) {
                    $res['nombre_imagen'] = '';
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }                
            }    

            if ($_POST['act_imagen'] == 1 && $res['mensaje'] == 'ok'){
                $fileNombre =  $_FILES["uploadImageAcf"]['name'];
                $nombre = $id_hv . '_' .  date('Ymd_His') . $fileNombre;
                $temporal = $_FILES['uploadImageAcf']['tmp_name'];
                if (!file_exists($ruta)) {
                    mkdir($ruta, 0777, true);
                }
                if (move_uploaded_file($temporal, $ruta . $nombre)) {
                    $sql = "UPDATE acf_hojavida SET imagen=:imagen,id_usr_actualiza=:id_usr_actualiza,fec_actualiza=:fec_actualiza WHERE id_activo_fijo=:id_hv";
                    $sql = $cmd->prepare($sql);

                    $sql->bindValue(':imagen', $nombre);
                    $sql->bindValue(':id_usr_actualiza', $id_usr_crea, PDO::PARAM_INT);
                    $sql->bindValue(':fec_actualiza', $fecha_crea);
                    $sql->bindValue(':id_hv', $id_hv, PDO::PARAM_INT);
                    $updated = $sql->execute();

                    if ($updated) {
                        $res['nombre_imagen'] = $nombre;
                    } else {
                        $res['mensaje'] = $sql->errorInfo()[2];
                    }
                } else {
                    $res['mensaje'] = 'Error al Adjuntar el Archivo';
                }    
            }    
        } else {
            $res['mensaje'] = 'Primero debe guardar la Hoja de Vida del Activo Fijo';
        }
    } else {
        $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
