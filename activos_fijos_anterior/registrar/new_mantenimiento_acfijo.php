<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_serial = isset($_POST['id_serie_acfijo']) ? $_POST['id_serie_acfijo'] : exit('Acción no permitida');
$orden = $_POST['txtNoOrdenMmto'];
$fec_inicia = $_POST['fecIniciaMmto'];
$fec_fin = $_POST['fecFinMmto'];
$concepto = $_POST['txtConcptoMmto'];
$val_deterioro = $_POST['numValDeterioro'];
$observaciones = $_POST['txtObservaMmto'];
$tipo = $_POST['slcTipoMmto'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_mantenimiento_acfijo` (`id_serial`, `num_orden`, `fec_inicia`, `fec_termina`, `concpeto`, `val_deterioro`, `observaciones`, `tipo`, `id_user_reg`, `fec_reg`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_serial, PDO::PARAM_INT);
    $sql->bindParam(2, $orden, PDO::PARAM_STR);
    $sql->bindParam(3, $fec_inicia, PDO::PARAM_STR);
    $sql->bindParam(4, $fec_fin, PDO::PARAM_STR);
    $sql->bindParam(5, $concepto, PDO::PARAM_STR);
    $sql->bindParam(6, $val_deterioro, PDO::PARAM_STR);
    $sql->bindParam(7, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(8, $tipo, PDO::PARAM_STR);
    $sql->bindParam(9, $id_user, PDO::PARAM_INT);
    $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
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
