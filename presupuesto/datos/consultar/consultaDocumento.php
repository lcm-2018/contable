<?php
// Busca consecutivos del tipo de documento recibido para validar que no se duplique número
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'),true);
// Buscamos si hay registros posteriores a la fecha recibida
    $sql = "SELECT id_manu from pto_documento WHERE id_manu = '$_post[doc]' AND tipo_doc='$_post[tipo]' ";
    $res = $conexion->query($sql);
    $row = $res->fetch_assoc();
    $num = $res->num_rows;
$response[] = array("numero" => $num);
echo json_encode($response);
$conexion->close();
exit;
