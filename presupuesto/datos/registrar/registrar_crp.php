<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
$id_pto = $_POST['id_pto_presupuestos'];
$fecha = $_POST['fecha'];
$contrato = $_POST['contrato'];
$tercero = $_POST['id_tercero'];
$objeto = $_POST['objeto'];
$detalles = $_POST['detalle'];
$id_crp = $_POST['id_crp'];
$id_cdp = $_POST['id_cdp'];
$id_user = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$estado = 1;
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT
                MAX(`id_manu`) AS `id_manu` 
            FROM
                `pto_crp`
            WHERE (`id_pto` = $id_pto)";
    $rs = $cmd->query($sql);
    $consecutivo = $rs->fetch();
    $numCrp = !empty($consecutivo) ? $consecutivo['id_manu'] + 1 : 1;
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if ($id_crp == 0) {
    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $sql = "INSERT INTO `pto_crp`
                (`id_pto`, `id_cdp`,`fecha`,`id_manu`,`id_tercero_api`,`objeto`,`num_contrato`,`estado`,`id_user_reg`,`fecha_reg`)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_pto, PDO::PARAM_INT);
        $sql->bindParam(2, $id_cdp, PDO::PARAM_INT);
        $sql->bindParam(3, $fecha, PDO::PARAM_STR);
        $sql->bindParam(4, $numCrp, PDO::PARAM_INT);
        $sql->bindParam(5, $tercero, PDO::PARAM_INT);
        $sql->bindParam(6, $objeto, PDO::PARAM_STR);
        $sql->bindParam(7, $contrato, PDO::PARAM_STR);
        $sql->bindParam(8, $estado, PDO::PARAM_STR);
        $sql->bindParam(9, $id_user, PDO::PARAM_INT);
        $sql->bindValue(10, $date->format('Y-m-d H:i:s'));
        $sql->execute();
        $id_new = $cmd->lastInsertId();
        if ($id_new > 0) {
            $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
            $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
            $query = "INSERT INTO `pto_crp_detalle`
                        (`id_pto_crp`,`id_pto_cdp_det`,`id_tercero_api`,`valor`,`id_user_reg`,`fecha_reg`)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $query = $cmd->prepare($query);
            $query->bindParam(1, $id_new, PDO::PARAM_INT);
            $query->bindParam(2, $id_detalle, PDO::PARAM_INT);
            $query->bindParam(3, $tercero, PDO::PARAM_INT);
            $query->bindParam(4, $valor, PDO::PARAM_STR);
            $query->bindParam(5, $id_user, PDO::PARAM_INT);
            $query->bindValue(6, $date->format('Y-m-d H:i:s'));
            foreach ($detalles as $key => $value) {
                $id_detalle = $key;
                $valor = str_replace(',', '', $value);
                $query->execute();
                if (!($cmd->lastInsertId() > 0)) {
                    $response['msg'] = $query->errorInfo()[2];
                    break;
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
} else {
}

echo json_encode($response);
