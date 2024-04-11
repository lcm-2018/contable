<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
// Realizo conexion con la base de datos
$response['value'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = "UPDATE `ctb_pgcp` SET `estado` = 1 WHERE `id_pgcp` =?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $data, PDO::PARAM_INT);
    $query->execute();
    if ($query->rowCount() > 0) {
        $response['value'] = 'ok';
        $response['msg'] = 'Cuenta activada correctamente';
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
    $cmd = null;
} catch (Exception $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
echo json_encode($response);
