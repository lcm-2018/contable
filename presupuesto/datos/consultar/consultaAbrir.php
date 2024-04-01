<?php
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$data = file_get_contents("php://input");
// update ctb_libaux set estado='C' where id_ctb_doc=$data;
$sql = "UPDATE pto_documento SET estado=1 WHERE id_pto_doc=$data";
$res = $conexion->query($sql);
$response[] = array("value" => "ok");
echo json_encode($response);
$conexion->close();
exit;
