<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_nomina = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
try {
    $estado = 2;
    $tipo = 'N';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `nom_nominas` SET `estado` = ?, `planilla` = ? WHERE `id_nomina` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $estado, PDO::PARAM_STR);
    $sql->bindParam(3, $id_nomina, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo 'ok';
    } else {
        echo 'error ' . $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}