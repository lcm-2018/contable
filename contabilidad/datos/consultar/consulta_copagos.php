<?php

include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$concepto = $_post['concep'];
$tercero = $_post['tercero'];
$fecha = $_post['fecha'];
$sql = "SELECT
        SUM(`valor`) as valor
        , `cc_fact`
        , `concepto`
        FROM
        `seg_vta_copagos`
        WHERE (`fecha` ='$fecha'
        AND `cc_fact` =$tercero
        AND `concepto` =$concepto)
        GROUP BY `concepto`;";
$response[] = array("valor" => $sql);
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $valor = $row['valor'] ?? 0;
    $response[] = array("valor" => $valor);
}
echo json_encode($response);
exit;
