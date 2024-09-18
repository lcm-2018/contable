<?php

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
//API URL
$id_dcto = isset($_POST['id_dcto']) ? $_POST['id_dcto'] : exit('Acción no permitida');
$fecha = $_POST['datFecDcto'];
$concepto = $_POST['txtConDcto'];
$valor = $_POST['numValDcto'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `nom_otros_descuentos` SET `fecha` = ?, `concepto` = ?, `valor` = ?  WHERE `id_dcto` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecha, PDO::PARAM_STR);
    $sql->bindParam(2, $concepto, PDO::PARAM_STR);
    $sql->bindParam(3, $valor, PDO::PARAM_STR);
    $sql->bindParam(4, $id_dcto, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `nom_otros_descuentos` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_dcto` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_dcto, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo 'ok';
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
