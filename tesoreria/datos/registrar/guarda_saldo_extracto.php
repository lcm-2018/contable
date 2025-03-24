<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_conciliacion = isset($_POST['id_conciliacion']) ? $_POST['id_conciliacion'] : exit('Acceso no disponible');
$id_cuenta = $_POST['id_cuenta'];
$mes = $_POST['mes'];
$vigencia = $_SESSION['vigencia'];
$saldo = $_POST['saldo'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    //consulto la id_tes_cuenta  con el id_cuenta
    if ($id_conciliacion == 0) {
        $query = "INSERT INTO `tes_conciliacion`
                    (`id_cuenta`,`vigencia`,`mes`,`saldo_extracto`,`estado`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_cuenta, PDO::PARAM_INT);
        $query->bindParam(2, $vigencia, PDO::PARAM_STR);
        $query->bindParam(3, $mes, PDO::PARAM_STR);
        $query->bindParam(4, $saldo, PDO::PARAM_STR);
        $query->bindParam(5, $estado, PDO::PARAM_INT);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
            $response['id_conciliacion'] = $cmd->lastInsertId();
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_conciliacion`
                    SET `saldo_extracto` = ?
                WHERE `id_conciliacion` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $saldo, PDO::PARAM_STR);
        $query->bindParam(2, $id_conciliacion, PDO::PARAM_INT);
        if ($query->execute()) {
            $response['status'] = 'ok';
            $response['id_conciliacion'] = $id_conciliacion;
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
