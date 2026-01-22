<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_fno = isset($_POST['id']) ?  $_POST['id'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `ctt_fact_noobligado`  WHERE `id_facturano`  = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_fno, PDO::PARAM_INT);
    $sql->execute();
    if (!($sql->rowCount() > 0)) {
        echo $sql->errorInfo()[2];
    } else {
        echo 'ok';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
