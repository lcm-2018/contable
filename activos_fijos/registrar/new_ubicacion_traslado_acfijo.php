<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
include '../../conexion.php';
$id_serial = isset($_POST['id_serie_acfijo']) ? $_POST['id_serie_acfijo'] : exit('Acción no permitida');
$sede = $_POST['slcSedeUbTr'];
$centro_costo = $_POST['slcCentroCosto'];
$fecha = $_POST['fecUbicTrasl'];
$estado = $_POST['slcEstadoAcFijo'];
$responsable = $_POST['numTercerResp'];
$observaciones = $_POST['txtObservaUbicaTraslado'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `seg_ubica_traslado_centro_costo` (`id_serial`, `id_centro_costo`, `fecha`, `estado`, `id_tercero_api`, `observaciones`, `id_user_reg`, `fec_reg`) 
            VALUES (? , ? , ? , ? , ? , ? , ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_serial, PDO::PARAM_INT);
    $sql->bindParam(2, $centro_costo, PDO::PARAM_INT);
    $sql->bindParam(3, $fecha, PDO::PARAM_STR);
    $sql->bindParam(4, $estado, PDO::PARAM_INT);
    $sql->bindParam(5, $responsable, PDO::PARAM_INT);
    $sql->bindParam(6, $observaciones, PDO::PARAM_STR);
    $sql->bindParam(7, $id_user, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    if ($cmd->lastInsertId() > 0) {
        echo '1';
    } else {
        echo $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
