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
$estado = 1;
$objeto = $_POST['txtObjeto'];
$id_adq = isset($_POST['id_adq']) ? $_POST['id_adq'] : 0;
$id_otro = isset($_POST['id_otro']) ? $_POST['id_otro'] : 0;
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_cdp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $numCdp = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $sql = "INSERT INTO `pto_cdp`
                (`id_pto`,`fecha`,`id_manu`,`objeto`,`num_solicitud`,`estado`,`id_user_reg`,`fecha_reg`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
    $sql = $cmd->prepare($sql);
    $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
    $sql->bindParam(2, $fecha, PDO::PARAM_STR);
    $sql->bindParam(3, $numCdp, PDO::PARAM_INT);
    $sql->bindParam(4, $objeto, PDO::PARAM_STR);
    $sql->bindParam(5, $num_solicitud, PDO::PARAM_STR);
    $sql->bindParam(6, $estado, PDO::PARAM_STR);
    $sql->bindParam(7, $id_user, PDO::PARAM_INT);
    $sql->bindValue(8, $date->format('Y-m-d H:i:s'));
    $sql->execute();
    $id_new = $cmd->lastInsertId();
    if ($id_new > 0) {
        if ($id_adq > 0) {
            if ($id_otro > 0) {
                //es un otrosi 
                $sql = "UPDATE `ctt_novedad_adicion_prorroga`
                        SET `id_cdp` = ?
                        WHERE (`id_nov_con` = ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_new, PDO::PARAM_INT);
                $sql->bindParam(2, $id_otro, PDO::PARAM_INT);
                $sql->execute();
            } else if ($id_otro == 0) {
                //es una contratacion
                $sql = "UPDATE `ctt_adquisiciones`
                        SET `id_cdp` = ?
                        WHERE (`id_adquisicion` = ?)";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $id_new, PDO::PARAM_INT);
                $sql->bindParam(2, $id_adq, PDO::PARAM_INT);
                $sql->execute();
            }
        }
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
