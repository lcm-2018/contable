<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_det = isset($_POST['id']) ?  $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `seg_entra_detalle_activos_fijos`  WHERE `id_acfijo`  = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_det, PDO::PARAM_INT);
    $sql->execute();
    if (!($sql->rowCount() > 0)) {
        echo $sql->errorInfo()[2];
    } else {
        $sql = "DELETE FROM `seg_num_serial`  WHERE `id_activo_fijo`  = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_det, PDO::PARAM_INT);
        $sql->execute();
        if (!($sql->rowCount() > 0)) {
            echo $sql->errorInfo()[2];
        } else {

            echo 1;
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
