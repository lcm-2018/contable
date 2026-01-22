<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$aplica = $_POST['aplicacion'];
$marca = $_POST['marca'];
$cuenta = $_POST['cuenta'];

$where = $aplica == 1 ? "WHERE `cuenta` = '$cuenta'" : "WHERE `cuenta` LIKE '$cuenta%'";

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "UPDATE `ctb_pgcp` SET `desagrega` = $marca $where AND  `tipo_dato` = 'D'";
    $query = $cmd->prepare($query);
    if (!($query->execute())) {
        echo $query->errorInfo()[2];
    } else {
        echo 'Se actualizaron ' . $query->rowCount() . ' registros.';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
