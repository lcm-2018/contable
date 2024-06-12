<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_depreciacion = isset($_POST['id_depreciacion']) ? $_POST['id_depreciacion'] : exit('Acción no permitida');
$metodo = $_POST['metodoDeprecia'];
$finicia = $_POST['fecIniDeprecia'];
$meses = $_POST['numMesesDeprecia'];
$residual = $_POST['numValResidual'];
$capacidad = $_POST['numCapacProd'];
$observaciones = $_POST['txtObservaDeprecia'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_depreciacion` SET  `id_metodo` = ?, `fec_inicia` = ?, `vida_util` = ?, `valor_residual` = ?, `capacidad_produccion` = ?, `observacion` = ? WHERE `id_depreciacion` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $metodo, PDO::PARAM_INT);
    $sql->bindParam(2, $finicia, PDO::PARAM_STR);
    $sql->bindParam(3, $meses, PDO::PARAM_INT);
    $sql->bindParam(4, $residual, PDO::PARAM_INT);
    $sql->bindParam(5, $capacidad, PDO::PARAM_INT);
    $sql->bindParam(6, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(7, $id_depreciacion, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_depreciacion` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_depreciacion` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_depreciacion, PDO::PARAM_INT);
            $sql->execute();
            if ($sql->rowCount() > 0) {
               echo '1';
            } else {
                echo $sql->errorInfo()[2];
                exit();
            }
        }else{
            echo 'No se ha modificado níngun dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
