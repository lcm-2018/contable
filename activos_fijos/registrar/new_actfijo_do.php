<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_tercero = isset($_POST['id_tercero_pd']) ? $_POST['id_tercero_pd'] : exit('Acción no permitida');
$tipoEntrada = $_POST['tipoEntrada'];
$numActaRem = $_POST['numActaRem'];
$fecActRem = $_POST['fecActRem'];
$observacion = $_POST['txtObservaActFijo'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_entrada_activo_fijo` (`id_tercero_api`, `id_tipo_entrada`, `acta_remision`, `fec_acta_remision`, `observacion`, `vigencia`, `id_user_reg`, `fec_reg`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(2, $tipoEntrada, PDO::PARAM_INT);
    $sql->bindParam(3, $numActaRem, PDO::PARAM_STR);
    $sql->bindParam(4, $fecActRem, PDO::PARAM_STR);
    $sql->bindParam(5, $observacion, PDO::PARAM_STR);
    $sql->bindParam(6, $vigencia, PDO::PARAM_STR);
    $sql->bindParam(7, $iduser, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
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
