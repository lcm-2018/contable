<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}
include '../conexion.php';
include '../permisos.php';
$key = array_search('10', array_column($perm_modulos, 'id_modulo'));
if ($key === false) {
    echo 'Usuario no autorizado';
    exit();
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$nombre = $_POST['txtNombreConsulta'];
$parametros  = $_POST['jsonParam'];
$consulta = $_POST['txtConsultaSQL'];
$id_user = $_SESSION['id_user'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_consultas_sql` (`nombre`, `parametros`, `consulta`, `id_user_reg`, `fec_reg`) VALUES (?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $nombre, PDO::PARAM_STR);
    $sql->bindParam(2, $parametros, PDO::PARAM_STR);
    $sql->bindParam(3, $consulta, PDO::PARAM_STR);
    $sql->bindParam(4, $id_user, PDO::PARAM_INT);
    $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
