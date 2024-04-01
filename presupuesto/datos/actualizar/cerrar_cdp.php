<?php

session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$id_cdp = file_get_contents("php://input");
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$estado = 2;
$response['status'] = 'ok';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `pto_cdp` SET `estado` = ? WHERE `id_pto_cdp` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $estado, PDO::PARAM_INT);
    $sql->bindParam(2, $id_cdp, PDO::PARAM_INT);
    if (!($sql->execute())) {
        $response['msg'] = $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `pto_cdp` SET `id_user_act` = ?, `fecha_act` = ? WHERE `id_pto_cdp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_STR);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_cdp, PDO::PARAM_INT);
            $sql->execute();
            $response['status'] = 'ok';
        } else {
            $response['status'] = 'ok';
            $response['msg'] = 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
