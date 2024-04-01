<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$idRol = isset($_POST['id_perfil']) ? $_POST['id_perfil'] : exit('Acción no permitida');
$perfil = $_POST['perfil'];
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_rol` SET `nom_rol` = ?, `id_usr_crea` = ? WHERE `id_rol` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $perfil, PDO::PARAM_STR);
    $sql->bindParam(2, $id_user, PDO::PARAM_INT);
    $sql->bindParam(3, $idRol, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            echo 'ok';
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
