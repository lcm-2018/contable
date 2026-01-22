<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];

include '../../../conexion.php';

// Incio la transaccion
$response['status'] = 'error';

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "DELETE FROM `tes_caja_const` WHERE `id_caja_const` = ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $id);
    $query->execute();
    if ($query->rowCount() > 0) {
        include '../../../financiero/reg_logs.php';
        $ruta = '../../../log';
        $consulta = "DELETE FROM `tes_caja_const` WHERE `id_caja_const` = $id";
        RegistraLogs($ruta, $consulta);
        $response['status'] = 'ok';
    } else {
        $response['msg'] = 'Error: ' . $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
