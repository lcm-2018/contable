<?php

use Sabberworm\CSS\Value\Value;

session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$_post = json_decode(file_get_contents('php://input'), true);
$tipo = $_post['id'];
$response['status'] = "error";
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
try {
    $sql = "SELECT MAX(`num_doc`) AS `num_doc` FROM `ctb_factura` WHERE (`id_tipo_doc` = $tipo)";
    $rs = $cmd->query($sql);
    $datos = $rs->fetch();
    $consecutivo = !empty($datos) ? $datos['num_doc'] + 1 : 1;
    $response['status'] = 'ok';
    $response['consecutivo'] = $consecutivo;
    $response['msg'] = 'Consecutivo generado';
    $cmd = null;
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
