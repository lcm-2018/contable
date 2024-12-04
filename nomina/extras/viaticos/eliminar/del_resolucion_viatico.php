<?php

session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
include '../../../../conexion.php';

$id_resolucion = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "DELETE FROM `nom_resolucion_viaticos`  WHERE `id_resol_viat` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_resolucion, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $res = '1';
    } else {
        $res = print_r($cmd->errorInfo());
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo $res;
