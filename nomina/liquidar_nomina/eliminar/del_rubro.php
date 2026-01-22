<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("' . $_SESSION['urlin'] . '/index.php");</script>';
    exit();
}

include '../../../conexion.php';

$id = $_POST['id'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `nom_rel_rubro` WHERE `id_relacion` = $id LIMIT 1";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
