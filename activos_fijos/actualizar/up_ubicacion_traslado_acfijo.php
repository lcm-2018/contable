<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_traslado = isset($_POST['id_traslado']) ? $_POST['id_traslado'] : exit('Acción no permitida');
$sede = $_POST['slcSedeUbTr'];
$centro_costo = $_POST['slcCentroCosto'];
$fecha = $_POST['fecUbicTrasl'];
$estado = $_POST['slcEstadoAcFijo'];
$observaciones = $_POST['txtObservaUbicaTraslado'];
$id_tercero_api = $_POST['numTercerResp'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `seg_ubica_traslado_centro_costo` SET `id_centro_costo` = ?, `fecha` = ?, `estado` = ?, `id_tercero_api` = ?, `observaciones` = ? WHERE `id_traslado` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $centro_costo, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
    $sql->bindParam(3, $estado, PDO::PARAM_INT);
    $sql->bindParam(4, $id_tercero_api, PDO::PARAM_INT);
    $sql->bindParam(5, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(6, $id_traslado, PDO::PARAM_INT);
    if (!($sql->execute())) {
        echo $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $sql = "UPDATE `seg_ubica_traslado_centro_costo` SET  `id_user_act` = ? ,`fec_act` = ?  WHERE `id_traslado` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_INT);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_traslado, PDO::PARAM_INT);
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
