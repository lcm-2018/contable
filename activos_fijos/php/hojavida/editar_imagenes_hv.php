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

        $id_hv = $_POST['id_hv'];

        $rs = $cmd->query($sql);
        $obj_ingreso = $rs->fetch();

        if ($oper == 'add') {
            if ($id_hv != -1) {

                $nombreImagenLocal =  $_FILES["uploadImageAcf"]['name'];
                $fileExtension = '.' . strtolower( pathinfo($nombreImagenLocal)['extension']);
                $nombre = $id_hv . '_' .  date('Ymd_His') . $fileExtension;
                $temporal = $_FILES['uploadImageAcf']['tmp_name'];
                $ruta = '../../imagenes/activos_fijos/';

                if (!file_exists($ruta)) {
                    $ruta = mkdir($ruta, 0777, true);
                    $ruta = '../../imagenes/activos_fijos/';
                }
                if (!(move_uploaded_file($temporal, $ruta . $nombre))) {
                    $res['mensaje'] = 'No se pudo adjuntar el archivo';
                    exit();
                } 
            
                $sql = "UPDATE acf_hojavida SET imagen = :imagen, id_usr_act = :id_usr_act, fecha_act = :fecha_act WHERE id = :id_hv";
                $sql = $cmd->prepare($sql);

                $sql->bindValue(':imagen', $nombre);
                $sql->bindValue(':id_usr_act', $id_usr_crea, PDO::PARAM_INT);
                $sql->bindValue(':fecha_act', $fecha_crea);
                $sql->bindValue(':id_hv', $id_hv, PDO::PARAM_INT);

                $updated = $sql->execute();

                if ($updated) {
                    $res['mensaje'] = 'ok';
                    $res['id_hv'] = $id_hv;
                    $res['nombre_imagen'] = $nombre;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            } 

        } else {
            $res['mensaje'] = 'El Usuario del Sistema no tiene Permisos para esta Acción';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($res);
