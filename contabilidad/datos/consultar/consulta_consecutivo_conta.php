<?php
// Busca consecutivos del tipo de documento recibido para sugerir un numero 
include '../../../conexion.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
// Buscamos si hay registros posteriores a la fecha recibida
$sql = "SELECT max(id_manu) as id_manu from ctb_doc WHERE CAST(fecha AS DATE) <= '$_post[fecha]' AND tipo_doc='$_post[documento]' ";
$res = $conexion->query($sql);
$row = $res->fetch_assoc();
$id_manu = $row['id_manu'];
// Buscar espacios libre hacia adelante
$i = 1;
do {
    $id_manu = $id_manu + 1;
    $sql = "SELECT id_manu from ctb_doc WHERE id_manu='$id_manu' AND tipo_doc='$_post[documento]' ";
    $res = $conexion->query($sql);
    $num = $res->num_rows;
    if ($num == 0) {
        $i = 2;
    }
} while ($i < 2);
$response[] = array("numero" => $id_manu);
echo json_encode($response);
$conexion->close();
exit;
