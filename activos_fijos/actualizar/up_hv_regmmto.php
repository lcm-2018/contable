<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';

$id_serie = isset($_POST['id_serie']) ? $_POST['id_serie'] : exit('Acción no permitida');
$id_mmto = $_POST['id_mmto'];
$fecMmto = $_POST['fecMmto'];
$tipoMmnto = $_POST['tipoMmnto'];
$txtaDescribe = $_POST['txtaDescribe'];
$numReporte = $_POST['numReporte'];
$idTercero = $_POST['idTercero'];
$txtaObservaciones = $_POST['txtaObservaciones'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_registro_mantenimiento`
                (`id_serie`, `fecha`, `tipo_mmto`, `descripcion`, `no_reporte`, `tercero_resp`, `observaciones`, `id_user_reg`, `fec_reg`,`id_mmto`)
            VALUES(? , ? , ? , ? , ? , ? , ? , ? , ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_serie, PDO::PARAM_INT);
    $sql->bindParam(2, $fecMmto, PDO::PARAM_STR);
    $sql->bindParam(3, $tipoMmnto, PDO::PARAM_STR);
    $sql->bindParam(4, $txtaDescribe, PDO::PARAM_STR);
    $sql->bindParam(5, $numReporte, PDO::PARAM_STR);
    $sql->bindParam(6, $idTercero, PDO::PARAM_INT);
    $sql->bindParam(7, $txtaObservaciones, PDO::PARAM_STR);
    $sql->bindParam(8, $iduser, PDO::PARAM_INT);
    $sql->bindValue(9, $date->format('Y-m-d H:i:s'), PDO::PARAM_STR);
    $sql->bindParam(10, $id_mmto, PDO::PARAM_INT);
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        $estado = 1;
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "UPDATE `seg_mantenimiento_acfijo` SET `estado`= ? WHERE `id_mmto` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $estado, PDO::PARAM_INT);
        $sql->bindParam(2, $id_mmto, PDO::PARAM_INT);
        if (!($sql->execute())) {
            echo $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
                $sql = "UPDATE `seg_mantenimiento_acfijo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_mmto` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $id_mmto, PDO::PARAM_INT);
                $sql->execute();
                if ($sql->rowCount() > 0) {
                    echo '1';
                } else {
                    echo $sql->errorInfo()[2];
                    exit();
                }
            } else {
                echo '1';
            }
        }
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
