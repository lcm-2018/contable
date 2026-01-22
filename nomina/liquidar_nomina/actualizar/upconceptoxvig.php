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
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acceso denegado');
$valor = $_POST['valor'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "UPDATE `nom_valxvigencia` SET `valor` = ? WHERE `id_valxvig` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $valor, PDO::PARAM_STR);
    $sql->bindParam(2, $id, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $sql = $sql = "UPDATE `nom_valxvigencia` SET `fec_act` = ? WHERE `id_valxvig` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(2, $id, PDO::PARAM_INT);
            $sql->execute();
            echo 'ok';
        } else {
            echo 'No se realizó ningún cambio';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
