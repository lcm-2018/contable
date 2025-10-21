<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id_perfil']) ? $_POST['id_perfil'] : exit('Acción no permitida');
$descripcion = $_POST['txtPerfilTercero'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($id == 0) {
        $sql = "INSERT INTO `ctt_perfil_tercero`
                    (`descripcion`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $descripcion, PDO::PARAM_STR);
        $sql->bindParam(2, $id_user, PDO::PARAM_INT);
        $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `ctt_perfil_tercero` SET `descripcion` = ? WHERE `id_perfil` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $descripcion, PDO::PARAM_STR);
        $sql->bindParam(2, $id, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo 'No se actualizó ningún registro';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
