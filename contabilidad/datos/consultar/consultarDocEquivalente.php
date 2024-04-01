<?php
session_start();
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$cx = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida

// consultar valor valor_aprobado en pto_cargue
$sql = "SELECT MAX(`num_doc`) AS num_doc FROM `seg_ctb_factura` WHERE (`tipo_doc` ='$_post[id]');";
$rs = $cx->query($sql);
$datos = $rs->fetch_assoc();
$tipo = $datos['num_doc'];
$response[] = array("value" => "ok", "tipo" => $tipo);
echo json_encode($response);
$cx->close();
exit;
