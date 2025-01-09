<?php
session_start();
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
include '../../../financiero/consultas.php';

$id_rubro = isset($_POST['rubro']) ? $_POST['rubro'] : exit('Acceso no permitido');
$valor = str_replace(',', '', $_POST['valor']);
$fecha = $_POST['fecha'];

$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$response['status'] = 'error';
$valores = SaldoRubro($cmd, $id_rubro, $fecha);
$saldo =  $valores['valor_aprobado'] - $valores['debito_cdp'] + $valores['credito_cdp'] + $valores['debito_mod'] - $valores['credito_mod'];
$response['saldo'] = number_format($saldo, 2, '.', ',');
if ($saldo >= $valor) {
    $response['status'] = 'ok';
}
echo json_encode($response);
$cmd = null;
exit;
