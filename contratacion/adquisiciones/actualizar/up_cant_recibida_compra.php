<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../../../index.php");
    exit();
}
include '../../../conexion.php';
//API URL
$estado = isset($_POST['est']) ? $_POST['est'] : exit('Acción no permitida');
$iduser = $_SESSION['id_user'];
$tipuser = 'user';
if ($estado == 1) {
    $id = $_POST['id'];
    $id_c = '';
} else {
    $id = $_REQUEST['entrega'];
    $id_c = $_POST['id_cnt'];
}
$data = [
    "id" => $id,
    "estado" => $estado,
    "id_c" => $id_c,
    "iduser" => $iduser,
    "tipuser" => $tipuser,
];
//API
$url = $api . 'terceros/datos/res/actualizar/estado_entrega';
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($ch);
curl_close($ch);
echo json_decode($res, true);
