<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_crp = isset($_POST['id_crp']) ? $_POST['id_crp'] : exit('Acceso no disponible');
$id_pto = $_POST['id_pto'];
$fecha = $_POST['dateFecha'];
$num_solicitud = $_POST['txtContrato'];
$id_manu = $_POST['id_manu'];
$objeto = $_POST['txtObjeto'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$reponse['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto AND `id_manu` = $id_manu AND `id_pto_crp` <> $id_crp)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    if (!empty($consecutivo)) {
        $response['msg'] = 'El consecutivo de RP <b>' . $id_manu . '</b> ya se encuentra registrado';
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
    $sql = "UPDATE `pto_crp` SET `fecha` = ?, `objeto` = ?, `num_contrato` = ?, `id_manu` = ? WHERE `id_pto_crp` = ?";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $fecha, PDO::PARAM_STR);
    $sql->bindParam(2, $objeto, PDO::PARAM_STR);
    $sql->bindParam(3, $num_solicitud, PDO::PARAM_STR);
    $sql->bindParam(4, $id_manu, PDO::PARAM_INT);
    $sql->bindParam(5, $id_crp, PDO::PARAM_INT);
    if (!($sql->execute())) {
        $response['msg'] = $sql->errorInfo()[2];
        exit();
    } else {
        if ($sql->rowCount() > 0) {
            $sql = "UPDATE `pto_crp` SET `id_user_act` = ?, `fecha_act` = ? WHERE `id_pto_crp` = ?";
            $sql = $cmd->prepare($sql);
            $sql->bindParam(1, $id_user, PDO::PARAM_STR);
            $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
            $sql->bindParam(3, $id_crp, PDO::PARAM_INT);
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
