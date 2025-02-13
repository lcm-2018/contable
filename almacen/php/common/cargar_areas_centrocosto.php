<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
$idcec = $_POST['id_centrocosto'];
$todas = isset($_POST['todas']) ? $_POST['todas'] : false;
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    echo '<option value="">' . $titulo . '</option>';    
    if ($todas){
        $sql = "SELECT id_area,nom_area FROM far_centrocosto_area WHERE id_area<>0";
    } else {    
        $sql = "SELECT id_area,nom_area FROM far_centrocosto_area WHERE id_centrocosto=$idcec";
    }
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    foreach ($objs as $obj) {
        echo '<option value="' . $obj['id_area'] . '">' . $obj['nom_area'] . '</option>';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
