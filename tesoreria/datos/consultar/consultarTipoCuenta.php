<?php
session_start();
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$cx = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida

// consultar valor valor_aprobado en pto_cargue
$sql = "SELECT tipo_dato FROM seg_ctb_pgcp WHERE cuenta = '$_post[cuenta]'";
$rs = $cx->query($sql);
$datos = $rs->fetch_assoc();
$tipo = $datos['tipo_dato'];
$response[] = array("tipo" => $tipo);
echo json_encode($response);
$cx->close();
exit;
