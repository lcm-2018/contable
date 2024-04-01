<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$tcompra = isset($_POST['slcTipoCompra']);
$tcontrato = mb_strtoupper($_POST['txtTipoContrato']) ? $_POST['txtTipoContrato'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO tb_tipo_contratacion (id_tipo_compra, tipo_contrato, id_user_reg, fec_reg) VALUES (?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $tcompra, PDO::PARAM_INT);
    $sql->bindParam(2, $tcontrato, PDO::PARAM_STR);
    $sql->bindParam(3, $iduser, PDO::PARAM_INT);
    $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
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
