<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
$data = isset($_POST['id']) ? explode('|', base64_decode($_POST['id'])) : exit('Acceso no disponible');
$id = $data[0];
$detalle = $data[1];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$response['status'] = 'error';

try {
    $query = $cmd->prepare("DELETE FROM `ctb_factura` WHERE `id_cta_factura` = ?");
    $query->bindParam(1, $detalle);
    $query->execute();
    if ($query->rowCount() > 0) {
        $response['status'] = 'ok';
        $response['msg'] = 'Factura eliminada correctamente';
        $response['id'] = $id;
    } else {
        $response['msg'] = $query->errorInfo()[2];
    }
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}

echo json_encode($response);
