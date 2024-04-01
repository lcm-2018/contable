<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$idemple = isset($_POST['idEmpLibranza']) ? $_POST['idEmpLibranza'] : exit('Acción no permitida');
$entidad = $_POST['slcEntidad'];
$valtot = str_replace(',', '', $_POST['numValTotal']);
$cuotas = $_POST['numTotCuotasLib'];
$desc = $_POST['txtDescripLib'];
$valmes = $_POST['txtValLibMes'];
$xctag = $_POST['txtPorcLibMes'] / 100;
$estado = '1';
$finlib = date('Y-m-d', strtotime($_POST['datFecInicioLib']));
if ($_POST['datFecFinLib'] === '') {
    $ffinlib;
} else {
    $ffinlib = date('Y-m-d', strtotime($_POST['datFecFinLib']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO nom_libranzas (id_banco, id_empleado, valor_total, cuotas, descripcion_lib, val_mes, porcentaje, estado, fecha_inicio, fecha_fin, fec_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $entidad, PDO::PARAM_INT);
    $sql->bindParam(2, $idemple, PDO::PARAM_INT);
    $sql->bindParam(3, $valtot, PDO::PARAM_STR);
    $sql->bindParam(4, $cuotas, PDO::PARAM_STR);
    $sql->bindParam(5, $desc, PDO::PARAM_STR);
    $sql->bindParam(6, $valmes, PDO::PARAM_STR);
    $sql->bindParam(7, $xctag, PDO::PARAM_STR);
    $sql->bindParam(8, $estado, PDO::PARAM_STR);
    $sql->bindParam(9, $finlib, PDO::PARAM_STR);
    $sql->bindParam(10, $ffinlib, PDO::PARAM_STR);
    $sql->bindValue(11, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
