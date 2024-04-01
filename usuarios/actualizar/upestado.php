<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';
$data = isset($_POST['datas']) ? explode('|', $_POST['datas']) : exit('Acceso Denegado');
$estado = $data[1];
$iduser = $data[0];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
if ($estado == '1') {
    $estad = '0';
} else {
    $estad = '1';
}

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE seg_usuarios_sistema SET estado = ?, fec_inactivacion = ? WHERE id_usuario = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estad, PDO::PARAM_INT);
    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(3, $iduser, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        echo $cmd->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
