<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idtcontrato = isset($_POST['idTipoContrato']) ? $_POST['idTipoContrato'] : exit('Acción no permitida');
$idtcompra = $_POST['slcTipoCompra'];
$tcontrato = $_POST['txtTipoContrato'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE tb_tipo_contratacion SET id_tipo_compra = ?, tipo_contrato = ? WHERE id_tipo = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idtcompra, PDO::PARAM_INT);
    $sql->bindParam(2, $tcontrato, PDO::PARAM_STR);
    $sql->bindParam(3, $idtcontrato, PDO::PARAM_INT);
    $sql->execute();
    $cambio = $sql->rowCount();
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($cambio > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE tb_tipo_contratacion SET  id_user_act = ? ,fec_act = ? WHERE id_tipo = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $idtcontrato, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo '1';
            } else {
                echo $sql->errorInfo()[2];
            }
        } else {
            echo 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
