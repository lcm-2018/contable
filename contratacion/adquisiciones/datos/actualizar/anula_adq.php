<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
include '../../../../permisos.php';
$id_c = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');

try {
    $id_user = $_SESSION['id_user'];
    $estado = 99;
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $id_user, PDO::PARAM_INT);
    $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(4, $id_c, PDO::PARAM_INT);
    $sql->execute();
    if (!($sql->rowCount() > 0)) {
        print_r($sql->errorInfo()[2]);
    } else {
        echo 1;
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
