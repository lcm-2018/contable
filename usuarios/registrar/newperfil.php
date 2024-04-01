<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$perfil = isset($_POST['data']) ? $_POST['data'] : exit('Acción no permitida');
$user_reg = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_rol` (`nom_rol`, `id_usr_crea`)VALUES (?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $perfil, PDO::PARAM_STR);
    $sql->bindParam(2, $user_reg, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $cmd->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
