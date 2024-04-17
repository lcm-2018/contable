<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
$data = json_decode($data, true);
// Realizo conexion con la base de datos
$response['value'] = 'error';
$id = $data['id'];
$estado = $data['estado'];
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $cmd->prepare("UPDATE `ctb_fuente` SET `estado` = ? WHERE `id_doc_fuente`= ?");
    $query->bindParam(1, $estado, PDO::PARAM_INT);
    $query->bindParam(2, $id, PDO::PARAM_INT);
    $query->execute();
    if ($query->rowCount() > 0) {
        $response['value'] = 'ok';
        $response['msg'] = 'Estado de documento fuente modificado correctamente';
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
    $cmd = null;
} catch (Exception $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
