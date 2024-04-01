<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';

$idafp = $_SESSION['del'];


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "DELETE FROM nom_afp  WHERE id_afp = ?";
    $sql = $cmd-> prepare($sql);
    $sql -> bindParam(1, $idafp, PDO::PARAM_INT);
    $sql->execute();
    if($sql->rowCount() > 0){
        $res = '1';
    }
    else{
        $res = print_r($cmd->errorInfo());
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo $res;


