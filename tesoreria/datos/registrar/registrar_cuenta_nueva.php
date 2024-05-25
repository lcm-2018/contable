<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_tes_cuenta = isset($_POST['id_tes_cuenta']) ? $_POST['id_tes_cuenta'] : 0;
$banco = $_POST['banco'];
$cuentas = $_POST['id_cuenta'] > 0 ? $_POST['id_cuenta'] : NULL;
$tipo_cuenta = $_POST['tipo_cuenta'];
$numero = $_POST['numero'];
$nombre = $_POST['cuentas'];
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
try {
    if ($id_tes_cuenta == 0) {
        $estado = 1;
        $query = "INSERT INTO `tes_cuentas`
                    (`id_banco`,`id_tipo_cuenta`,`id_cuenta`,`nombre`,`numero`,`estado`, `id_user_reg`,`fecha_reg`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $banco, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $cuentas, PDO::PARAM_INT);
        $query->bindParam(4, $nombre, PDO::PARAM_STR);
        $query->bindParam(5, $numero, PDO::PARAM_STR);
        $query->bindParam(6, $estado, PDO::PARAM_INT);
        $query->bindParam(7, $iduser, PDO::PARAM_INT);
        $query->bindParam(8, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response['status'] = 'ok';
        } else {
            $response['msg'] = $query->errorInfo()[2];
        }
    } else {
        $query = "UPDATE `tes_cuentas`
                    SET `id_banco` = ?, `id_tipo_cuenta` = ?, `id_cuenta` = ?, `nombre` = ?, `numero` = ?
                WHERE `id_tes_cuenta` = ?";
        $query = $cmd->prepare($query);
        $query->bindParam(1, $banco, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_cuenta, PDO::PARAM_INT);
        $query->bindParam(3, $cuentas, PDO::PARAM_INT);
        $query->bindParam(4, $nombre, PDO::PARAM_STR);
        $query->bindParam(5, $numero, PDO::PARAM_STR);
        $query->bindParam(6, $id_tes_cuenta, PDO::PARAM_INT);
        if (!($query->execute())) {
            $response['msg'] = $query->errorInfo()[2];
        } else {
            if ($query->rowCount() > 0) {
                $query = $query = "UPDATE `tes_cuentas` SET `fec_act` = ?, `id_user_act` = ? WHERE `id_tes_cuenta` = ?";
                $query = $cmd->prepare($query);
                $query->bindParam(1, $fecha2);
                $query->bindParam(2, $iduser, PDO::PARAM_INT);
                $query->bindParam(3, $id_tes_cuenta, PDO::PARAM_INT);
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
exit;
