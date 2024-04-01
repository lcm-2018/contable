<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit;
}
include '../../conexion.php';

$iduser = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM seg_usuarios_sistema  WHERE id_usuario = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $iduser, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $res = '1';
    } else {
        $res = $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $res['mensaje'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo $res;
