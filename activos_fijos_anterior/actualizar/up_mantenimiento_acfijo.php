<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_mmto = isset($_POST['id_mmto']) ? $_POST['id_mmto'] : exit('Acción no permitida');
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
    $sql = "UPDATE `seg_mantenimiento_acfijo` SET `num_orden` = ?, `fec_inicia` = ?, `fec_termina` = ?, `concpeto` = ?, `val_deterioro` = ?, `observaciones` = ?, `tipo` = ? WHERE `id_mmto` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $orden, PDO::PARAM_STR);
    $sql->bindParam(2, $fec_inicia, PDO::PARAM_STR);
    $sql->bindParam(3, $fec_fin, PDO::PARAM_STR);
    $sql->bindParam(4, $concepto, PDO::PARAM_STR);
    $sql->bindParam(5, $val_deterioro, PDO::PARAM_STR);
    $sql->bindParam(6, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(7, $tipo, PDO::PARAM_STR);
    $sql->bindParam(8, $id_mmto, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_mantenimiento_acfijo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_mmto` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_mmto, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
                echo 1;
            } else {
                echo $sql->errorInfo()[2];
                exit();
            }
        } else {
            echo 'No se ingresó ningún dato nuevo';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
