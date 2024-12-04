<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id_bs']) ? $_POST['id_bs'] : exit('Acción no permitida');
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_tipo_b_s, objeto_definido
            FROM
            tb_tipo_bien_servicio
            WHERE id_tipo_b_s= '$id'";
    $rs = $cmd->query($sql);
    $objeto_pred = $rs->fetch();
    echo $objeto_pred['objeto_definido'];
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
