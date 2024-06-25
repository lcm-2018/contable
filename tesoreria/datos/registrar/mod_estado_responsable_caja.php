<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$id_detalle = isset($_POST['id']) ? $_POST['id'] : exit('Acceso no permitido');
$estado = $_POST['estado'] == '1' ? 0 : 1;
$iduser = $_SESSION['id_user'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
$fecha2 = $date->format('Y-m-d H:i:s');
include '../../../conexion.php';
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "UPDATE `tes_caja_respon` SET `estado` = ? WHERE (`id_caja_respon` = ?)";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $estado, PDO::PARAM_INT);
    $query->bindParam(2, $id_detalle, PDO::PARAM_INT);
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
        }
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
