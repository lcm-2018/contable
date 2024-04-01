<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_entra_af = isset($_POST['id_entra_af']) ? $_POST['id_entra_af'] : exit('Acción no permitida');
$id_tercero = $_POST['id_tercero_pd'];
$numActaRem = $_POST['numActaRem'];
$fecActRem = $_POST['fecActRem'];
$observacion = $_POST['txtObservaActFijo'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_entrada_activo_fijo`  SET `id_tercero_api` = ?, `acta_remision` = ?, `fec_acta_remision` = ?, `observacion` = ? WHERE  `id_entra_af` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_tercero, PDO::PARAM_INT);
    $sql->bindParam(2, $numActaRem, PDO::PARAM_STR);
    $sql->bindParam(3, $fecActRem, PDO::PARAM_STR);
    $sql->bindParam(4, $observacion, PDO::PARAM_STR);
    $sql->bindParam(5, $id_entra_af, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_entrada_activo_fijo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_entra_af` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $iduser, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_entra_af, PDO::PARAM_INT);
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
