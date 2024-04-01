<?php
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$id_retencion = $_post['id'] ?? 0;
$valor_base = $_post['base'];
$valor_iva = $_post['iva'];
$descuento = 0;
// Consultar tipo de retencion de tabla seg_ctb_retenciones
$sql = "SELECT id_retencion_tipo FROM seg_ctb_retenciones WHERE id_retencion={$id_retencion}";
$rs = $conexion->query($sql);
$retencion = $rs->fetch_assoc();
$id_retencion_tipo = $retencion['id_retencion_tipo'];
// Para retención en la fuente
// Consultar los rangos para aplicar la tarifa que corresponda a la base
$sql = "SELECT valor_base, valor_tope, tarifa FROM seg_ctb_retencion_rango WHERE id_retencion = '$id_retencion' AND valor_base <=$valor_base AND (valor_tope =0 OR valor_tope >=$valor_base)";
$res = $conexion->query($sql);
$rango = $res->fetch_assoc();
// Consulto el tercero de acuerdo al tipo de retencion
$sql = "SELECT
`seg_terceros`.`id_tercero_api`
FROM
`seg_ctb_retenciones`
INNER JOIN `seg_ctb_retencion_tipo` 
    ON (`seg_ctb_retenciones`.`id_retencion` = `seg_ctb_retencion_tipo`.`id_retencion_tipo`)
INNER JOIN `seg_terceros` 
    ON (`seg_ctb_retencion_tipo`.`id_tercero` = `seg_terceros`.`id_tercero`)
WHERE (`seg_ctb_retenciones`.`id_retencion` ='$id_retencion_tipo');";
$res = $conexion->query($sql);
$tercero = $res->fetch_assoc();
$terceroapi = $tercero['id_tercero_api'];
if ($id_retencion_tipo  == 1) {
    $descuento = $valor_base * $rango['tarifa'];
}
// Para retención en el IVA
if ($id_retencion_tipo  == 2) {
    $descuento = $valor_iva * $rango['tarifa'];
}
// Para retención en el ICA
if ($id_retencion_tipo  == 3) {
    $descuento = $valor_base * $rango['tarifa'];
}
if ($id_retencion_tipo == 6) {
    $descuento = $rango['valor_base'];
}
if ($id_retencion_tipo == 5) {
    $descuento =  $valor_base * $rango['tarifa'];
}
$response[] = array("value" => "ok", "desc" => $descuento, "tarifa" => $rango['tarifa'], "terceroapi" => $terceroapi);
echo json_encode($response);
exit;
