<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
//Permisos: 1-Consultar,2-Crear,3-Editar,4-Eliminar,5-Anular,6-Imprimir

$oper = isset($_POST['oper']) ? $_POST['oper'] : exit('Acción no permitida');
$fecha_crea = new DateTime('now', new DateTimeZone('America/Bogota'));
$id_usr_crea = $_SESSION['id_user'];
$res = array();

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

    if ((PermisosUsuario($permisos, 5010, 2) && $oper == 'add' && $_POST['id_dependencia'] == -1) ||
        (PermisosUsuario($permisos, 5010, 3) && $oper == 'add' && $_POST['id_dependencia'] != -1) ||
        (PermisosUsuario($permisos, 5010, 4) && $oper == 'del') || $id_rol == 1) {

        if ($oper == 'add') {
            $id = $_POST['id_dependencia'];            
            $nom_dependencia = $_POST['txt_nom_dependencia'];

            if ($id == -1) {
                $sql = "INSERT INTO tb_dependencias(nom_dependencia,id_usr_crea) VALUES(?,?)";
            } else {
                $sql = "UPDATE tb_dependencias SET nom_dependencia=? WHERE id_dependencia=" . $id;
            }
            $sql = $cmd->prepare($sql);          
            $sql->bindParam(1, $nom_dependencia, PDO::PARAM_STR);         
            if ($id == -1) {
                $sql->bindValue(2, $id_usr_crea);              
                $rs = $sql->execute();
                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $sql_i = 'SELECT LAST_INSERT_ID() AS id';
                    $rs = $cmd->query($sql_i);
                    $obj = $rs->fetch();
                    $res['id'] = $obj['id'];
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            } else {
                $rs = $sql->execute();
                if ($rs) {
                    $res['mensaje'] = 'ok';
                    $res['id'] = $id;
                } else {
                    $res['mensaje'] = $sql->errorInfo()[2];
                }
            }
        }

        if ($oper == 'del') {
            $id = $_POST['id'];
            $sql = "DELETE FROM tb_dependencias WHERE id_dependencia=" . $id;
            $rs = $cmd->query($sql);
            if ($rs) {
                $res['mensaje'] = 'ok';
            } else {
                $res['mensaje'] = $cmd->errorInfo()[2];
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
