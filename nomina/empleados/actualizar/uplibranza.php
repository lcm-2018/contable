<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$idlib = isset($_POST['numidLibranza']) ? $_POST['numidLibranza'] : exit('Acción no permitida');
$identidad = $_POST['slcUpEntidad'];
$valtotal = str_replace(',', '', $_POST['numUpValTotal']);
$cuotas = $_POST['numUpTotCuotasLib'];
$desc = $_POST['txtUpDescripLib'];
$valmes = $_POST['txtUpValLibMes'];
$porce = $_POST['txtUpPorcLibMes'] / 100;
$finilib = date('Y-m-d', strtotime($_POST['datUpFecInicioLib']));
if ($_POST['datUpFecFinLib'] === '') {
    $ffinlib;
} else {
    $ffinlib = date('Y-m-d', strtotime($_POST['datUpFecFinLib']));
}
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE nom_libranzas SET id_banco = ?, valor_total = ?, cuotas = ?, descripcion_lib = ?, val_mes = ?, porcentaje = ?, fecha_inicio = ?, fecha_fin = ? WHERE id_libranza = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $identidad, PDO::PARAM_INT);
    $sql->bindParam(2, $valtotal, PDO::PARAM_STR);
    $sql->bindParam(3, $cuotas, PDO::PARAM_STR);
    $sql->bindParam(4, $desc, PDO::PARAM_STR);
    $sql->bindParam(5, $valmes, PDO::PARAM_STR);
    $sql->bindParam(6, $porce, PDO::PARAM_STR);
    $sql->bindParam(7, $finilib, PDO::PARAM_STR);
    $sql->bindParam(8, $ffinlib, PDO::PARAM_STR);
    $sql->bindParam(9, $idlib, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        $updata = 1;
    } else {
        $updata = 0;
    }
    if (!($sql->execute())) {
        print_r($sql->errorInfo()[2]);
        exit();
    }
    if ($updata > 0) {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE nom_libranzas SET  fec_act = ? WHERE id_libranza = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(2, $idlib, PDO::PARAM_INT);
        $sql->execute();
        if ($sql->rowCount() > 0) {
            echo '1';
        } else {
            print_r($sql->errorInfo()[2]);
        }
    } else {
        echo 'No se ingresó ningún dato nuevo';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
