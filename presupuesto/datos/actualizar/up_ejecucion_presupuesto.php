<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$id_cdp = isset($_POST['id_cdp']) ? $_POST['id_cdp'] : exit('Acceso no disponible');
$fecha = $_POST['dateFecha'];
$num_solicitud = $_POST['numSolicitud'];
$objeto = $_POST['txtObjeto'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$reponse['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "UPDATE `pto_cdp` SET `fecha` = ?, `objeto` = ?, `num_solicitud` = ? WHERE `id_pto_cdp` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecha, PDO::PARAM_STR);
    $sql->bindParam(2, $objeto, PDO::PARAM_STR);
    $sql->bindParam(3, $num_solicitud, PDO::PARAM_STR);
    $sql->bindParam(4, $id_cdp, PDO::PARAM_INT);
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
            $response['msg'] = 'No se registró ningún nuevo dato';
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
