<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';

$id_cc = isset($_SESSION['del']) ? $_SESSION['del'] : exit('Acción no permitida');
$id_compra = $_POST['id_c'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "DELETE FROM `ctt_contratos`  WHERE `id_contrato_compra` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_cc, PDO::PARAM_INT);
    $sql->execute();
    if ($sql->rowCount() > 0) {
        try {
            $estado = 6;
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `ctt_adquisiciones` SET `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $estado, PDO::PARAM_INT);
            $sql->bindParam(2, $id_user, PDO::PARAM_INT);
            $sql->bindValue(3, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(4, $id_compra, PDO::PARAM_INT);
            $sql->execute();
            if (!($sql->rowCount() > 0)) {
                print_r($sql->errorInfo()[2]);
            } else {
                echo 1;
            }
            $cmd = null;
        } catch (PDOException $e) {
            echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
        }
    } else {
        print_r($sql->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
