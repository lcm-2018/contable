<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : exit('Accion no permitida');
$cantidad = $_POST['numCantidad'];
$val_unid = $_POST['numValUnid'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    $sql = "UPDATE `ctt_orden_compra_detalle` SET `cantidad` = ? , `val_unid` = ? WHERE `id_detalle` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $cantidad, PDO::PARAM_INT);
    $sql->bindParam(2, $val_unid, PDO::PARAM_STR);
    $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `ctt_orden_compra_detalle` SET `id_user_act` = ? , `fec_act` = ? WHERE `id_detalle` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_detalle, PDO::PARAM_INT);
            $sql->execute();
            echo $sql->rowCount() > 0 ? 'ok' : $sql->errorInfo()[2];
        } else {
            echo 'No se actualizó ningún registro';
        }
    }
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
