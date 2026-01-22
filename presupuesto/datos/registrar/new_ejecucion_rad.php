<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';

$id_pto = $_POST['id_pto'];
$fecha = $_POST['dateFecha'];
$num_solicitud = $_POST['numSolicitud'];
$id_manu = $_POST['id_manu'];
$estado = 1;
$id_tercero = $_POST['id_tercero'];
$objeto = $_POST['txtObjeto'];

$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `pto_rad`
            WHERE (`id_pto` = $id_pto AND `id_manu` = $id_manu)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El consecutivo de RAD <b>' . $id_manu . '</b> ya se encuentra registrado';
        echo json_encode($response);
        exit();
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `pto_rad`
                (`id_pto`,`fecha`,`id_manu`,`objeto`,`num_factura`,`estado`,`id_user_reg`,`fecha_reg`,`id_tercero_api`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
    $sql->bindParam(3, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(4, $objeto, PDO::PARAM_STR);
    $sql->bindParam(5, $num_solicitud, PDO::PARAM_STR);
    $sql->bindParam(6, $estado, PDO::PARAM_STR);
    $sql->bindParam(7, $id_user, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->bindParam(9, $id_tercero, PDO::PARAM_INT);
    $sql->execute();
    $id_new = $cmd->lastInsertId();
    if ($id_new > 0) {
        $response['status'] = 'ok';
        $response['msg'] = $id_new;
    } else {
        $response['msg'] = $sql->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
