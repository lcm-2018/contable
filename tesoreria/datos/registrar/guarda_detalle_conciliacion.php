<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
$id_conciliacion = isset($_POST['id_conciliacion']) ? $_POST['id_conciliacion'] : exit('Acceso no disponible');
$id_libaux = $_POST['id_libaux'];
$opc = $_POST['opc'];
$vigencia = $_SESSION['vigencia'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    //consulto la id_tes_cuenta  con el id_cuenta
    if ($opc == 1) {
        $query = "INSERT INTO `tes_conciliacion_detalle`
                    (`id_concilia`,`id_ctb_libaux`,`fecha_marca`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_conciliacion, PDO::PARAM_INT);
        $query->bindParam(2, $id_libaux, PDO::PARAM_INT);
        $query->bindParam(3, $fecha2);
        $query->bindParam(4, $iduser, PDO::PARAM_INT);
        $query->bindParam(5, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        // eliminar registro
        $query = "DELETE FROM `tes_conciliacion_detalle` WHERE `id_concilia` = ? AND `id_ctb_libaux` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_conciliacion, PDO::PARAM_INT);
        $query->bindParam(2, $id_libaux, PDO::PARAM_INT);
        $query->execute();
        if ($query->rowCount()) {
            include '../../../financiero/reg_logs.php';
            $ruta = '../../../log';
            $consulta = "DELETE FROM `tes_conciliacion_detalle` WHERE `id_concilia` = $id_conciliacion AND `id_ctb_libaux` = $id_libaux";
            RegistraLogs($ruta, $consulta);
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    }
    $query = "SELECT
                `tes_conciliacion_detalle`.`id_concilia`
                , SUM(`ctb_libaux`.`debito`) AS `debito`
                , SUM(`ctb_libaux`.`credito`) AS `credito`
            FROM
                `tes_conciliacion_detalle`
                INNER JOIN `ctb_libaux` 
                    ON (`tes_conciliacion_detalle`.`id_ctb_libaux` = `ctb_libaux`.`id_ctb_libaux`)
            WHERE `tes_conciliacion_detalle`.`id_concilia` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id_conciliacion, PDO::PARAM_INT);
    $query->execute();
    $saldos = $query->fetch();
    $response['debito'] = !empty($saldos) ? $saldos['debito'] : '0';
    $response['credito'] = !empty($saldos) ? $saldos['credito'] : '0';
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
