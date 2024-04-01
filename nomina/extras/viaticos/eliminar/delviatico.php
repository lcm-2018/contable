<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';

$iddelviat = $_SESSION['del'];


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "DELETE FROM seg_detalle_viaticos  WHERE id_detviatic = ?";
    $sql = $cmd-> prepare($sql);
    $sql -> bindParam(1, $iddelviat, PDO::PARAM_INT);
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

