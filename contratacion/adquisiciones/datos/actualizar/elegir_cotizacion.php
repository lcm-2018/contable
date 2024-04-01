<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';

$data = isset($_POST['datos']) ? explode('|', $_POST['datos']) : exit('Acción no permitida');
$id_cot = $data[0];
$cc_nit = $data[1];
try {
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "SELECT `id_tercero` FROM `seg_terceros` WHERE `no_doc` = '$cc_nit' LIMIT 1";
    $rs = $cmd->query($sql);
    $id_tercero = $rs->fetch();
    $cmd = null;
    if (isset($id_tercero)) {
        $id_ter = $id_tercero['id_tercero'];
        try {
            $id_user = $_SESSION['id_user'];
            $estado = 5;
            $date = new DateTime('now', new DateTimeZone('America/Bogota'));
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `ctt_adquisiciones` SET `id_tercero`= ?, `estado`= ?, `id_user_act` = ?, `fec_act` = ? WHERE `id_adquisicion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_ter, PDO::PARAM_INT);
            $sql->bindParam(2, $estado, PDO::PARAM_INT);
            $sql->bindParam(3, $id_user, PDO::PARAM_INT);
            $sql->bindValue(4, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(5, $id_cot, PDO::PARAM_INT);
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
        print_r($cdm->errorInfo()[2]);
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
