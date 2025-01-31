<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
// Incio la transaccion
$response['status'] = 'error';
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $estado = 2;
    $query = "UPDATE `ctb_doc` SET `estado`= ? WHERE `id_ctb_doc`= ?";
    $query = $cmd->prepare($query);
    $query->bindParam(1, $estado, PDO::PARAM_INT);
    $query->bindParam(2, $data, PDO::PARAM_INT);
    $query->execute();
    $response['status'] = 'ok';
    $cmd = null;
} catch (Exception $e) {
    $response['msg'] = $e->getMessage();
}
echo json_encode($response);
