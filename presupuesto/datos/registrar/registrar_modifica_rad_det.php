<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';

$op = isset($_POST['opcion']) ? $_POST['opcion'] : exit('Acceso no disponible');
$id_rubroCod = $_POST['id_rubroCod'];
$id_rad = $_POST['id_rad'];
$valorDeb = str_replace(",", "", $_POST['valorDeb']);
$valorCred = 0;
$id_tercero = $_POST['id_tercero'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$vigencia = $_SESSION['vigencia'];
$response['status'] = 'error';

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

try {
    if ($op == 0) {
        $sql = "INSERT INTO `pto_rad_detalle`
                    (`id_pto_rad`,`id_rubro`,`valor`,`valor_liberado`,`id_user_reg`,`fecha_reg`,`id_tercero_api`)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_rad, PDO::PARAM_INT);
        $sql->bindParam(2, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(3, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(4, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(5, $iduser, PDO::PARAM_INT);
        $sql->bindValue(6, $date->format('Y-m-d H:i:s'));
        $sql->bindParam(7, $id_tercero, PDO::PARAM_INT);
        $sql->execute();
        if ($cmd->lastInsertId() > 0) {
            $response['status'] = "ok";
        } else {
            $response['msg'] = $sql->errorInfo()[2];
        }
    } else {
        $sql = "UPDATE `pto_rad_detalle`
                    SET `id_rubro` = ?, `valor` = ?, `valor_liberado` = ?
                WHERE `id_pto_rad_det` = ?";
        $sql = $cmd->prepare($sql);
        $sql->bindParam(1, $id_rubroCod, PDO::PARAM_INT);
        $sql->bindParam(2, $valorDeb, PDO::PARAM_STR);
        $sql->bindParam(3, $valorCred, PDO::PARAM_STR);
        $sql->bindParam(4, $op, PDO::PARAM_INT);
        if (!($sql->execute())) {
            $response['msg'] = $sql->errorInfo()[2];
            exit();
        } else {
            if ($sql->rowCount() > 0) {
                $sql = "UPDATE `pto_rad_detalle`
                            SET `id_user_act` = ?, `fecha_act` = ?
                        WHERE `id_pto_rad_det` = ?";
                $sql = $cmd->prepare($sql);
                $sql->bindParam(1, $iduser, PDO::PARAM_INT);
                $sql->bindValue(2, $date->format('Y-m-d H:i:s'));
                $sql->bindParam(3, $op, PDO::PARAM_INT);
                $sql->execute();
                $response['status'] = 'ok';
            } else {
                $response['msg'] = 'No se registró ningún nuevo dato';
            }
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}

echo json_encode($response);
