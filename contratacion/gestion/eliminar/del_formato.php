<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$idbs = $_POST['id'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `ctt_formatos_doc_rel`  WHERE `id_relacion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idbs, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        unlink('../../adquisiciones/soportes/' . $idbs . '.docx');
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
