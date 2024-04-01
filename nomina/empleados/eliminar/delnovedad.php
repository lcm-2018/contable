<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idnovedad = $_SESSION['del'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM nom_novedades_eps  WHERE id_novedad = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idnovedad, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
