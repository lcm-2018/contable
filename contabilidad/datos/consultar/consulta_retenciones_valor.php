<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$id_doc = $_post['id'][0];
$sql = "SELECT sum(valor_retencion) as valor FROM ctb_causa_retencion WHERE id_ctb_doc='$id_doc'";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $valor = $row['valor'] ?? 0;
    $response[] = array("valor_ret" => $valor);
}
echo json_encode($response);
exit;
