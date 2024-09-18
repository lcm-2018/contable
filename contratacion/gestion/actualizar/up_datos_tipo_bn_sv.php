<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$idtbs = isset($_POST['idTipoBnSv']) ? $_POST['idTipoBnSv'] : exit('Acción no permitida');
$idtcontrato = $_POST['slcTipoContrato'];
$tcontrato = mb_strtoupper($_POST['txtTipoBnSv']);
$objpre = mb_strtoupper($_POST['txtObjPre']);
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE tb_tipo_bien_servicio SET id_tipo_cotrato = ?, tipo_bn_sv = ?, objeto_definido = ? WHERE id_tipo_b_s = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $idtcontrato, PDO::PARAM_INT);
    $sql->bindParam(2, $tcontrato, PDO::PARAM_STR);
    $sql->bindParam(3, $objpre, PDO::PARAM_STR);
    $sql->bindParam(4, $idtbs, PDO::PARAM_INT);
    $sql->execute();
    $cambio = $sql->rowCount();
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($cambio > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE tb_tipo_bien_servicio SET  id_user_act = ? ,fec_act = ? WHERE id_tipo_b_s = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $idtbs, PDO::PARAM_INT);
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
