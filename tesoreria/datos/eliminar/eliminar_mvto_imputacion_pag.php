<?php
$_post = json_decode(file_get_contents('php://input'), true);
$id = $_post['id'];
$tipo_mov = 'PAG';
include '../../../conexion.php';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $pdo->prepare("DELETE FROM pto_documento_detalles WHERE id_pto_mvto = ? AND tipo_mov = ?");
    $query->bindParam(1, $id);
    $query->bindParam(2, $tipo_mov);
    $query->execute();
    $response[] = array("value" => 'ok', "id" => $id);
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
echo json_encode($response);
