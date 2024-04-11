<?php
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$id_rp = $_post['id'];
$parcial = $_post['total'];
$valor_total = 0;
// Consultar valor total causado al registro
$sql = "SELECT sum(valor) as valor FROM ctb_causa_costos WHERE id_pto_rp='$id_rp' AND estado=0";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $total = $row['valor'];
}
// Consulto las causaciones realizadas al registro que se esta ajustando
$sql = "SELECT id, valor FROM ctb_causa_costos WHERE id_pto_rp='$id_rp' AND estado=0";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $valor = ($row['valor'] / $total) * $parcial;
    // Realizo el update
    $sq2 = "UPDATE ctb_causa_costos SET valor='$valor' WHERE id='{$row['id']}'";
    $conexion->query($sq2);
    $id_u = $row['id'];
    $valor_total += $valor;
}
$response[] = array("value" => "ok", "valorcc" => $valor_total);
echo json_encode($response);
exit;
