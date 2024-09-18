<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
$key = array_search('51', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$vigencia = isset($_POST['vigencia']) ? $_POST['vigencia'] : exit('Acceso denegado');
$id_empresa = 2;
$registros  = 12;
$vence = $vigencia . '-' . '12-31';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "INSERT INTO `tb_vigencias`
                (`anio`, `ven_fecha`, `id_empresa`)
            VALUES      (?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(2, $vence, PDO::PARAM_STR);
    $sql->bindParam(3, $id_empresa, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo 'ok';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
