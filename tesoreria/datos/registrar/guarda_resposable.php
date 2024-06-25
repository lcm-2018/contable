<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_detalle = isset($_POST['id_detalle']) ? $_POST['id_detalle'] : exit('Acceso no disponible');
$id_caja = $_POST['id_caja'];
$id_tercero = $_POST['id_tercero'];
$fec_ini = $_POST['fecha_ini'];
$fec_fin = $_POST['fecha_fin'];
$estado = 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if ($id_detalle == 0) {
        $query = "INSERT INTO `tes_caja_respon`
                    (`id_caja_const`,`id_terceros_api`,`fecha_ini`,`fecha_fin`,`estado`,`id_user_reg`,`fec_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_caja, PDO::PARAM_INT);
        $query->bindParam(2, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(3, $fec_ini, PDO::PARAM_STR);
        $query->bindParam(4, $fec_fin, PDO::PARAM_STR);
        $query->bindParam(5, $estado, PDO::PARAM_INT);
        $query->bindParam(6, $iduser, PDO::PARAM_INT);
        $query->bindParam(7, $fecha2, PDO::PARAM_STR);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_caja_respon`
                    SET `id_terceros_api` = ?, `fecha_ini` = ?, `fecha_fin` = ?
                WHERE `id_caja_respon` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $id_tercero, PDO::PARAM_INT);
        $query->bindParam(2, $fec_ini, PDO::PARAM_STR);
        $query->bindParam(3, $fec_fin, PDO::PARAM_STR);
        $query->bindParam(4, $id_detalle, PDO::PARAM_INT);
        if (!($query->execute())) {
            echo $query->errorInfo()[2] . $query->queryString;
        } else {
            if ($query->rowCount() > 0) {
                $query = "UPDATE `tes_caja_respon` SET `id_user_act` = ?, `fecha_act` = ? WHERE (`id_caja_respon` = ?)";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $iduser, PDO::PARAM_INT);
                $query->bindParam(2, $fecha2, PDO::PARAM_STR);
                $query->bindParam(3, $id_detalle, PDO::PARAM_INT);
                $query->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se realizó ningún cambio';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
