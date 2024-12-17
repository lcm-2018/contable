<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
$estado = $_POST['est']  == '1' ? 0 : 1;
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sqlup = "UPDATE `nom_otros_descuentos` SET `estado` = ?, `fec_act` = ? WHERE `id_dcto` = ?";
    $sqlup = $cmd->prepare($sqlup);
    $sqlup->bindParam(1, $estado);
    $sqlup->bindValue(2, $date->format('Y-m-d H:i:s'));
    $sqlup->bindParam(3, $id);
    $sqlup->execute();
    if (!($sqlup->rowCount() > 0)) {
        echo  $sqlup->errorInfo();
    } else {
        echo 'ok';
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
