<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$idsede = $_POST['id_sede'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    echo '<option value="">' . $titulo . '</option>';    
    $sql = "SELECT id_area,nom_area,id_responsable FROM far_centrocosto_area WHERE id_sede=$idsede";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    foreach ($objs as $obj) {
        $dtad = 'data-idresponsable="' . $obj['id_responsable'] . '"';
        echo '<option value="' . $obj['id_area'] . '"' . $dtad . '>' . $obj['nom_area'] . '</option>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
