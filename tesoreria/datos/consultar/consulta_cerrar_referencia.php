<?php

include '../../../conexion.php';
$data = file_get_contents("php://input");
// Realizo conexion con la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Incio la transaccion
try {
    $query = $cmd->prepare("UPDATE seg_tes_referencia SET estado=1 WHERE id_referencia=?");
    $query->bindParam(1, $data, PDO::PARAM_INT);
    $query->execute();
    $response[] = array("value" => "ok");
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
$cmd = null;
