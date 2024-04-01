<?php

session_start();
include '../conexion.php';
$datos = isset($_POST['ids']) ? explode('|', $_POST['ids']) : exit('Acción no permitida');
$id_user = $_POST['id_user'];
$id_modulo = $datos[0];
$estado = $datos[1];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($estado == 0) {
        $sql = "INSERT INTO `seg_permisos_modulos` (`id_usuario`, `id_modulo`) VALUES (?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_user);
        $sql->bindParam(2, $id_modulo);
        $sql->execute();
        $res = $sql->rowCount();
    } else {
        $sql = "DELETE FROM `seg_permisos_modulos` WHERE `id_per_mod` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_modulo);
        $sql->execute();
        $res = $sql->rowCount();
    }
    if ($res > 0) {
        echo 'ok';
    } else {
        echo $cmd->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
