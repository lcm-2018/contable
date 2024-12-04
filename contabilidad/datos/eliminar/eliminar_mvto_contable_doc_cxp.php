<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
include_once '../../../financiero/consultas.php';
function pesos($valor)
{
    return '$ '.number_format($valor, 2, '.', ',');
}
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
} catch (PDOException $e) {
    $response['msg'] = $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
$acumulado = GetValoresCxP($id, $cmd);
$acumulado = $acumulado['val_factura'];
$response['acumulado'] = pesos($acumulado);
echo json_encode($response);
