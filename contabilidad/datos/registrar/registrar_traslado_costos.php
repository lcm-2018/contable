<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_cta  = isset($_POST['opcion']) ? $_POST['opcion'] : exit('Acceso no disponible');
$id_cta_costo = $_POST['id_pgcp'];
$id_cta_debito = $_POST['id_codigoCta1'];
$id_cta_credito = $_POST['id_codigoCta2'];

$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));


try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_cta == 0) {
        $sql = "INSERT INTO `ctb_cuenta_costo`
                (`id_cta_costo`,`id_cta_debito`,`id_cta_credito`,`id_user_reg`,`fec_reg`)
            VALUES (?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_cta_costo, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cta_debito, PDO::PARAM_INT);
        $sql->bindParam(3, $id_cta_credito, PDO::PARAM_INT);
        $sql->bindParam(4, $iduser, PDO::PARAM_INT);
        $sql->bindValue(5, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id = $cmd->lastInsertId();
        if ($cmd->lastInsertId() > 0) {
            echo 'ok';
        } else {
            echo $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `ctb_cuenta_costo`
                    SET `id_cta_debito` = ?, `id_cta_credito` = ?
                WHERE `id_cta` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_cta_debito, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cta_credito, PDO::PARAM_INT);
        $sql->bindParam(3, $id_cta, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `ctb_cuenta_costo` SET `fec_act` = ?, `id_user_act` = ? WHERE `id_cta` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindValue(1, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(2, $iduser, PDO::PARAM_INT);
                $sql->bindParam(3, $id_cta, PDO::PARAM_INT);
                $sql->execute();
                echo 'ok';
            } else {
                echo 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
