<?php
// Realiza la suma del valor total asignado a un CDP
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'),true);
// Buscamos si hay registros posteriores a la fecha recibida
    $sql = "SELECT sum(valor) as total from pto_documento_detalles WHERE id_pto_doc ='$_post[id]' AND tipo_doc='$_post[documento]' ";
    $res = $conexion->query($sql);
    $row = $res->fetch_assoc();
    $total = $row['total'];
$response[] = array("total" => $total);
echo json_encode($response);
$conexion->close();
exit;
