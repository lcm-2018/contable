<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../../../index.php');
    exit();
}
$id_doc = isset($_POST['id_doc']) ? $_POST['id_doc'] : exit('Acción no permitida');
//API URL
include '../../../../conexion.php';
$url = $api . 'terceros/datos/res/descargar/docs/' . $id_doc;
$ch = curl_init($url);
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);
echo $result;
