<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$id_doc = $_post['id'][0];
$estado = $_post['id'][1];
$sql = "SELECT sum(valor) as valor FROM seg_ctb_causa_costos WHERE id_ctb_doc='$id_doc' AND estado='$estado'";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $valor = $row['valor'] ?? 0;
    $response[] = array("valorcc" => $valor);
}
echo json_encode($response);
exit;
