<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$estado = $_POST['e'];
$idter = isset($_POST['idt']) ? $_POST['idt'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE seg_terceros SET estado = ?, fec_act = ?, id_user_act = ? WHERE id_tercero = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado);
    $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(3, $iduser);
    $sql->bindParam(4, $idter);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        echo $estado;
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
