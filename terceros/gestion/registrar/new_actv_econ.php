<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../index.php');
    exit();
}
include '../../../conexion.php';
$idt = isset($_POST['idTercero']) ? $_POST['idTercero'] : exit('Acción no permitida');
$id_actv_econ = $_POST['slcActvEcon'];
$finic = date('Y-m-d', strtotime($_POST['datFecInicio']));
$estado = '1';
$iduser = isset($_SESSION['user']);
$tipouser = 'user';
$doc_reg = $_SESSION['nit_emp'];
$date = new DateTime('now', new DateTimeZone('America/Bogota'));
//API URL
$url = $api . 'terceros/datos/res/nuevo/actividad';
$ch = curl_init($url);
$data = [
    "id_tercero" => $idt,
    "id_actividad" => $id_actv_econ,
    "finic" => $finic,
    "id_user" => $iduser,
    "tipo_user" => $tipouser,
    "nit_reg" => $doc_reg,
];
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
$res = json_decode($result, true);
echo $res;
